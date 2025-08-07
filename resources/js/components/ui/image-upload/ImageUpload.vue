<script setup lang="ts">
import { ref, computed } from 'vue';
import { Upload, X, Image as ImageIcon } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Props {
    modelValue?: File[];
    multiple?: boolean;
    maxFiles?: number;
    accept?: string;
    maxSize?: number; // in MB
}

const props = withDefaults(defineProps<Props>(), {
    modelValue: () => [],
    multiple: true,
    maxFiles: 5,
    accept: 'image/*',
    maxSize: 5
});

const emit = defineEmits<{
    'update:modelValue': [files: File[]];
    'filesAdded': [files: File[]];
}>();

const dragActive = ref(false);
const fileInput = ref<HTMLInputElement>();

const files = computed({
    get: () => props.modelValue || [],
    set: (value) => emit('update:modelValue', value)
});

const handleFiles = (fileList: FileList) => {
    const newFiles = Array.from(fileList);
    const validFiles = newFiles.filter(file => {
        // Check file type
        if (!file.type.startsWith('image/')) {
            alert('Hanya file gambar yang diperbolehkan');
            return false;
        }

        // Check file size
        if (file.size > props.maxSize * 1024 * 1024) {
            alert(`Ukuran file terlalu besar. Maksimal ${props.maxSize}MB`);
            return false;
        }

        return true;
    });

    if (files.value.length + validFiles.length > props.maxFiles) {
        alert(`Maksimal ${props.maxFiles} file`);
        return;
    }

    // Validate image dimensions
    const validateImageDimensions = async (file: File): Promise<boolean> => {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () => {
                if (img.width < 800 || img.height < 600) {
                    alert(`Gambar ${file.name} terlalu kecil. Minimal 800x600 pixel`);
                    resolve(false);
                } else {
                    resolve(true);
                }
            };
            img.onerror = () => {
                alert(`Gagal memuat gambar ${file.name}`);
                resolve(false);
            };
            img.src = URL.createObjectURL(file);
        });
    };

    // Process files with dimension validation
    const processFiles = async () => {
        const validatedFiles = [];
        for (const file of validFiles) {
            const isValid = await validateImageDimensions(file);
            if (isValid) {
                validatedFiles.push(file);
            }
        }

        if (validatedFiles.length > 0) {
            // Check for duplicates before adding
            const existingFileNames = files.value.map(f => f.name);
            const uniqueFiles = validatedFiles.filter(file => !existingFileNames.includes(file.name));

            if (uniqueFiles.length > 0) {
                const newFileList = [...files.value, ...uniqueFiles];
                files.value = newFileList;
                emit('filesAdded', uniqueFiles);
            }
        }
    };

    processFiles();
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    dragActive.value = false;

    if (e.dataTransfer?.files) {
        handleFiles(e.dataTransfer.files);
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
    if (target.files) {
        handleFiles(target.files);
    }
};

const removeFile = (index: number) => {
    const newFiles = [...files.value];
    newFiles.splice(index, 1);
    files.value = newFiles;
};

const openFileDialog = () => {
    fileInput.value?.click();
};

const getFilePreview = (file: File): string => {
    return URL.createObjectURL(file);
};
</script>

<template>
    <div class="space-y-4">
        <!-- Upload Area -->
        <div
            class="relative border-2 border-dashed rounded-lg p-6 transition-colors"
            :class="[
                dragActive
                    ? 'border-primary bg-primary/5'
                    : 'border-gray-300 hover:border-gray-400'
            ]"
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
        >
            <input
                ref="fileInput"
                type="file"
                :accept="accept"
                :multiple="multiple"
                class="hidden"
                @change="handleFileInput"
            />

            <div class="text-center">
                <Upload class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                <div class="space-y-2">
                    <p class="text-lg font-medium text-gray-900">
                        Upload Gambar Hotel
                    </p>
                    <p class="text-sm text-gray-500">
                        Drag & drop gambar atau
                        <button
                            type="button"
                            class="text-primary hover:text-primary/80 underline font-medium"
                            @click="openFileDialog"
                        >
                            pilih file
                        </button>
                    </p>
                    <p class="text-xs text-gray-400">
                        Maksimal {{ maxFiles }} file, ukuran maksimal {{ maxSize }}MB per file
                    </p>
                    <div class="flex justify-center gap-2 text-xs text-gray-400">
                        <span>Format: JPG, PNG, GIF</span>
                        <span>•</span>
                        <span>Resolusi: Min. 800x600</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Area -->
        <div v-if="files.length > 0" class="space-y-4">
            <h4 class="text-sm font-medium text-gray-900">
                Gambar yang Dipilih ({{ files.length }}/{{ maxFiles }})
            </h4>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div
                    v-for="(file, index) in files"
                    :key="index"
                    class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200"
                >
                    <img
                        :src="getFilePreview(file)"
                        :alt="file.name"
                        class="w-full h-full object-cover"
                    />

                    <!-- Remove Button -->
                    <button
                        type="button"
                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                        @click="removeFile(index)"
                    >
                        <X class="w-4 h-4" />
                    </button>

                    <!-- File Info -->
                    <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-2">
                        <p class="text-xs truncate">{{ file.name }}</p>
                        <p class="text-xs opacity-75">
                            {{ (file.size / 1024 / 1024).toFixed(1) }}MB
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
