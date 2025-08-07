<script setup lang="ts">
import { ref, computed } from 'vue';
import { Upload, X, Image as ImageIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Props {
    modelValue?: File | null;
    accept?: string;
    maxSize?: number; // in MB
    placeholder?: string;
    existingImage?: string | null; // URL gambar yang sudah ada
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: null,
    accept: 'image/*',
    maxSize: 5,
    placeholder: 'Upload Gambar',
    existingImage: null
});

const emit = defineEmits<{
    'update:modelValue': [file: File | null];
}>();

const dragActive = ref(false);
const fileInput = ref<HTMLInputElement>();

const selectedFile = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const hasImage = computed(() => selectedFile.value || props.existingImage);

const handleFile = (file: File) => {
    // Check file type
    if (!file.type.startsWith('image/')) {
        alert('Hanya file gambar yang diperbolehkan');
        return;
    }

    // Check file size
    if (file.size > props.maxSize * 1024 * 1024) {
        alert(`Ukuran file terlalu besar. Maksimal ${props.maxSize}MB`);
        return;
    }

    // Validate image dimensions
    const img = new Image();
    img.onload = () => {
        if (img.width < 800 || img.height < 600) {
            alert(`Gambar terlalu kecil. Minimal 800x600 pixel`);
            return;
        }
        selectedFile.value = file;
        // Reset file input so the same file can be selected again
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    };
    img.onerror = () => {
        alert(`Gagal memuat gambar`);
    };
    img.src = URL.createObjectURL(file);
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    dragActive.value = false;

    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        handleFile(e.dataTransfer.files[0]);
    }
};

const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    dragActive.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    dragActive.value = false;
};

const handleFileInput = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        handleFile(target.files[0]);
    }
};

const removeFile = () => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const openFileDialog = () => {
    fileInput.value?.click();
};

const getFilePreview = (file: File): string => {
    return URL.createObjectURL(file);
};

const getImageUrl = (): string => {
    if (selectedFile.value) {
        return getFilePreview(selectedFile.value);
    }
    return props.existingImage || '';
};
</script>

<template>
    <div class="space-y-4">
        <!-- Hidden File Input (Always Available) -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            multiple="false"
            class="hidden"
            @change="handleFileInput"
        />

        <!-- Upload Area -->
        <div v-if="!hasImage"
            class="relative border-2 border-dashed rounded-lg p-6 transition-colors"
            :class="[
                dragActive
                    ? 'border-blue-500 bg-blue-50'
                    : 'border-gray-300 hover:border-gray-400'
            ]"
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
        >

            <div class="text-center">
                <Upload class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <div class="space-y-2">
                    <p class="text-lg font-medium text-gray-900">
                        {{ placeholder }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Drag & drop gambar atau
                        <button
                            type="button"
                            class="text-blue-600 hover:text-blue-800 underline font-medium"
                            @click="openFileDialog"
                        >
                            pilih file
                        </button>
                    </p>
                    <p class="text-xs text-gray-400">
                        Ukuran maksimal {{ maxSize }}MB, minimal 800x600 pixel
                    </p>
                    <div class="flex justify-center gap-2 text-xs text-gray-400">
                        <span>Format: JPG, PNG, GIF</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Area -->
        <div v-if="hasImage" class="space-y-4">
            <div class="relative group">
                <img
                    :src="getImageUrl()"
                    :alt="selectedFile?.name || 'Menu Image'"
                    class="w-full max-w-md h-64 object-cover rounded-lg border border-gray-200"
                />

                <!-- Remove Button -->
                <button
                    v-if="selectedFile"
                    type="button"
                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                    @click="removeFile"
                >
                    <X class="w-4 h-4" />
                </button>

                <!-- File Info -->
                <div v-if="selectedFile" class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-3 rounded-b-lg">
                    <p class="text-sm truncate font-medium">{{ selectedFile.name }}</p>
                    <p class="text-xs opacity-75">
                        {{ (selectedFile.size / 1024 / 1024).toFixed(1) }}MB
                    </p>
                </div>

                <!-- Existing Image Badge -->
                <div v-if="!selectedFile && props.existingImage" class="absolute top-2 left-2 bg-blue-500 text-white px-2 py-1 rounded text-xs font-medium">
                    Gambar Saat Ini
                </div>
            </div>

            <!-- Replace Button -->
            <div class="flex justify-center">
                <Button
                    type="button"
                    variant="outline"
                    @click="openFileDialog"
                >
                    <Upload class="w-4 h-4 mr-2" />
                    Ganti Gambar
                </Button>
            </div>
        </div>
    </div>
</template>
