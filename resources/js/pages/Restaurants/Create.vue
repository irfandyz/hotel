<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputGroup } from '@/components/ui/input-group';

import { SingleImageUpload } from '@/components/ui/single-image-upload';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { computed } from 'vue';

interface Property {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Props {
    properties: Property[];
    categories: Category[];
}

const props = defineProps<Props>();

const page = usePage();
const initialPropertyId = page.url.split('?')[1]?.split('&').find(q => q.startsWith('property_id='))?.split('=')[1] || '';

const form = useForm({
    property_id: initialPropertyId,
    name: '',
    description: '',
    price: '',
    status: 'enabled',
    categories: [] as number[],
    image: null as File | null,
});

const toggleCategory = (categoryId: number, checked: boolean) => {
    const idx = form.categories.indexOf(categoryId);
    if (checked && idx === -1) {
        form.categories.push(categoryId);
    } else if (!checked && idx > -1) {
        form.categories.splice(idx, 1);
    }
};



const submit = () => {
    form.post('/restaurants', {
        forceFormData: true,
        onSuccess: () => {
            // Success message will be handled by Inertia
        },
        onError: (errors: any) => {
            console.error('Form errors:', errors);
        }
    });
};

const selectedProperty = computed(() =>
  props.properties.find((p: Property) => p.id.toString() === form.property_id)
);
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Restoran', href: '/restaurants' },
        { title: 'Tambah Menu', href: '/restaurants/create' }
    ]">
        <div class="max-w-4xl container mx-auto mt-6">
            <!-- Header -->
            <div class="">
                <div class="flex items-center justify-between space-x-4">
                    <Button variant="outline" @click="router.get('/restaurants')">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Kembali
                    </Button>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Tambah Menu Baru</h3>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="mt-6">
                <Card class="border-none shadow-none">
                    <CardHeader>
                        <CardTitle>Informasi Menu</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Property Selection -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">
                                    Property
                                </label>
                                <div class="rounded border px-3 py-2  bg-gray-200 ">
                                    <span class="text-sm font-medium text-gray-700">{{ selectedProperty?.name }}</span>
                                </div>
                                <input type="hidden" name="property_id" :value="form.property_id" />
                                <p v-if="form.errors.property_id" class="text-xs text-red-500 mt-1">
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
                                <InputGroup
                                    v-model="form.price"
                                    type="number"
                                    placeholder="1"
                                    left-addon="Rp"
                                    :min="1"
                                    :error="form.errors.price"
                                />
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status *
                                </label>
                                <select v-model="form.status" class="w-full h-10 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="enabled">Aktif</option>
                                    <option value="disabled">Nonaktif</option>
                                </select>
                                <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Gambar Menu
                                </label>
                                                                <SingleImageUpload
                                    v-model="form.image"
                                    placeholder="Upload Gambar Menu"
                                    accept="image/*"
                                    :max-size="5"
                                />

                                <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <!-- Categories -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">
                                    Kategori
                                </label>
                                <div class="grid grid-cols-4 gap-3">
                                    <div
                                        v-for="category in categories"
                                        :key="category.id"
                                        class="flex items-center space-x-2"
                                    >
                                        <input
                                            type="checkbox"
                                            :id="`category-${category.id}`"
                                            :value="category.id"
                                            :checked="form.categories.includes(category.id)"
                                            @change="(e) => {
                                                const target = e.target as HTMLInputElement;
                                                toggleCategory(category.id, target.checked);
                                            }"
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                        />
                                        <label :for="`category-${category.id}`" class="text-sm cursor-pointer">
                                            {{ category.name }}
                                        </label>
                                    </div>
                                </div>

                                <p v-if="form.errors.categories" class="text-xs text-red-500 mt-1">
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
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Menu' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
