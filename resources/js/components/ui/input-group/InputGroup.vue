<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  modelValue?: string | number;
  type?: string;
  placeholder?: string;
  disabled?: boolean;
  error?: string;
  leftAddon?: string;
  rightAddon?: string;
  min?: number;
  max?: number;
  step?: string | number;
  id?: string;
  name?: string;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  disabled: false,
});

const emit = defineEmits(['update:modelValue']);

const inputClasses = computed(() => {
  const baseClasses = 'flex-1 px-3 py-2 text-sm border focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors';
  const stateClasses = props.error
    ? 'border-red-500 focus:ring-red-500'
    : 'border-gray-300 focus:border-blue-500';
  const disabledClasses = props.disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white';

  return `${baseClasses} ${stateClasses} ${disabledClasses}`;
});

const addonClasses = computed(() => {
  const baseClasses = 'flex items-center px-3 py-2 text-sm font-medium border bg-gray-50 text-gray-700 min-w-[3rem] justify-center';
  const stateClasses = props.error ? 'border-red-500' : 'border-gray-300';

  return `${baseClasses} ${stateClasses}`;
});

function onChange(e: Event) {
  const value = (e.target as HTMLInputElement).value;
  emit('update:modelValue', value);
}
</script>

<template>
  <div class="w-full">
    <div class="flex">
      <!-- Left Addon -->
      <div v-if="leftAddon" :class="addonClasses" class="rounded-l-md border-r-0">
        {{ leftAddon }}
      </div>

      <!-- Input -->
      <input
        :id="id"
        :name="name"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :min="min"
        :max="max"
        :step="step"
        :class="[
          inputClasses,
          leftAddon ? 'rounded-l-none' : 'rounded-l-md',
          rightAddon ? 'rounded-r-none' : 'rounded-r-md'
        ]"
        @input="onChange"
      />

      <!-- Right Addon -->
      <div v-if="rightAddon" :class="addonClasses" class="rounded-r-md border-l-0">
        {{ rightAddon }}
      </div>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="text-xs text-red-500 mt-1">
      {{ error }}
    </p>
  </div>
</template>
