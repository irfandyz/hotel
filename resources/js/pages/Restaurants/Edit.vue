<script setup lang="ts">
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { SingleImageUpload } from '@/components/ui/single-image-upload';

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
    property_id: props.menuItem.property_id as number,
    name: props.menuItem.name,
    description: props.menuItem.description || '',
    price: props.menuItem.price.toString(),
    status: props.menuItem.status,
    categories: props.menuItem.categories.map(cat => cat.id),
});

// Form khusus untuk gambar
const imageForm = useForm({
    image: null as File | null,
});

const selectedProperty = computed(() =>
    props.properties.find((p: Property) => p.id === form.property_id)
);

const toggleCategory = (categoryId: number, checked: boolean) => {
    const idx = form.categories.indexOf(categoryId);
    if (checked && idx === -1) {
        form.categories.push(categoryId);
    } else if (!checked && idx > -1) {
        form.categories.splice(idx, 1);
    }
};

const submit = () => {
    form.put(`/restaurants/${props.menuItem.id}`, {
        onSuccess: () => {
            // Success message will be handled by Inertia
        },
    });
};

const submitImage = () => {
    if (!imageForm.image) return;
    imageForm.post(`/restaurants/${props.menuItem.id}/image`, {
        forceFormData: true,
        onSuccess: () => {
            imageForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Restaurants', href: '/restaurants' },
        { title: 'Edit Menu', href: `/restaurants/${menuItem.id}/edit` }
    ]">
        <div class="max-w-4xl container mx-auto mt-6">
            <!-- Header -->
            <div class="">
                <div class="flex items-center justify-between space-x-4">
                    <Button variant="outline" @click="router.get('/restaurants', {
                        property_id: selectedProperty?.id
                    })">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Edit Menu</h3>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="mt-6">
                <Card class="border-none shadow-none">
                    <CardHeader>
                        <CardTitle>Menu Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Property Selection -->
                            <div>
                                <label class="text-sm font-medium text-gray-700 mb-1 block">
                                    Property
                                </label>
                                <div class="rounded border px-3 py-2 bg-gray-200">
                                    <span class="text-sm font-medium text-gray-700">{{ selectedProperty?.name }}</span>
                                </div>
                                <input type="hidden" v-model="form.property_id" />
                                <p v-if="form.errors.property_id" class="text-xs text-red-500 mt-1">
                                    {{ form.errors.property_id }}
                                </p>
                            </div>

                        <!-- Menu Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Menu Name *
                            </label>
                            <Input
                                v-model="form.name"
                                placeholder="Enter menu name..."
                                :class="{ 'border-red-500': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter menu description..."
                                :class="{ 'border-red-500': form.errors.description }"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-red-500 text-sm mt-1">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Price -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Price *
                            </label>
                            <div class="flex">
                                <div class="flex items-center px-3 py-2 bg-gray-100 border border-r-0 border-gray-300 rounded-l-md text-sm font-medium text-gray-700 min-w-[3rem] justify-center">
                                    Rp
                                </div>
                                <Input
                                    v-model="form.price"
                                    type="number"
                                    placeholder="1"
                                    class="rounded-l-none focus:z-10"
                                    :min="1"
                                    :class="{ 'border-red-500 focus:ring-red-500': form.errors.price, 'border-gray-300': !form.errors.price }"
                                />
                            </div>
                            <p v-if="form.errors.price" class="text-red-500 text-sm mt-1">
                                {{ form.errors.price }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status *
                            </label>
                            <select v-model="form.status" class="w-full h-10 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <option value="enabled">Active</option>
                                <option value="disabled">Inactive</option>
                            </select>
                            <p v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                                {{ form.errors.status }}
                            </p>
                        </div>



                        <!-- Categories -->
                        <div>
                            <label class="text-sm font-medium text-gray-700 mb-1 block">
                                Categories
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
                                        :checked="form.categories.includes(category.id)"
                                        @change="(e) => {
                                            const target = e.target as HTMLInputElement;
                                            toggleCategory(category.id, target.checked);
                                        }"
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    />
                                    <label :for="`category-${category.id}`" class="text-sm">
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
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                <Save class="w-4 h-4 mr-2" />
                                {{ form.processing ? 'Saving...' : 'Update Menu' }}
                            </Button>
                        </div>
                    </form>
                    <hr class="my-4">
                    <!-- Form khusus update gambar -->
                    <div class="mt-8 w-full border rounded-lg bg-gray-50 p-5 flex flex-col gap-4 shadow-sm">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-1">
                            <svg xmlns='http://www.w3.org/2000/svg' class='w-5 h-5 text-blue-500' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3H8a2 2 0 002-2V7M16 3H8a2 2 0 00-2 2v0a2 2 0 002 2h8a2 2 0 002-2v0a2 2 0 00-2-2z' /></svg>
                            Update Menu Image
                        </label>

                        <SingleImageUpload
                            v-model="imageForm.image"
                            placeholder="Upload Menu Image"
                            accept="image/*"
                            :max-size="5"
                            :existing-image="menuItem.image ? `/storage/${menuItem.image}` : null"
                        />

                        <p v-if="imageForm.errors.image" class="text-red-500 text-xs mt-1">
                            {{ imageForm.errors.image }}
                        </p>

                        <Button
                            @click="submitImage"
                            :disabled="imageForm.processing || !imageForm.image"
                            class="bg-green-600 hover:bg-green-700 w-fit ms-auto mt-4"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            {{ imageForm.processing ? 'Saving...' : 'Update Image' }}
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
        </div>
    </AppLayout>
</template>
