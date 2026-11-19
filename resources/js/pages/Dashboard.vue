<script setup lang="ts">
import { onBeforeMount, watch, ref, computed } from 'vue';
import Layout from './Layout.vue';
import { useHttp, router, usePage } from '@inertiajs/vue3';
import { useForm } from 'laravel-precognition-vue';
import Error from '@/shared/Error.vue';

const http = useHttp<RequestForm>();
const totalPrice = ref(0);
const page = usePage();

type RequestForm = {
    prompt: string;
    size: string;
    quality: string;
    reference_images: string[];
};

const props = defineProps({
    auth: Object,
    pixelforge: Object,
})

// Compute combined errors from form validation and flash messages
const errorMessage = computed(() => {
    return page.props.flash?.error as string | null;
});

const form = useForm<RequestForm>('post', '/dashboard/generate', {
    prompt: '',
    size: '',
    quality: '',
    reference_images: ['', '', '', ''],
})

onBeforeMount(() => {
    form.size = props.pixelforge?.default_size ?? '';
    form.quality = props.pixelforge?.default_quality ?? '';
});
watch(form, (formData) => {
    if (form.prompt.trim()) {
        http.transform((data) => {
            return {
                ...data,
                size: formData.size,
                quality: formData.quality,
                reference_images: formData.reference_images,
                prompt: formData.prompt,
            };
        }).post('/dashboard/price', {
            onSuccess: (response) => {
                totalPrice.value = response.price ?? 0;
            },
        });
    }
});
const submit = () => form.submit({
    onSuccess: () => {
        form.reset();
        totalPrice.value = 0;
        router.visit('/dashboard/generations');
    },
});
</script>
<template>
    <Layout>
        <div v-if="props.pixelforge" class="flex flex-col items-start gap-2 w-full md:w-2/4 m-auto mt-2">
            <div  v-if="totalPrice"  class="flex flex-col justify-start items-start gap-1 border border-stone-800 rounded-lg py-5 px-10">
                <div class="text-stone-400 text-lg">Billed estimate</div>
                <span class="text-2xl text-white">~ {{ totalPrice }}</span>
            </div>
            <h2 class="text-2xl text-white">Generate Image</h2>
            <div v-if="errorMessage" class="flex items-center justify-start text-red-500 border border-red-600 px-4 py-2 rounded w-full">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <p>{{ errorMessage }}</p>
            </div>
            <Error :form="form" />
            <form @submit.prevent="submit" class="flex flex-col gap-2 w-full">
                <textarea placeholder="Enter your prompt" class="form-input" rows="5" v-model="form.prompt" @change="form.validate('prompt')"></textarea>
                <select class="form-input" v-model="form.size">
                    <option v-for="size in props.pixelforge?.size ?? []" :key="size" :value="size">
                        {{ size }}
                    </option>
                </select>
                <select class="form-input" v-model="form.quality">
                    <option v-for="quality in props.pixelforge?.quality ?? []" :key="quality" :value="quality">
                        {{ quality }}
                    </option>
                </select>
                <input type="text" placeholder="Image URL https://..." class="form-input" v-model="form.reference_images[0]" @change="form.validate('reference_images')" />
                <input type="text" placeholder="Image URL https://..." class="form-input" v-model="form.reference_images[1]" @change="form.validate('reference_images')" />
                <input type="text" placeholder="Image URL https://..." class="form-input" v-model="form.reference_images[2]" @change="form.validate('reference_images')" />
                <input type="text" placeholder="Image URL https://..." class="form-input" v-model="form.reference_images[3]" @change="form.validate('reference_images')" />
                <button type="submit" class="btn-primary w-fit" :disabled="form.processing">Generate</button>
            </form>
        </div>
    </Layout>
</template>
