<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ImageUpload } from '@/components/ui/image-upload';
import { ArrowLeft, Save } from 'lucide-vue-next';

interface Property {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface MenuItem {
    id: number;
    property_id: number;
    name: string;
    description: string | null;
    image: string | null;
    price: number;
    status: 'enabled' | 'disabled';
    categories: Category[];
}

interface Props {
    menuItem: MenuItem;
    properties: Property[];
    categories: Category[];
}

const props = defineProps<Props>();

const form = useForm({
    property_id: props.menuItem.property_id.toString(),
    name: props.menuItem.name,
    description: props.menuItem.description || '',
    price: props.menuItem.price.toString(),
    status: props.menuItem.status,
    categories: props.menuItem.categories.map(cat => cat.id),
    image: null as File | null,
});

const toggleCategory = (categoryId: number) => {
    const index = form.categories.indexOf(categoryId);
    if (index > -1) {
        form.categories.splice(index, 1);
    } else {
        form.categories.push(categoryId);
    }
};

const handleImageChange = (file: File | null) => {
    form.image = file;
};

const submit = () => {
    form.put(`/restaurants/${props.menuItem.id}`, {
        onSuccess: () => {
            // Success message will be handled by Inertia
        },
    });
};

const getImageUrl = (imagePath: string | null) => {
    if (!imagePath) return '/placeholder-food.jpg';
    return `/storage/${imagePath}`;
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Restoran', href: '/restaurants' },
        { title: 'Edit Menu', href: `/restaurants/${menuItem.id}/edit` }
    ]">
        <div class="container mx-auto p-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <Button variant="outline" @click="router.get('/restaurants')">
                    <ArrowLeft class="w-4 h-4 mr-2" />
                    Kembali
                </Button>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Menu</h1>
                    <p class="text-gray-600 mt-1">Edit informasi menu restoran</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Informasi Menu</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Property Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Properti *
                            </label>
                            <Select v-model="form.property_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Pilih properti..." />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="property in properties"
                                        :key="property.id"
                                        :value="property.id.toString()"
                                    >
                                        {{ property.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.property_id" class="text-red-500 text-sm mt-1">
                                {{ form.errors.property_id }}
                            </p>
                        </div>

                        <!-- Menu Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nama Menu *
                            </label>
                            <Input
                                v-model="form.name"
                                placeholder="Masukkan nama menu..."
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Deskripsi
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Masukkan deskripsi menu..."
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Harga *
                            </label>
                            <Input
                                v-model="form.price"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="0.00"
                                :class="{ 'border-red-500': form.errors.price }"
                            />
                            <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status *
                            </label>
                            <Select v-model="form.status">
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="enabled">Aktif</SelectItem>
                                    <SelectItem value="disabled">Nonaktif</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Current Image -->
                        <div v-if="menuItem.image">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Gambar Saat Ini
                            </label>
                            <div class="w-32 h-32 rounded-lg overflow-hidden border">
                                <img
                                    :src="getImageUrl(menuItem.image)"
                                    :alt="menuItem.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ menuItem.image ? 'Ganti Gambar' : 'Gambar Menu' }}
                            </label>
                            <ImageUpload
                                :value="form.image"
                                @update:value="handleImageChange"
                                accept="image/*"
                                class="w-full"
                            />
                            <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                                {{ form.errors.image }}
                            </p>
                        </div>

                        <!-- Categories -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Kategori
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div
                                    v-for="category in categories"
                                    :key="category.id"
                                    class="flex items-center space-x-2"
                                >
                                    <Checkbox
                                        :id="`category-${category.id}`"
                                        :checked="form.categories.includes(category.id)"
                                        @update:checked="toggleCategory(category.id)"
                                    />
                                    <label :for="`category-${category.id}`" class="text-sm">
                                        {{ category.name }}
                                    </label>
                                </div>
                            </div>
                            <p v-if="form.errors.categories" class="text-red-500 text-sm mt-1">
                                {{ form.errors.categories }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-3">
                            <Button
                                type="button"
                                variant="outline"
                                @click="router.get('/restaurants')"
                            >
                                Batal
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Menyimpan...' : 'Update Menu' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
        </div>
    </AppLayout>
</template>
