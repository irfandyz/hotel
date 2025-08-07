<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog';
import Badge from '@/components/ui/badge/Badge.vue';
import { Plus, Search, Filter, Edit, Trash2, X } from 'lucide-vue-next';

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
    name: string;
    description: string | null;
    image: string | null;
    price: number;
    status: 'enabled' | 'disabled';
    categories: Category[];
}

interface Props {
    properties: Property[];
    selectedProperty: Property | null;
    menuItems: {
        data: MenuItem[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categories: Category[];
    filters: {
        property_id: string | null;
        categories: number[];
        search: string;
    };
}

const props = defineProps<Props>();

const selectedPropertyId = ref<string>('');
const selectOpen = ref(false);
const searchQuery = ref(props.filters.search || '');
const selectedCategories = ref<number[]>(props.filters.categories || []);

const showCategoryFilter = ref(false);
const categorySearchQuery = ref('');

// Computed properties
const filteredCategories = computed(() => {
    return props.categories.filter(category =>
        selectedCategories.value.includes(category.id)
    );
});

const filteredCategoriesForFilter = computed(() => {
    return props.categories.filter(category =>
        category.name.toLowerCase().includes(categorySearchQuery.value.toLowerCase())
    );
});


// Watch for changes in selectedPropertyId and navigate
watch(selectedPropertyId, (newValue) => {
    if (newValue) {
        nextTick(() => { selectOpen.value = false; }); // paksa close setelah DOM update
        router.get('/restaurants', {
            property_id: newValue,
            categories: selectedCategories.value,
            search: searchQuery.value,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }
});

const applySearch = () => {
    router.get('/restaurants', {
        property_id: selectedPropertyId.value,
        categories: selectedCategories.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const toggleCategory = (categoryId: number) => {
    const index = selectedCategories.value.indexOf(categoryId);
    if (index > -1) {
        selectedCategories.value.splice(index, 1);
    } else {
        selectedCategories.value.push(categoryId);
    }
};

const toggleCategoryAndApply = (categoryId: number) => {
    toggleCategory(categoryId);
    // Apply filter immediately
    router.get('/restaurants', {
        property_id: selectedPropertyId.value,
        categories: selectedCategories.value,
        search: searchQuery.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};



const clearFilters = () => {
    selectedCategories.value = [];
    searchQuery.value = '';
    router.get('/restaurants', {
        property_id: selectedPropertyId.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const showDeleteDialog = ref(false);
const menuItemToDelete = ref<MenuItem | null>(null);

const openDeleteDialog = (menuItem: MenuItem) => {
    menuItemToDelete.value = menuItem;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (menuItemToDelete.value) {
        router.delete(`/restaurants/${menuItemToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteDialog.value = false;
                menuItemToDelete.value = null;
            },
        });
    }
};

const cancelDelete = () => {
    showDeleteDialog.value = false;
    menuItemToDelete.value = null;
};



const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(price);
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
        { title: 'Restaurants', href: '/restaurants' }
    ]">
        <div class="container mx-auto p-6 relative">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Restaurants</h1>
                <p class="text-gray-600 mt-1">Manage restaurant menus for your properties</p>
            </div>

            <!-- Property Selector -->
            <div class="flex items-center space-x-4 relative">
                <select v-model="selectedPropertyId" class="w-64 h-10 px-3 py-2 border rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="" disabled>Select property...</option>
                    <option v-for="property in properties" :key="property.id" :value="property.id.toString()">
                        {{ property.name }}
                    </option>
                </select>

                <Button @click="router.get('/restaurants/create', { property_id: selectedPropertyId })" v-if="selectedPropertyId">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Menu
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
                <p class="text-gray-600 mb-6">Select a property from the dropdown above to view and manage its restaurant menus.</p>
            </div>
        </div>

        <!-- Content -->
        <div v-else>
            <!-- Filters -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
                <div class="flex-1 w-full sm:w-auto">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Search menu..."
                            class="pl-10"
                            @keyup.enter="applySearch"
                        />
                    </div>
                </div>

                <!-- Filter Categories Dropdown -->
                <div class="relative w-full sm:w-auto">
                                        <Button
                        variant="outline"
                        @click="showCategoryFilter = !showCategoryFilter"
                        class="flex items-center space-x-2 w-full sm:w-auto justify-between sm:justify-start"
                    >
                        <Filter class="w-4 h-4" />
                        <span>Categories</span>
                        <Badge v-if="selectedCategories.length > 0" variant="secondary" class="ml-1">
                            {{ selectedCategories.length }}
                        </Badge>
                        <svg
                            class="w-4 h-4 transition-transform"
                            :class="{ 'rotate-180': showCategoryFilter }"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </Button>

                                                            <!-- Dropdown Menu -->
                    <div
                        v-if="showCategoryFilter"
                        class="absolute top-full left-0 sm:left-auto sm:right-0 mt-1 w-full sm:w-80 lg:w-96 bg-white border border-gray-200 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto"
                        style="min-width: 280px; max-width: calc(100vw - 2rem);"
                    >
                        <div class="p-3 border-b border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-medium text-gray-900">Select Categories</h3>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="clearFilters"
                                    class="text-xs h-6 px-2"
                                >
                                    Clear
                                </Button>
                            </div>
                            <div class="relative">
                                <Search class="absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400 w-3 h-3" />
                                <Input
                                    v-model="categorySearchQuery"
                                    placeholder="Search categories..."
                                    class="pl-7 h-8 text-xs"
                                />
                            </div>
                        </div>

                        <div class="p-2 space-y-1">
                            <div
                                v-for="category in filteredCategoriesForFilter"
                                :key="category.id"
                                class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer"
                                @click="toggleCategoryAndApply(category.id)"
                            >
                                <input
                                    type="checkbox"
                                    :id="`filter-category-${category.id}`"
                                    :checked="selectedCategories.includes(category.id)"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                    @click.stop
                                />
                                <label :for="`filter-category-${category.id}`" class="text-sm cursor-pointer flex-1">
                                    {{ category.name }}
                                </label>
                            </div>
                        </div>

                                                <div class="p-3 border-t border-gray-100 bg-gray-50">
                            <div class="flex justify-center items-center">
                                <span class="text-xs text-gray-500">
                                    {{ selectedCategories.length }} categories selected
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Categories Display -->
            <div v-if="filteredCategories.length > 0" class="flex flex-wrap gap-2 mb-4">
                <Badge
                    v-for="category in filteredCategories"
                    :key="category.id"
                    variant="secondary"
                    class="flex items-center space-x-1"
                >
                    <span>{{ category.name }}</span>
                    <X
                        class="w-3 h-3 cursor-pointer"
                        @click="toggleCategory(category.id)"
                    />
                </Badge>
            </div>

            <!-- Menu Items Grid -->
            <div v-if="menuItems.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <Card v-for="menuItem in menuItems.data" :key="menuItem.id" class="group overflow-hidden hover:shadow-lg transition-all duration-300 border-0 shadow-sm">
                    <!-- Image Container with Overlay -->
                    <div class="relative aspect-square overflow-hidden bg-gray-100">
                        <img
                            :src="getImageUrl(menuItem.image)"
                            :alt="menuItem.name"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />

                        <!-- Status Badge Overlay -->
                        <div class="absolute top-3 left-3">
                                                            <Badge
                                    :variant="menuItem.status === 'enabled' ? 'default' : 'secondary'"
                                    class="text-xs font-medium shadow-sm"
                                >
                                    {{ menuItem.status === 'enabled' ? 'Active' : 'Inactive' }}
                                </Badge>
                        </div>

                        <!-- Action Buttons Overlay -->
                        <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex space-x-1">
                            <Button
                                size="sm"
                                class="h-8 w-8 p-0 bg-white/90 hover:bg-white text-gray-700 hover:text-blue-600 shadow-sm"
                                @click="router.get(`/restaurants/${menuItem.id}/edit`)"
                            >
                                <Edit class="w-4 h-4" />
                            </Button>
                                                            <Button
                                    size="sm"
                                    class="h-8 w-8 p-0 bg-white/90 hover:bg-white text-gray-700 hover:text-red-600 shadow-sm"
                                    @click="openDeleteDialog(menuItem)"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                        </div>

                        <!-- Price Tag Overlay -->
                        <div class="absolute bottom-3 left-3">
                            <div class="bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-sm">
                                <span class="text-sm font-bold text-green-600">
                                    {{ formatPrice(menuItem.price) }}
                                </span>
                            </div>
                        </div>
                    </div>
                                        <!-- Content -->
                    <CardHeader class="pb-3 pt-4">
                        <div class="space-y-2">
                            <!-- Categories -->
                            <div v-if="menuItem.categories.length > 0" class="flex flex-wrap gap-1.5">
                                <Badge
                                    v-for="category in menuItem.categories"
                                    :key="category.id"
                                    variant="outline"
                                    class="text-[10px] px-1.5 py-0.5 bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100"
                                >
                                    {{ category.name }}
                                </Badge>
                            </div>

                            <CardTitle class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                {{ menuItem.name }}
                            </CardTitle>

                            <p v-if="menuItem.description" class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                {{ menuItem.description }}
                            </p>
                        </div>
                    </CardHeader>
                </Card>
            </div>

            <!-- Empty Menu Items -->
            <div v-else class="text-center py-12">
                <div class="max-w-md mx-auto">
                    <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <Search class="w-12 h-12 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No menu items</h3>
                    <p class="text-gray-600 mb-6">
                        {{ searchQuery || selectedCategories.length > 0
                            ? 'No menu items match the selected filters.'
                            : 'No menu items for this property yet.' }}
                    </p>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="menuItems.last_page > 1" class="flex justify-center mt-8">
                <div class="flex space-x-2">
                    <Button
                        v-for="page in menuItems.last_page"
                        :key="page"
                        :variant="page === menuItems.current_page ? 'default' : 'outline'"
                        size="sm"
                        @click="router.get('/restaurants', {
                            property_id: selectedPropertyId,
                            categories: selectedCategories,
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
                        <span>Delete Menu</span>
                    </DialogTitle>
                    <DialogDescription class="text-left">
                        Are you sure you want to delete menu item
                        <span class="font-semibold text-gray-900">"{{ menuItemToDelete?.name }}"</span>?
                        <br><br>
                        <span class="text-sm text-gray-500">This action cannot be undone and the menu item will be permanently deleted.</span>
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="flex space-x-2">
                    <Button variant="outline" @click="cancelDelete">
                        Cancel
                    </Button>
                    <Button variant="destructive" @click="confirmDelete">
                        <Trash2 class="w-4 h-4 mr-2" />
                        Delete Menu
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
    line-clamp: 2; /* <-- add this line */
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
