<script setup lang="ts">
import { ref } from 'vue';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    items: NavItem[];
}>();

const page = usePage();

// State untuk track menu yang terbuka
const openMenus = ref<Record<string, boolean>>({});

function toggleMenu(title: string) {
    openMenus.value[title] = !openMenus.value[title];
}
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Platform</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in props.items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="item.href === page.url"
                    :tooltip="item.title"
                    @click="item.children ? toggleMenu(item.title) : null"
                >
                    <Link :href="item.href" v-if="!item.children">
                        <component v-if="item.icon" :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                    <div v-else style="display: flex; align-items: center; cursor: pointer;">
                        <component v-if="item.icon" :is="item.icon" />
                        <span>{{ item.title }}</span>
                        <span style="margin-left: auto;">{{ openMenus[item.title] ? '▲' : '▼' }}</span>
                    </div>
                </SidebarMenuButton>
                <!-- Dropdown submenu -->
                <SidebarMenu
                    v-if="item.children && openMenus[item.title]"
                    class="ml-4"
                >
                    <SidebarMenuItem v-for="child in item.children" :key="child.title">
                        <SidebarMenuButton as-child :is-active="child.href === page.url" :tooltip="child.title">
                            <Link :href="child.href">
                                <span>{{ child.title }}</span>
                            </Link>
                        </SidebarMenuButton>
                        <!-- Rekursif jika child juga punya children -->
                        <SidebarMenu v-if="child.children && child.children.length" class="ml-4">
                            <NavMain :items="child.children" />
                        </SidebarMenu>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
