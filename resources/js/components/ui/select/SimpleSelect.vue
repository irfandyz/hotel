<script setup lang="ts">
import { computed } from 'vue';

interface Option {
  value: string | number;
  label: string;
}

defineProps<{
  modelValue: string | number | null;
  options: Option[];
  placeholder?: string;
  disabled?: boolean;
  error?: string;
  id?: string;
  name?: string;
}>();

const emit = defineEmits(['update:modelValue']);

function onChange(e: Event) {
  const value = (e.target as HTMLSelectElement).value;
  emit('update:modelValue', value);
}
</script>

<template>
  <div>
    <select
      :id="id"
      :name="name"
      class="w-full h-10 px-3 py-2 border rounded-md text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition disabled:bg-gray-100 disabled:cursor-not-allowed shadow-sm appearance-none pr-8"
      :class="error ? 'border-red-500 focus:ring-red-500' : 'border-gray-300'"
      :value="modelValue ?? ''"
      @change="onChange"
      :disabled="disabled"
    >
      <option v-if="placeholder" value="" disabled :selected="modelValue === null || modelValue === ''">{{ placeholder }}</option>
      <option v-for="opt in options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
    <svg class="pointer-events-none absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" style="margin-top:-1.5rem;">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
    </svg>
    <p v-if="error" class="text-xs text-red-500 mt-1">{{ error }}</p>
  </div>
</template>

<style scoped>
div { position: relative; }
select { padding-right: 2.5rem; }
</style>
