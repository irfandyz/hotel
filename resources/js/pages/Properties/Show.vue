<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { StarRating } from '@/components/ui/star-rating';
import Badge from '@/components/ui/badge/Badge.vue';
import { ArrowLeft, MapPin, Phone, Calendar, Star, Edit, Save, X, Trash2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, reactive, onMounted, onUnmounted } from 'vue';
import axiosInstance from '@/lib/axios';
import { ImageUpload } from '@/components/ui/image-upload';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';

interface PropertyImage {
    id: number;
    image: string;
    caption?: string;
    type?: string;
    created_at: string;
}

interface Property {
    id: number;
    name: string;
    description?: string;
    phone?: string;
    address?: string;
    city?: string;
    state?: string;
    zip?: string;
    country?: string;
    latitude?: number;
    longitude?: number;
    star_rating?: number;
    total_rooms?: number;
    hotel_category?: string;
    created_at: string;
    images: PropertyImage[];
}

interface Props {
    property: Property;
}

const props = defineProps<Props>();

// Edit mode state
const isEditMode = ref(false);
const editingField = ref<string | null>(null);
const isLoading = ref(false);
const deletingImageId = ref<number|null>(null);

// Notification state
const notification = ref<{
  show: boolean;
  message: string;
  type: 'success' | 'error' | 'info';
}>({
  show: false,
  message: '',
  type: 'info'
});

const showNotification = (message: string, type: 'success' | 'error' | 'info' = 'info') => {
  notification.value = {
    show: true,
    message,
    type
  };

  // Auto hide after 3 seconds
  setTimeout(() => {
    notification.value.show = false;
  }, 3000);
};

// Dialog states
const showDeleteConfirmDialog = ref(false);
const imageToDelete = ref<number | null>(null);
const showErrorDialog = ref(false);
const errorDetails = ref<{
  title: string;
  message: string;
  errors?: Record<string, string[]>;
}>({
  title: '',
  message: '',
  errors: {}
});

// Function to refresh images from server after upload
const refreshImages = async () => {
  try {
    // For upload, we need to reload to get the new images
    // For delete, we don't need this as we update the local array
    window.location.reload();
  } catch (error) {
    console.error('Failed to refresh images:', error);
    showNotification('Gagal memperbarui daftar gambar', 'error');
  }
};

// Form data for editing
const form = reactive({
    name: props.property.name,
    description: props.property.description || '',
    phone: props.property.phone || '',
    address: props.property.address || '',
    city: props.property.city || '',
    state: props.property.state || '',
    zip: props.property.zip || '',
    country: props.property.country || '',
    latitude: props.property.latitude || '',
    longitude: props.property.longitude || '',
    star_rating: props.property.star_rating || 0,
    total_rooms: props.property.total_rooms || '',
    hotel_category: props.property.hotel_category || ''
});

const hasUnsavedChanges = ref(false);

// Tambahkan state lokal untuk gambar
const propertyImages = ref([...props.property.images]);
const newImages = ref<File[]>([]);
const isUploading = ref(false);

const handleNewImagesAdded = (files: File[]) => {
  console.log('handleNewImagesAdded called with:', files.length, 'files');

  // Filter out files that are already in newImages to prevent duplicates
  const existingFileNames = newImages.value.map(f => f.name);
  const newFiles = files.filter(file => !existingFileNames.includes(file.name));

  const totalImages = propertyImages.value.length + newImages.value.length + newFiles.length;
  if (totalImages > 5) {
    alert(`Maksimal 5 gambar. Saat ini ada ${propertyImages.value.length} gambar + ${newImages.value.length} yang akan diupload.`);
    return;
  }

  // Only add files that aren't already in the array
  if (newFiles.length > 0) {
    newImages.value = [...newImages.value, ...newFiles];
    console.log('Total newImages after adding:', newImages.value.length);
  }
};

const uploadNewImages = async () => {
  if (newImages.value.length === 0) return;
  isUploading.value = true;

  try {
    const formData = new FormData();
    console.log('Uploading', newImages.value.length, 'files');

    newImages.value.forEach((file, index) => {
      formData.append(`new_images[${index}]`, file);
    });

    // Gunakan axios untuk upload file
    console.log('FormData contents:');
    for (const [key, value] of formData.entries()) {
      console.log(key, value);
    }

    await axiosInstance.post(`/properties/${props.property.id}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    // Tampilkan pesan sukses
    showNotification('Gambar berhasil diupload', 'success');

    // Clear the newImages array after successful upload
    newImages.value = [];

    // Refresh gambar dari server tanpa reload halaman
    await refreshImages();
    } catch (error: any) {
    console.error('Upload error:', error);

    // Tampilkan error dialog dengan detail
    let errorMessage = 'Upload gagal';
    let errorTitle = 'Gagal Upload Gambar';
    let validationErrors: Record<string, string[]> = {};

    if (error.response) {
      // Server responded with error status
      console.error('Error response:', error.response.data);
      if (error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else if (error.response.data && error.response.data.errors) {
        validationErrors = error.response.data.errors;
        errorMessage = 'Terdapat kesalahan validasi pada file yang diupload';
        errorTitle = 'Validasi Error';
      } else {
        errorMessage = error.response.statusText;
      }
    } else if (error.request) {
      // Request was made but no response received
      errorMessage = 'Tidak ada respons dari server';
    } else {
      // Something else happened
      errorMessage = error.message;
    }

    showErrorDialog.value = true;
    errorDetails.value = {
      title: errorTitle,
      message: errorMessage,
      errors: Object.keys(validationErrors).length > 0 ? validationErrors : undefined
    };
  }

  isUploading.value = false;
};

// Hotel categories
const hotelCategories = [
    { value: 'budget', label: 'Budget' },
    { value: 'mid-range', label: 'Mid-Range' },
    { value: 'luxury', label: 'Luxury' }
];

// Functions
const toggleEditMode = () => {
    if (isEditMode.value) {
                // Check if there are unsaved changes
        const hasChanges = false; // No unsaved changes for images

        if (hasChanges) {
            const confirmExit = confirm('Ada perubahan gambar yang belum disimpan. Yakin ingin keluar dari mode edit?');
            if (!confirmExit) {
                return;
            }
        }

        editingField.value = null;
        // Reset form to original values
        Object.assign(form, {
            name: props.property.name,
            description: props.property.description || '',
            phone: props.property.phone || '',
            address: props.property.address || '',
            city: props.property.city || '',
            state: props.property.state || '',
            zip: props.property.zip || '',
            country: props.property.country || '',
            latitude: props.property.latitude || '',
            longitude: props.property.longitude || '',
            star_rating: props.property.star_rating || 0,
            total_rooms: props.property.total_rooms || '',
            hotel_category: props.property.hotel_category || ''
        });
        // Clear new images and images to delete when exiting edit mode
        newImages.value = [];
        hasUnsavedChanges.value = false;
    }

    isEditMode.value = !isEditMode.value;
};

const startEditing = (field: string) => {
    editingField.value = field;
};

const cancelEditing = () => {
    editingField.value = null;
};

const saveField = async (field: string) => {
    isLoading.value = true;

    try {
        await router.patch(`/properties/${props.property.id}`, {
            [field]: form[field as keyof typeof form]
        }, {
            onSuccess: () => {
                editingField.value = null;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            }
        });
    } catch {
        isLoading.value = false;
    }
};

const saveAllChanges = async () => {
    isLoading.value = true;

    try {
        await router.patch(`/properties/${props.property.id}`, form, {
            onSuccess: () => {
                isEditMode.value = false;
                editingField.value = null;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            }
        });
    } catch {
        isLoading.value = false;
    }
};

const confirmDeleteImage = (imageId: number) => {
  imageToDelete.value = imageId;
  showDeleteConfirmDialog.value = true;
};

const deleteImage = async () => {
  if (!imageToDelete.value || deletingImageId.value) return;

  deletingImageId.value = imageToDelete.value;

  try {
    const response = await axiosInstance.delete(`/property-images/${imageToDelete.value}`);

    if (response.data.success) {
      // Hapus gambar dari propertyImages array
      const idx = propertyImages.value.findIndex(img => img.id === imageToDelete.value);
      if (idx !== -1) {
        propertyImages.value.splice(idx, 1);
      }

      // Tampilkan pesan sukses
      showNotification(response.data.message, 'success');
    } else {
      throw new Error('Response tidak berhasil');
    }
  } catch (error: any) {
    console.error('Delete error:', error);

    // Tampilkan error dialog dengan detail
    let errorMessage = 'Gagal menghapus gambar';

    if (error.response) {
      if (error.response.data && error.response.data.message) {
        errorMessage = error.response.data.message;
      } else {
        errorMessage = error.response.statusText;
      }
    } else if (error.request) {
      errorMessage = 'Tidak ada respons dari server';
    } else {
      errorMessage = error.message;
    }

    showErrorDialog.value = true;
    errorDetails.value = {
      title: 'Gagal Menghapus Gambar',
      message: errorMessage
    };
  } finally {
    deletingImageId.value = null;
    imageToDelete.value = null;
    showDeleteConfirmDialog.value = false;
  }
};

const cancelDelete = () => {
  showDeleteConfirmDialog.value = false;
  imageToDelete.value = null;
};

// HAPUS: Semua fungsi terkait edit gambar, imagesToDelete, newImages, upload/hapus gambar, preview, dan UI edit gambar
// Keyboard shortcuts
const handleKeyboardShortcuts = (event: KeyboardEvent) => {
    if ((event.ctrlKey || event.metaKey) && isEditMode.value) {
        switch(event.key) {
            case 's':
                event.preventDefault();
                if (false) { // No unsaved changes for images
                    saveAllChanges();
                } else {
                    saveAllChanges();
                }
                break;
            case 'z':
                event.preventDefault();
                if (event.shiftKey) {
                    // Ctrl+Shift+Z = Redo (not implemented yet)
                } else {
                    // Ctrl+Z = Undo (not implemented yet)
                }
                break;
        }
    }
};

// Setup keyboard shortcuts
onMounted(() => {
    document.addEventListener('keydown', handleKeyboardShortcuts);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyboardShortcuts);
});
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Properti', href: '/properties' },
        { title: property.name, href: `/properties/${property.id}` }
    ]">
        <!-- Notification Toast -->
        <div
            v-if="notification.show"
            :class="[
                'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 max-w-sm',
                notification.type === 'success' ? 'bg-green-500 text-white' : '',
                notification.type === 'error' ? 'bg-red-500 text-white' : '',
                notification.type === 'info' ? 'bg-blue-500 text-white' : ''
            ]"
        >
            <div class="flex items-center gap-2">
                <span v-if="notification.type === 'success'" class="text-lg">✅</span>
                <span v-else-if="notification.type === 'error'" class="text-lg">❌</span>
                <span v-else class="text-lg">ℹ️</span>
                <span class="text-sm font-medium">{{ notification.message }}</span>
            </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog :open="showDeleteConfirmDialog" @update:open="showDeleteConfirmDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <Trash2 class="h-5 w-5 text-red-500" />
                        Konfirmasi Hapus Gambar
                    </DialogTitle>
                    <DialogDescription>
                        Apakah Anda yakin ingin menghapus gambar ini? Tindakan ini tidak dapat dibatalkan.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex gap-2">
                    <Button variant="outline" @click="cancelDelete" :disabled="deletingImageId !== null">
                        Batal
                    </Button>
                    <Button
                        variant="destructive"
                        @click="deleteImage"
                        :disabled="deletingImageId !== null"
                        class="flex items-center gap-2"
                    >
                        <Trash2 v-if="deletingImageId === null" class="h-4 w-4" />
                        <span v-else class="h-4 w-4 animate-spin">⏳</span>
                        {{ deletingImageId !== null ? 'Menghapus...' : 'Hapus Gambar' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Error Dialog -->
        <Dialog :open="showErrorDialog" @update:open="showErrorDialog = $event">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2 text-red-600">
                        <X class="h-5 w-5" />
                        {{ errorDetails.title }}
                    </DialogTitle>
                    <DialogDescription class="text-left">
                        {{ errorDetails.message }}
                    </DialogDescription>
                </DialogHeader>

                <!-- Validation Errors -->
                <div v-if="errorDetails.errors && Object.keys(errorDetails.errors).length > 0" class="mt-4">
                    <h4 class="font-medium text-sm text-red-600 mb-2">Detail Error:</h4>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <div
                            v-for="(errors, field) in errorDetails.errors"
                            :key="field"
                            class="text-sm"
                        >
                            <div class="font-medium text-red-600 capitalize">{{ field.replace('_', ' ') }}:</div>
                            <ul class="list-disc list-inside text-red-500 ml-2">
                                <li v-for="error in errors" :key="error">{{ error }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button @click="showErrorDialog = false" class="w-full">
                        Tutup
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <div class="w-full p-6">
            <div class="w-full max-w-6xl mx-auto">
                <!-- Header -->
                <div class="flex items-center gap-4 mb-6">
                    <Button variant="outline" size="sm" @click="router.get('/properties')">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali
                    </Button>
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <div v-if="editingField === 'name'" class="flex items-center gap-2">
                                <Input
                                    v-model="form.name"
                                    class="text-3xl font-bold h-12 text-3xl"
                                    placeholder="Nama properti"
                                />
                                <Button size="sm" @click="saveField('name')" :disabled="isLoading">
                                    <Save class="h-4 w-4" />
                                </Button>
                                <Button size="sm" variant="outline" @click="cancelEditing">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <h1 v-else class="text-3xl font-bold flex items-center gap-2">
                                {{ property.name }}
                                <Button
                                    v-if="isEditMode"
                                    size="sm"
                                    variant="ghost"
                                    @click="startEditing('name')"
                                >
                                    <Edit class="h-4 w-4" />
                                </Button>
                            </h1>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <Badge v-if="property.hotel_category" variant="secondary">
                                {{ property.hotel_category.charAt(0).toUpperCase() + property.hotel_category.slice(1) }}
                            </Badge>
                            <div v-if="property.star_rating" class="flex items-center gap-1">
                                <Star class="h-4 w-4 text-yellow-500 fill-current" />
                                <span class="text-sm font-medium">{{ property.star_rating }} Bintang</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            v-if="!isEditMode"
                            @click="toggleEditMode"
                            variant="outline"
                        >
                            <Edit class="h-4 w-4 mr-2" />
                            Edit Properti
                        </Button>
                        <div v-else class="flex gap-2">
                            <Button @click="saveAllChanges" :disabled="isLoading">
                                <Save class="h-4 w-4 mr-2" />
                                {{ isLoading ? 'Menyimpan...' : 'Simpan Semua' }}
                            </Button>
                            <Button variant="outline" @click="toggleEditMode">
                                <X class="h-4 w-4 mr-2" />
                                Batal
                            </Button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Gambar Properti -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center justify-between">
                                    Foto Properti
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <!-- Existing Images -->
                                <div v-if="propertyImages && propertyImages.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div
                                        v-for="image in propertyImages"
                                        :key="image.id"
                                        class="relative group overflow-hidden rounded-lg"
                                    >
                                        <img
                                            :src="`/storage/${image.image}`"
                                            :alt="`${property.name} - Image ${image.id}`"
                                            class="w-full h-48 object-cover transition-transform group-hover:scale-105"
                                        />
                                        <button v-if="isEditMode" @click="confirmDeleteImage(image.id)" class="absolute top-2 right-2 bg-white/80 hover:bg-red-500 hover:text-white text-red-500 rounded-full p-1 shadow transition-colors z-10" :disabled="deletingImageId === image.id">
                                            <Trash2 v-if="deletingImageId !== image.id" class="h-4 w-4" />
                                            <span v-else class="h-4 w-4 animate-spin">⏳</span>
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="text-muted-foreground text-sm">Belum ada gambar properti.</div>

                                <div v-if="isEditMode" class="mt-6 border-t pt-6">
                                    <h4 class="font-medium mb-4">Upload Gambar Baru</h4>
                                    <div v-if="propertyImages.length + newImages.length >= 5" class="text-sm text-red-500 mb-4">
                                        Maksimal 5 gambar. Hapus beberapa gambar terlebih dahulu.
                                    </div>
                                    <ImageUpload
                                        v-model="newImages"
                                        :max-files="5 - propertyImages.length"
                                        :max-size="5"
                                        @filesAdded="handleNewImagesAdded"
                                        :key="`upload-${newImages.length}`"
                                    />

                                    <!-- Preview gambar baru -->
                                    <div v-if="newImages.length > 0" class="mt-4 flex justify-end">
                                        <Button @click="uploadNewImages" :disabled="isUploading" size="sm">
                                            {{ isUploading ? 'Uploading...' : 'Upload Semua' }}
                                        </Button>
                                    </div>
                                </div>


                            </CardContent>
                        </Card>

                        <!-- Deskripsi -->
                        <Card v-if="property.description || isEditMode">
                            <CardHeader>
                                <CardTitle class="flex items-center justify-between">
                                    Deskripsi
                                    <Button
                                        v-if="isEditMode && !property.description"
                                        size="sm"
                                        variant="outline"
                                        @click="startEditing('description')"
                                    >
                                        <Edit class="h-4 w-4 mr-2" />
                                        Tambah Deskripsi
                                    </Button>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div v-if="editingField === 'description'" class="space-y-2">
                                    <div class="relative">
                                        <textarea
                                            v-model="form.description"
                                            rows="3"
                                            maxlength="255"
                                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            placeholder="Deskripsi singkat tentang properti Anda"
                                        ></textarea>
                                        <div class="absolute bottom-2 right-2 text-xs text-muted-foreground">
                                            {{ form.description.length }}/255
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <Button size="sm" @click="saveField('description')" :disabled="isLoading">
                                            <Save class="h-4 w-4 mr-2" />
                                            Simpan
                                        </Button>
                                        <Button size="sm" variant="outline" @click="cancelEditing">
                                            <X class="h-4 w-4 mr-2" />
                                            Batal
                                        </Button>
                                    </div>
                                </div>
                                <div v-else class="flex items-start justify-between">
                                    <p v-if="property.description" class="text-muted-foreground leading-relaxed">
                                        {{ property.description }}
                                    </p>
                                    <Button
                                        v-if="isEditMode && property.description"
                                        size="sm"
                                        variant="ghost"
                                        @click="startEditing('description')"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Informasi Detail -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Informasi Detail</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-semibold text-primary">{{ property.total_rooms || 0 }}</span>
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">Total Kamar</p>
                                                <div v-if="editingField === 'total_rooms'" class="flex items-center gap-2 mt-1">
                                                    <Input
                                                        v-model="form.total_rooms"
                                                        type="number"
                                                        min="1"
                                                        placeholder="Jumlah kamar"
                                                        class="flex-1"
                                                    />
                                                    <Button size="sm" @click="saveField('total_rooms')" :disabled="isLoading">
                                                        <Save class="h-4 w-4" />
                                                    </Button>
                                                    <Button size="sm" variant="outline" @click="cancelEditing">
                                                        <X class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                <div v-else class="flex items-center justify-between">
                                                    <p class="text-sm text-muted-foreground">
                                                        {{ property.total_rooms || 0 }} kamar
                                                    </p>
                                                    <Button
                                                        v-if="isEditMode"
                                                        size="sm"
                                                        variant="ghost"
                                                        @click="startEditing('total_rooms')"
                                                    >
                                                        <Edit class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <Phone class="w-5 h-5 text-muted-foreground" />
                                            <div class="flex-1">
                                                <p class="font-medium">Telepon</p>
                                                <div v-if="editingField === 'phone'" class="flex items-center gap-2 mt-1">
                                                    <Input
                                                        v-model="form.phone"
                                                        placeholder="+62 812-3456-7890"
                                                        class="flex-1"
                                                    />
                                                    <Button size="sm" @click="saveField('phone')" :disabled="isLoading">
                                                        <Save class="h-4 w-4" />
                                                    </Button>
                                                    <Button size="sm" variant="outline" @click="cancelEditing">
                                                        <X class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                <div v-else class="flex items-center justify-between">
                                                    <p class="text-sm text-muted-foreground">
                                                        {{ property.phone || 'Belum diisi' }}
                                                    </p>
                                                    <Button
                                                        v-if="isEditMode"
                                                        size="sm"
                                                        variant="ghost"
                                                        @click="startEditing('phone')"
                                                    >
                                                        <Edit class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="property.created_at" class="flex items-center gap-3">
                                            <Calendar class="w-5 h-5 text-muted-foreground" />
                                            <div>
                                                <p class="font-medium">Tanggal Dibuat</p>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ new Date(property.created_at).toLocaleDateString('id-ID', {
                                                        year: 'numeric',
                                                        month: 'long',
                                                        day: 'numeric'
                                                    }) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1">
                                                <Star
                                                    v-for="i in 5"
                                                    :key="i"
                                                    class="w-4 h-4"
                                                    :class="i <= (property.star_rating || 0) ? 'text-yellow-500 fill-current' : 'text-gray-300'"
                                                />
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium">Rating</p>
                                                <div v-if="editingField === 'star_rating'" class="flex items-center gap-2 mt-1">
                                                    <StarRating
                                                        v-model="form.star_rating"
                                                        :max="5"
                                                        size="md"
                                                    />
                                                    <Button size="sm" @click="saveField('star_rating')" :disabled="isLoading">
                                                        <Save class="h-4 w-4" />
                                                    </Button>
                                                    <Button size="sm" variant="outline" @click="cancelEditing">
                                                        <X class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                <div v-else class="flex items-center justify-between">
                                                    <p class="text-sm text-muted-foreground">
                                                        {{ property.star_rating || 0 }} dari 5 bintang
                                                    </p>
                                                    <Button
                                                        v-if="isEditMode"
                                                        size="sm"
                                                        variant="ghost"
                                                        @click="startEditing('star_rating')"
                                                    >
                                                        <Edit class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <Badge variant="outline">
                                                {{ (property.hotel_category || 'hotel').charAt(0).toUpperCase() + (property.hotel_category || 'hotel').slice(1) }}
                                            </Badge>
                                            <div class="flex-1">
                                                <p class="font-medium">Kategori</p>
                                                <div v-if="editingField === 'hotel_category'" class="flex items-center gap-2 mt-1">
                                                    <Select v-model="form.hotel_category" class="flex-1">
                                                        <SelectTrigger>
                                                            <SelectValue placeholder="Pilih kategori hotel" />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="category in hotelCategories"
                                                                :key="category.value"
                                                                :value="category.value"
                                                            >
                                                                {{ category.label }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                    <Button size="sm" @click="saveField('hotel_category')" :disabled="isLoading">
                                                        <Save class="h-4 w-4" />
                                                    </Button>
                                                    <Button size="sm" variant="outline" @click="cancelEditing">
                                                        <X class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                                <div v-else class="flex items-center justify-between">
                                                    <p class="text-sm text-muted-foreground">
                                                        {{ property.hotel_category === 'budget' ? 'Budget' :
                                                           property.hotel_category === 'mid-range' ? 'Mid-Range' :
                                                           property.hotel_category === 'luxury' ? 'Luxury' : 'Hotel' }}
                                                    </p>
                                                    <Button
                                                        v-if="isEditMode"
                                                        size="sm"
                                                        variant="ghost"
                                                        @click="startEditing('hotel_category')"
                                                    >
                                                        <Edit class="h-4 w-4" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Alamat -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center gap-2">
                                    <MapPin class="w-5 h-5" />
                                    Alamat
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div class="text-sm">
                                        <span class="font-medium">Alamat:</span>
                                        <div v-if="editingField === 'address'" class="mt-1">
                                            <textarea
                                                v-model="form.address"
                                                rows="2"
                                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                                placeholder="Jl. Contoh No. 123"
                                            ></textarea>
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('address')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-start justify-between">
                                            <p class="text-muted-foreground mt-1">
                                                {{ property.address || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('address')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <span class="font-medium">Kota:</span>
                                        <div v-if="editingField === 'city'" class="mt-1">
                                            <Input
                                                v-model="form.city"
                                                placeholder="Jakarta"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('city')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.city || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('city')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <span class="font-medium">Provinsi:</span>
                                        <div v-if="editingField === 'state'" class="mt-1">
                                            <Input
                                                v-model="form.state"
                                                placeholder="DKI Jakarta"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('state')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.state || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('state')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <span class="font-medium">Kode Pos:</span>
                                        <div v-if="editingField === 'zip'" class="mt-1">
                                            <Input
                                                v-model="form.zip"
                                                placeholder="12345"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('zip')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.zip || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('zip')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <span class="font-medium">Negara:</span>
                                        <div v-if="editingField === 'country'" class="mt-1">
                                            <Input
                                                v-model="form.country"
                                                placeholder="Indonesia"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('country')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.country || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('country')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Koordinat -->
                        <Card v-if="property.latitude && property.longitude || isEditMode">
                            <CardHeader>
                                <CardTitle class="flex items-center justify-between">
                                    Koordinat
                                    <Button
                                        v-if="isEditMode && !property.latitude && !property.longitude"
                                        size="sm"
                                        variant="outline"
                                        @click="startEditing('latitude')"
                                    >
                                        <Edit class="h-4 w-4 mr-2" />
                                        Tambah Koordinat
                                    </Button>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4 text-sm">
                                    <div>
                                        <span class="font-medium">Latitude:</span>
                                        <div v-if="editingField === 'latitude'" class="mt-1">
                                            <Input
                                                v-model="form.latitude"
                                                type="number"
                                                step="any"
                                                placeholder="-6.2088"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('latitude')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.latitude || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('latitude')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="font-medium">Longitude:</span>
                                        <div v-if="editingField === 'longitude'" class="mt-1">
                                            <Input
                                                v-model="form.longitude"
                                                type="number"
                                                step="any"
                                                placeholder="106.8456"
                                            />
                                            <div class="flex gap-2 mt-2">
                                                <Button size="sm" @click="saveField('longitude')" :disabled="isLoading">
                                                    <Save class="h-4 w-4 mr-1" />
                                                    Simpan
                                                </Button>
                                                <Button size="sm" variant="outline" @click="cancelEditing">
                                                    <X class="h-4 w-4 mr-1" />
                                                    Batal
                                                </Button>
                                            </div>
                                        </div>
                                        <div v-else class="flex items-center justify-between">
                                            <p class="text-muted-foreground">
                                                {{ property.longitude || 'Belum diisi' }}
                                            </p>
                                            <Button
                                                v-if="isEditMode"
                                                size="sm"
                                                variant="ghost"
                                                @click="startEditing('longitude')"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>


                    </div>
                </div>
            </div>
        </div>
        <!-- Auto-save indicator -->
        <div v-if="hasUnsavedChanges && isEditMode" class="fixed bottom-4 right-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-2 rounded-lg shadow-lg z-50">
            <div class="flex items-center gap-2">
                <div class="animate-pulse w-2 h-2 bg-yellow-500 rounded-full"></div>
                <p class="text-sm font-medium">Ada perubahan yang belum disimpan</p>
                <Button size="sm" variant="outline" @click="saveAllChanges" :disabled="false">
                    Simpan
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
