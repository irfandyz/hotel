<script setup lang="ts">
import { inject } from 'vue';
import { SelectValue as SelectValuePrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';

const selectRoot = inject('SelectRoot');

const props = withDefaults(defineProps<{
    class?: string;
    placeholder?: string;
}>(), {
    class: '',
    placeholder: '',
});
</script>

<template>
    <SelectValuePrimitive
        :class="cn('line-clamp-1', props.class)"
        :placeholder="props.placeholder"
    />
</template>
