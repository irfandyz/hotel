<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { ChevronDown } from 'lucide-vue-next';
import type { BreadcrumbItemType } from '@/types';
import { ref, onMounted } from 'vue';

interface Property {
    id: number;
    name: string;
    hotel_category?: string;
}

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const properties = ref<Property[]>([]);
const selectedProperty = ref<Property | null>(null);
const isLoading = ref(true);

const fetchProperties = async () => {
    try {
        const response = await fetch('/api/user-properties');
        const data = await response.json();
        properties.value = data;
        if (data.length > 0) {
            selectedProperty.value = data[0];
        }
    } catch (error) {
        console.error('Error fetching properties:', error);
    } finally {
        isLoading.value = false;
    }
};

const selectProperty = (property: Property) => {
    selectedProperty.value = property;
    // Emit event untuk memberitahu parent component tentang perubahan properti
    // emit('property-changed', property);
};

onMounted(() => {
    fetchProperties();
});
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <!-- Bagian kiri: Breadcrumb -->
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <!-- Bagian kanan: Property Selector -->
        <div class="flex items-center gap-2">
            <DropdownMenu v-if="!isLoading">
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" class="min-w-[200px] justify-between">
                        <span v-if="selectedProperty">
                            {{ selectedProperty.name }}
                        </span>
                        <span v-else class="text-muted-foreground">
                            Pilih Properti
                        </span>
                        <ChevronDown class="h-4 w-4 opacity-50" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[200px]">
                    <DropdownMenuItem
                        v-for="property in properties"
                        :key="property.id"
                        @click="selectProperty(property)"
                        :class="{ 'bg-accent': selectedProperty?.id === property.id }"
                    >
                        <div class="flex flex-col">
                            <span class="font-medium">{{ property.name }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ property.hotel_category ? property.hotel_category.charAt(0).toUpperCase() + property.hotel_category.slice(1) : 'Hotel' }}
                            </span>
                        </div>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <!-- Loading state -->
            <div v-if="isLoading" class="flex items-center gap-2">
                <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                <span class="text-sm text-muted-foreground">Memuat properti...</span>
            </div>
        </div>
    </header>
</template>
