<script setup lang="ts">
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Plus, Eye, Search, MapPin, Star, Bed } from 'lucide-vue-next';
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
    filters?: {
        search?: string;
        category?: string;
        city?: string;
    };
}

const props = defineProps<Props>();

// Reactive state
const searchQuery = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || '');
const selectedCity = ref(props.filters?.city || '');

// Computed properties
const filteredProperties = computed(() => {
    let filtered = props.properties;

    // Search filter
    if (searchQuery.value) {
        filtered = filtered.filter(property =>
            property.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            property.description?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            property.address?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            property.city?.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(property =>
            property.hotel_category === selectedCategory.value
        );
    }

    // City filter
    if (selectedCity.value) {
        filtered = filtered.filter(property =>
            property.city === selectedCity.value
        );
    }

    return filtered;
});

// Get unique categories and cities
const uniqueCategories = computed(() => {
    const categories = props.properties
        .map(p => p.hotel_category)
        .filter(Boolean) as string[];
    return [...new Set(categories)];
});

const uniqueCities = computed(() => {
    const cities = props.properties
        .map(p => p.city)
        .filter(Boolean) as string[];
    return [...new Set(cities)];
});

// Search function
const applySearch = () => {
    router.get('/properties', {
        search: searchQuery.value,
        category: selectedCategory.value,
        city: selectedCity.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedCity.value = '';
    router.get('/properties', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Hotel', href: '/properties' }
    ]">
        <div class="container mx-auto p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-3xl font-bold">Properti Saya</h1>
                    <p class="text-muted-foreground">Kelola semua properti Anda</p>
                </div>
                <Button @click="router.get('/properties/create')">
                    <Plus class="h-4 w-4 mr-2" />
                    Tambah Properti
                </Button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Cari properti..."
                            class="pl-10"
                            @keyup.enter="applySearch"
                        />
                    </div>
                </div>

                <div class="flex gap-2">
                    <select
                        v-model="selectedCategory"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @change="applySearch"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="category in uniqueCategories" :key="category" :value="category">
                            {{ category.charAt(0).toUpperCase() + category.slice(1) }}
                        </option>
                    </select>

                    <select
                        v-model="selectedCity"
                        class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        @change="applySearch"
                    >
                        <option value="">Semua Kota</option>
                        <option v-for="city in uniqueCities" :key="city" :value="city">
                            {{ city }}
                        </option>
                    </select>

                    <Button variant="outline" @click="clearFilters" class="text-sm">
                        Bersihkan
                    </Button>
                </div>
            </div>

            <!-- Results Count -->
            <div class="mb-4 text-sm text-gray-600">
                {{ filteredProperties.length }} properti ditemukan
            </div>

            <!-- Empty State -->
            <div v-if="filteredProperties.length === 0" class="text-center py-12">
                <div class="text-muted-foreground">
                    <p class="text-lg mb-2">
                        {{ searchQuery || selectedCategory || selectedCity ? 'Tidak ada properti yang sesuai' : 'Belum ada properti' }}
                    </p>
                    <p v-if="!searchQuery && !selectedCategory && !selectedCity">
                        Mulai dengan menambahkan properti pertama Anda
                    </p>
                    <p v-else>
                        Coba ubah filter pencarian Anda
                    </p>
                </div>
            </div>

            <!-- Properties Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Card v-for="property in filteredProperties" :key="property.id" class="hover:shadow-lg transition-shadow group">
                    <CardHeader>
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <CardTitle class="group-hover:text-blue-600 transition-colors">
                                    {{ property.name }}
                                </CardTitle>
                                <CardDescription class="mt-1">
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="font-medium text-gray-700">
                                            {{ property.hotel_category ? property.hotel_category.charAt(0).toUpperCase() + property.hotel_category.slice(1) : 'Hotel' }}
                                        </span>
                                        <div v-if="property.star_rating" class="flex items-center gap-1">
                                            <Star class="w-3 h-3 text-yellow-500 fill-current" />
                                            <span>{{ property.star_rating }}</span>
                                        </div>
                                        <div v-if="property.total_rooms" class="flex items-center gap-1">
                                            <Bed class="w-3 h-3 text-gray-500" />
                                            <span>{{ property.total_rooms }} kamar</span>
                                        </div>
                                    </div>
                                </CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-if="property.description" class="text-sm text-gray-600 line-clamp-2">
                                {{ property.description }}
                            </div>

                            <div class="space-y-2">
                                <div v-if="property.address" class="flex items-start gap-2 text-sm">
                                    <MapPin class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <span class="text-gray-700">{{ property.address }}</span>
                                </div>
                                <div v-if="property.city" class="flex items-center gap-2 text-sm">
                                    <MapPin class="w-4 h-4 text-gray-400" />
                                    <span class="text-gray-700">{{ property.city }}</span>
                                </div>
                                <div v-if="property.phone" class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-400">📞</span>
                                    <span class="text-gray-700">{{ property.phone }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="router.get(`/properties/${property.id}`)"
                                class="group-hover:bg-blue-50 group-hover:border-blue-200 group-hover:text-blue-700 transition-colors"
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

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

