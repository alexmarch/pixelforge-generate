<script setup lang="ts">
import { useFormContext } from '@inertiajs/vue3';
const props = defineProps({
    form: Object,
});
const form = props.form ?? useFormContext();
</script>

<template>
    <div v-if="form" class="flex flex-col gap-2 w-full">
        <div v-if="form.errors" class="flex flex-col gap-2 mt-2">
            <!-- v-for over form.errors -->
            <div v-for="(error, key) in form.errors" :key="key"
                class="flex items-center justify-start text-red-500 border border-red-600 px-4 py-2 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p>{{ error }}</p>
            </div>
        </div>
    </div>
</template>