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
import axios from 'axios';
import { ImageUpload } from '@/components/ui/image-upload';

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

    // Get CSRF token from meta tag
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const response = await axios.post(`/properties/${props.property.id}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token || '',
      },
    });

    console.log('Upload success:', response.data);

    // Clear the newImages array after successful upload
    newImages.value = [];

    // Reload halaman untuk mendapatkan gambar terbaru
    window.location.reload();
  } catch (error: any) {
    console.error('Upload error:', error);

    // Tampilkan error yang lebih informatif
    if (error.response) {
      // Server responded with error status
      console.error('Error response:', error.response.data);
      if (error.response.data && error.response.data.message) {
        alert(`Upload gagal: ${error.response.data.message}`);
      } else if (error.response.data && error.response.data.errors) {
        const errorMessages = Object.values(error.response.data.errors).flat();
        alert(`Upload gagal: ${errorMessages.join(', ')}`);
      } else {
        alert(`Upload gagal: ${error.response.statusText}`);
      }
    } else if (error.request) {
      // Request was made but no response received
      alert('Upload gagal: Tidak ada respons dari server');
    } else {
      // Something else happened
      alert(`Upload gagal: ${error.message}`);
    }
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

const deleteImage = async (imageId: number) => {
  if (deletingImageId.value) return;
  deletingImageId.value = imageId;
  try {
    await router.delete(`/property-images/${imageId}`, {
      onSuccess: () => {
        // Hapus gambar dari propertyImages
        const idx = propertyImages.value.findIndex(img => img.id === imageId);
        if (idx !== -1) propertyImages.value.splice(idx, 1);
      },
      onError: () => {
        alert('Gagal menghapus gambar');
      }
    });
  } catch (error) {
    console.error('Delete error:', error);
    alert('Gagal menghapus gambar');
  }
  deletingImageId.value = null;
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
                                        <button v-if="isEditMode" @click="deleteImage(image.id)" class="absolute top-2 right-2 bg-white/80 hover:bg-red-500 hover:text-white text-red-500 rounded-full p-1 shadow transition-colors z-10" :disabled="deletingImageId === image.id">
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
