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

interface TvManager {
    id: number;
    property_id: number;
    area_name: string;
    guest_name: string;
    birth_date: string | null;
    image: string | null;
}

interface Props {
    tvManager: TvManager;
    properties: Property[];
}

const props = defineProps<Props>();

const form = useForm({
    property_id: props.tvManager.property_id as number,
    area_name: props.tvManager.area_name,
    guest_name: props.tvManager.guest_name,
    birth_date: '',
});

// Set birth_date after form initialization
if (props.tvManager.birth_date) {
    form.birth_date = new Date(props.tvManager.birth_date).toISOString().split('T')[0];
}

// Form khusus untuk gambar
const imageForm = useForm({
    image: null as File | null,
});

const selectedProperty = computed(() =>
    props.properties.find((p: Property) => p.id === form.property_id)
);

const submit = () => {
    form.put(`/tv-managers/${props.tvManager.id}`, {
        onSuccess: () => {
            // Success message will be handled by Inertia
        },
    });
};

const submitImage = () => {
    if (imageForm.image) {
        imageForm.post(`/tv-managers/${props.tvManager.id}/image`, {
            forceFormData: true,
            onSuccess: () => {
                imageForm.reset();
            },
        });
    }
};


</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'TV Managers', href: '/tv-managers' },
        { title: 'Edit Guest', href: `/tv-managers/${tvManager.id}/edit` }
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
                        <h3 class="text-3xl font-bold text-gray-900">Edit Guest</h3>
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
                                    {{ form.processing ? 'Saving...' : 'Update Guest' }}
                                </Button>
                            </div>
                        </form>

                        <hr class="my-4">
                        <!-- Form khusus update gambar -->
                        <div class="mt-8 w-full border rounded-lg bg-gray-50 p-5 flex flex-col gap-4 shadow-sm">
                            <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-1">
                                <svg xmlns='http://www.w3.org/2000/svg' class='w-5 h-5 text-blue-500' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3H8a2 2 0 00-2 2v0a2 2 0 002 2h8a2 2 0 002-2v0a2 2 0 00-2-2z' /></svg>
                                Update Guest Photo
                            </label>

                            <SingleImageUpload
                                v-model="imageForm.image"
                                    placeholder="Upload Guest Photo"
                                accept="image/*"
                                :max-size="5"
                                :existing-image="tvManager.image ? `/storage/${tvManager.image}` : null"
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
                                {{ imageForm.processing ? 'Saving...' : 'Update Photo' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
