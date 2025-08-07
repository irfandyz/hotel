<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Plus, Eye } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Property {
    id: number;
    name: string;
    hotel_category?: string;
    star_rating?: number;
    total_rooms?: number;
    description?: string;
    address?: string;
    city?: string;
    phone?: string;
    created_at: string;
}

interface Props {
    properties: Property[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Hotel', href: '/properties' }
    ]">
        <div class="container mx-auto p-6">
            <div class="flex items-center justify-between mb-6">
                            <div>
                <h1 class="text-3xl font-bold">Hotel Saya</h1>
                <p class="text-muted-foreground">Kelola semua hotel Anda</p>
            </div>
                <Button @click="router.get('/properties/create')">
                    <Plus class="h-4 w-4 mr-2" />
                    Tambah Hotel
                </Button>
            </div>

            <div v-if="properties.length === 0" class="text-center py-12">
                <div class="text-muted-foreground">
                    <p class="text-lg mb-2">Belum ada hotel</p>
                    <p>Mulai dengan menambahkan hotel pertama Anda</p>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="property in properties" :key="property.id" class="hover:shadow-lg transition-shadow">
                    <CardHeader>
                        <CardTitle>{{ property.name }}</CardTitle>
                        <CardDescription>
                            {{ property.hotel_category ? property.hotel_category.charAt(0).toUpperCase() + property.hotel_category.slice(1) : 'Hotel' }}
                            {{ property.star_rating ? `• ${property.star_rating}⭐` : '' }}
                            {{ property.total_rooms ? `• ${property.total_rooms} kamar` : '' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-if="property.description" class="text-sm text-muted-foreground">
                                {{ property.description }}
                            </div>
                            <div v-if="property.address" class="text-sm">
                                <span class="font-medium">Alamat:</span> {{ property.address }}
                            </div>
                            <div v-if="property.city" class="text-sm">
                                <span class="font-medium">Kota:</span> {{ property.city }}
                            </div>
                            <div v-if="property.phone" class="text-sm">
                                <span class="font-medium">Telepon:</span> {{ property.phone }}
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="router.get(`/properties/${property.id}`)"
                            >
                                <Eye class="h-4 w-4 mr-2" />
                                Detail
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
