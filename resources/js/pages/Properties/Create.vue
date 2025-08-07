<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { StarRating } from '@/components/ui/star-rating';
import { ImageUpload } from '@/components/ui/image-upload';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, Save } from 'lucide-vue-next';
import SimpleSelect from '@/components/ui/select/SimpleSelect.vue';

const form = ref({
    name: '',
    description: '',
    phone: '',
    address: '',
    city: '',
    state: '',
    zip: '',
    country: '',
    latitude: '',
    longitude: '',
    star_rating: 0,
    total_rooms: '',
    hotel_category: '',
    images: [] as File[]
});

const isLoading = ref(false);

const hotelCategories = [
    { value: 'budget', label: 'Budget' },
    { value: 'mid-range', label: 'Mid-Range' },
    { value: 'luxury', label: 'Luxury' }
];

const submitForm = () => {
    isLoading.value = true;

    // Create FormData for file upload
    const formData = new FormData();

    // Add all form fields
    Object.keys(form.value).forEach(key => {
        if (key === 'images') {
            // Handle images array
            form.value.images.forEach((file: File, index: number) => {
                formData.append(`images[${index}]`, file);
            });
        } else {
            formData.append(key, String(form.value[key as keyof typeof form.value]));
        }
    });

    router.post('/properties', formData, {
        onSuccess: () => {
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Properti', href: '/properties' },
        { title: 'Tambah Properti', href: '/properties/create' }
    ]">
        <div class="w-full p-6">
            <div class="w-full max-w-4xl mx-auto">
                <div class="flex items-center gap-4 mb-6 justify-between">
                    <Button variant="outline" size="sm" @click="router.get('/properties')">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Kembali
                    </Button>
                    <div>
                        <h3 class="text-3xl font-bold">Tambah Properti</h3>
                    </div>
                </div>

                <Card class="w-full shadow-none border-none">
                    <CardHeader>
                        <CardTitle>Informasi Properti</CardTitle>
                        <CardDescription>
                            Masukkan detail properti yang akan Anda kelola
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Informasi Dasar -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Informasi Dasar</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="name">Nama Properti *</Label>
                                        <Input
                                            id="name"
                                            v-model="form.name"
                                            placeholder="Masukkan nama properti"
                                            required
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="hotel_category">Kategori Properti</Label>
                                        <SimpleSelect
                                            v-model="form.hotel_category"
                                            :options="hotelCategories"
                                            placeholder="Pilih kategori properti"
                                            :disabled="isLoading"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Deskripsi</Label>
                                    <div class="relative">
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="3"
                                            maxlength="255"
                                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            placeholder="Deskripsi singkat tentang properti Anda"
                                        ></textarea>
                                        <div
                                            class="absolute bottom-2 right-2 text-xs transition-colors"
                                            :class="[
                                                form.description.length > 240 ? 'text-red-500' :
                                                form.description.length > 200 ? 'text-yellow-500' :
                                                'text-muted-foreground'
                                            ]"
                                        >
                                            {{ form.description.length }}/255
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label>Rating Bintang</Label>
                                        <div class="flex items-center gap-4">
                                            <StarRating
                                                v-model="form.star_rating"
                                                :max="5"
                                                size="lg"
                                            />
                                            <div class="flex flex-col">
                                                <span class="text-sm text-muted-foreground">
                                                    {{ form.star_rating || 0 }} dari 5 bintang
                                                </span>
                                                <span class="text-xs text-muted-foreground">
                                                    {{ form.star_rating === 1 ? 'Sangat Buruk' :
                                                       form.star_rating === 2 ? 'Buruk' :
                                                       form.star_rating === 3 ? 'Cukup' :
                                                       form.star_rating === 4 ? 'Baik' :
                                                       form.star_rating === 5 ? 'Sangat Baik' :
                                                       'Pilih rating' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="total_rooms">Total Kamar</Label>
                                        <Input
                                            id="total_rooms"
                                            v-model="form.total_rooms"
                                            type="number"
                                            min="1"
                                            placeholder="Jumlah kamar"
                                        />
                                    </div>
                                </div>

                                <!-- Upload Gambar -->
                                <div class="space-y-2">
                                    <Label>Gambar Properti</Label>
                                    <ImageUpload
                                        v-model="form.images"
                                        :max-files="5"
                                        :max-size="5"
                                    />
                                </div>
                            </div>

                            <!-- Informasi Kontak -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Informasi Kontak</h3>

                                <div class="space-y-2">
                                    <Label for="phone">Nomor Telepon</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        placeholder="+62 812-3456-7890"
                                    />
                                </div>
                            </div>

                            <!-- Alamat -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Alamat</h3>

                                <div class="space-y-2">
                                    <Label for="address">Alamat Lengkap</Label>
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        rows="2"
                                        class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                        placeholder="Jl. Contoh No. 123"
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="space-y-2">
                                        <Label for="city">Kota</Label>
                                        <Input
                                            id="city"
                                            v-model="form.city"
                                            placeholder="Jakarta"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="state">Provinsi</Label>
                                        <Input
                                            id="state"
                                            v-model="form.state"
                                            placeholder="DKI Jakarta"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="zip">Kode Pos</Label>
                                        <Input
                                            id="zip"
                                            v-model="form.zip"
                                            placeholder="12345"
                                        />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="country">Negara</Label>
                                    <Input
                                        id="country"
                                        v-model="form.country"
                                        placeholder="Indonesia"
                                    />
                                </div>
                            </div>

                            <!-- Koordinat (Opsional) -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold">Koordinat (Opsional)</h3>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <Label for="latitude">Latitude</Label>
                                        <Input
                                            id="latitude"
                                            v-model="form.latitude"
                                            placeholder="-6.2088"
                                            type="number"
                                            step="any"
                                        />
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="longitude">Longitude</Label>
                                        <Input
                                            id="longitude"
                                            v-model="form.longitude"
                                            placeholder="106.8456"
                                            type="number"
                                            step="any"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Submit -->
                            <div class="flex justify-end gap-4 pt-6 border-t">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.get('/properties')"
                                >
                                    Batal
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="isLoading"
                                >
                                    <Save class="h-4 w-4 mr-2" />
                                    {{ isLoading ? 'Menyimpan...' : 'Simpan Properti' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
