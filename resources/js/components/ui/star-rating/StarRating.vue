<script setup lang="ts">
import { Star, StarOff } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    modelValue?: number;
    max?: number;
    size?: 'sm' | 'md' | 'lg';
    readonly?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: 0,
    max: 5,
    size: 'md',
    readonly: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: number];
}>();

const hoverRating = ref(0);

const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-6 h-6',
    lg: 'w-8 h-8'
};

const stars = computed(() => {
    return Array.from({ length: props.max }, (_, index) => index + 1);
});

const handleStarClick = (starValue: number) => {
    if (!props.readonly) {
        emit('update:modelValue', starValue);
    }
};

const handleStarHover = (starValue: number) => {
    if (!props.readonly) {
        hoverRating.value = starValue;
    }
};

const handleMouseLeave = () => {
    if (!props.readonly) {
        hoverRating.value = 0;
    }
};
</script>

<template>
    <div class="flex items-center gap-1" @mouseleave="handleMouseLeave">
        <button
            v-for="star in stars"
            :key="star"
            type="button"
            :class="[
                'transition-all duration-200 ease-in-out',
                'hover:scale-110 active:scale-95',
                'focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2',
                'rounded-sm p-1',
                sizeClasses[size]
            ]"
            :disabled="readonly"
            @click="handleStarClick(star)"
            @mouseenter="handleStarHover(star)"
        >
            <Star
                v-if="star <= (hoverRating || modelValue)"
                :class="[
                    hoverRating && star <= hoverRating ? 'text-yellow-300 fill-current drop-shadow-sm' : 'text-yellow-400 fill-current drop-shadow-sm',
                    sizeClasses[size]
                ]"
            />
            <StarOff
                v-else
                :class="[
                    'text-gray-300 hover:text-yellow-300 transition-colors',
                    sizeClasses[size]
                ]"
            />
        </button>

        <span v-if="!readonly" class="ml-2 text-sm text-muted-foreground">
            {{ hoverRating || modelValue }}/{{ max }}
        </span>
    </div>
</template>
