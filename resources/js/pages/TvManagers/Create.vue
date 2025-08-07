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

interface Props {
    properties: Property[];
}

const props = defineProps<Props>();

// Get initial property ID from URL query parameter
const initialPropertyId = new URLSearchParams(window.location.search).get('property_id');

const form = useForm({
    property_id: initialPropertyId ? Number(initialPropertyId) : '',
    area_name: '',
    guest_name: '',
    birth_date: '',
    image: null as File | null,
});

const selectedProperty = computed(() =>
    props.properties.find((p: Property) => p.id === form.property_id)
);

const submit = () => {
    form.post('/tv-managers', {
        forceFormData: true,
        onSuccess: () => {
            // Success message will be handled by Inertia
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'TV Managers', href: '/tv-managers' },
        { title: 'Add Guest', href: '/tv-managers/create' }
    ]">
        <div class="max-w-4xl container mx-auto mt-6">
            <!-- Header -->
            <div class="">
                <div class="flex items-center justify-between space-x-4">
                    <Button variant="outline" @click="router.get('/tv-managers', {
                        property_id: selectedProperty?.id
                    })">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back
                    </Button>
                    <div>
                        <h3 class="text-3xl font-bold text-gray-900">Add Guest</h3>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="mt-6">
                <Card class="border-none shadow-none">
                    <CardHeader>
                        <CardTitle>Guest Information</CardTitle>
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

                            <!-- Area Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Area Name *
                                </label>
                                <Input
                                    v-model="form.area_name"
                                    placeholder="Enter area name..."
                                    :class="{ 'border-red-500': form.errors.area_name }"
                                />
                                <p v-if="form.errors.area_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.area_name }}
                                </p>
                            </div>

                            <!-- Guest Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Guest Name *
                                </label>
                                <Input
                                    v-model="form.guest_name"
                                    placeholder="Enter guest name..."
                                    :class="{ 'border-red-500': form.errors.guest_name }"
                                />
                                <p v-if="form.errors.guest_name" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.guest_name }}
                                </p>
                            </div>

                            <!-- Birth Date -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Birth Date
                                </label>
                                <Input
                                    v-model="form.birth_date"
                                    type="date"
                                    :class="{ 'border-red-500': form.errors.birth_date }"
                                />
                                <p v-if="form.errors.birth_date" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.birth_date }}
                                </p>
                            </div>

                            <!-- Image Upload -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Guest Photo
                                </label>
                                <SingleImageUpload
                                    v-model="form.image"
                                    placeholder="Upload Guest Photo"
                                    accept="image/*"
                                    :max-size="5"
                                />
                                <p v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                                    {{ form.errors.image }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="router.get('/tv-managers')"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    <Save class="w-4 h-4 mr-2" />
                                    {{ form.processing ? 'Saving...' : 'Save Guest' }}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
