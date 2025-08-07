<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import { Plus, Search, Edit, Trash2 } from 'lucide-vue-next';

interface Property {
    id: number;
    name: string;
}

interface TvManager {
    id: number;
    area_name: string;
    guest_name: string;
    image: string | null;
    birth_date: string | null;
}

interface Props {
    properties: Property[];
    selectedProperty: Property | null;
    tvManagers: {
        data: TvManager[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        property_id: string | null;
        search: string;
    };
}

const props = defineProps<Props>();

const selectedPropertyId = ref<string>('');
const selectOpen = ref(false);
const searchQuery = ref(props.filters.search || '');

// Watch for changes in selectedPropertyId and navigate
watch(selectedPropertyId, (newValue) => {
    if (newValue) {
        nextTick(() => { selectOpen.value = false; }); // paksa close setelah DOM update
        router.get('/tv-managers', {
            property_id: newValue,
            search: searchQuery.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
});

const applySearch = () => {
    router.get('/tv-managers', {
        property_id: selectedPropertyId.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const showDeleteDialog = ref(false);
const tvManagerToDelete = ref<TvManager | null>(null);

const openDeleteDialog = (tvManager: TvManager) => {
    tvManagerToDelete.value = tvManager;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (tvManagerToDelete.value) {
        router.delete(`/tv-managers/${tvManagerToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                tvManagerToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    tvManagerToDelete.value = null;
};

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getImageUrl = (imagePath: string | null) => {
    if (!imagePath) return '/placeholder-food.jpg';
    return `/storage/${imagePath}`;
};

// Watch for changes in filters prop and update local state
watch(() => props.filters.property_id, (newValue) => {
    selectedPropertyId.value = newValue ? newValue.toString() : '';
}, { immediate: true });
</script>

<template>
    <AppLayout :breadcrumbs="[
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'TV Managers', href: '/tv-managers' }
    ]">
        <div class="container mx-auto p-6 relative">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">TV Managers</h1>
                <p class="text-gray-600 mt-1">Manage guest data for your properties</p>
            </div>

            <!-- Property Selector -->
            <div class="flex items-center space-x-4 relative">
                <select v-model="selectedPropertyId" class="w-64 h-10 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="" disabled>Select property...</option>
                    <option v-for="property in properties" :key="property.id" :value="property.id.toString()">
                        {{ property.name }}
                    </option>
                </select>

                <Button @click="router.get('/tv-managers/create', { property_id: selectedPropertyId })" v-if="selectedPropertyId">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Guest
                </Button>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!selectedPropertyId" class="text-center py-12">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <Search class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Select Property</h3>
                <p class="text-gray-600 mb-6">Select a property from the dropdown above to view and manage guest data.</p>
            </div>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Search -->
            <div class="mb-6">
                <div class="flex items-center gap-2 w-full">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search by area name or guest name..."
                            class="pl-10"
                            @keyup.enter="applySearch"
                        />
                    </div>
                    <Button @click="applySearch" class="px-4">
                        <Search class="h-4" />
                    </Button>
                </div>
            </div>

            <!-- TV Managers Grid -->
            <div v-if="tvManagers.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Card v-for="tvManager in tvManagers.data" :key="tvManager.id" class="group overflow-hidden hover:shadow-lg transition-all duration-300 border-0 shadow-sm">
                    <!-- Image Container with Overlay -->
                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                        <img
                            :src="getImageUrl(tvManager.image)"
                            :alt="tvManager.guest_name"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />

                        <!-- Action Buttons Overlay -->
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-1">
                            <Button
                                size="sm"
                                class="h-8 w-8 p-0 bg-white/90 hover:bg-white text-gray-700 hover:text-blue-600 shadow-sm"
                                @click="router.get(`/tv-managers/${tvManager.id}/edit`)"
                            >
                                <Edit class="w-4 h-4" />
                            </Button>
                            <Button
                                size="sm"
                                class="h-8 w-8 p-0 bg-white/90 hover:bg-white text-gray-700 hover:text-red-600 shadow-sm"
                                @click="openDeleteDialog(tvManager)"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>

                    <!-- Content -->
                    <CardHeader class="pb-3 pt-4">
                        <div class="space-y-2">
                            <CardTitle class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ tvManager.guest_name }}
                            </CardTitle>

                            <p class="text-sm text-gray-600">
                                <span class="font-medium">Area:</span> {{ tvManager.area_name }}
                            </p>
                            <p v-if="tvManager.birth_date" class="text-sm text-gray-600">
                                <span class="font-medium">Birth Date:</span> {{ formatDate(tvManager.birth_date) }}
                            </p>
                        </div>
                    </CardHeader>
                </Card>
            </div>

            <!-- Empty TV Managers -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <Search class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No guest data</h3>
                    <p class="text-gray-600 mb-6">
                        {{ searchQuery
                            ? 'No guests match your search.'
                            : 'No guest data for this property yet.' }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="tvManagers.last_page > 1" class="flex justify-center mt-8">
                <div class="flex space-x-2">
                    <Button
                        v-for="page in tvManagers.last_page"
                        :key="page"
                        :variant="page === tvManagers.current_page ? 'default' : 'outline'"
                        size="sm"
                        @click="router.get('/tv-managers', {
                            property_id: selectedPropertyId,
                            search: searchQuery,
                            page: page
                        }, { preserveState: true, preserveScroll: true, replace: true })"
                    >
                        {{ page }}
                    </Button>
                </div>
            </div>
        </div>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Dialog v-model:open="showDeleteDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                            <Trash2 class="w-5 h-5 text-red-600" />
                        </div>
                        <span>Delete Guest Data</span>
                    </DialogTitle>
                    <DialogDescription class="text-left">
                        Are you sure you want to delete guest data for
                        <span class="font-semibold text-gray-900">"{{ tvManagerToDelete?.guest_name }}"</span>?
                        <br><br>
                        <span class="text-sm text-gray-500">This action cannot be undone and the data will be permanently deleted.</span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex space-x-2">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete Data
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
