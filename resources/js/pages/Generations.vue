<script setup lang="ts">
import Layout from './Layout.vue';
import { ref, onMounted, nextTick, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

declare global {
    interface Window {
        Echo: any;
    }
}

const page = usePage();
const props = defineProps({
    generations: Array,
});

const generations = ref<any[]>(props.generations || []);

const formatDateToClientTimezone = (isoString: string | null): string => {
    if (!isoString) return '';
    try {
        const date = new Date(isoString);
        return date.toLocaleString(undefined, {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    } catch {
        return isoString;
    }
};

const setupEchoListeners = () => {
    if (!window.Echo) {
        setTimeout(setupEchoListeners, 1000);
        return;
    }

    const userId = (page.props.auth as any)?.user?.id;
    
    if (!userId) {
        console.warn('No user ID found');
        return;
    }

    try {
        const userChannel = window.Echo.private(`user.${userId}`);
        const generationChannel = window.Echo.private(`user.${userId}.generations`);

        console.log('Listening to channels:', `user.${userId}`, `user.${userId}.generations`);

        generationChannel.listen('GenerationStatusUpdate', (data: any) => {
            const index = generations.value.findIndex((g: any) => g.id === data.id);
            if (index !== -1) {
                generations.value[index] = { ...data };
            } else {
                generations.value.unshift({ ...data });
            }
        });
        
        // userChannel.listen('GenerationStatusUpdate', (data: any) => {
        //     const index = generations.value.findIndex((g: any) => g.id === data.id);
        //     if (index !== -1) {
        //         generations.value[index] = { ...data };
        //     } else {
        //         generations.value.unshift({ ...data });
        //     }
        // });

        userChannel.listen('BalanceUpdate', (data: any) => {
            console.log('Balance update received:', data);
        });

    } catch (error) {
        setTimeout(setupEchoListeners, 2000);
    }
};

onMounted(async () => {
    await nextTick();
    setupEchoListeners();
});

// Fallback: try to setup listeners if generations change
watch(
    () => props.generations,
    () => {
        if (!window.Echo) {
            setupEchoListeners();
        }
    }
);
</script>
<template>
    <Layout>
        <div v-if="generations && generations.length">
            <div class="grid grid-cols-4 gap-4">
                <div v-for="generation in generations" :key="generation.id" class="flex flex-col border border-stone-800 rounded-2xl p-4 gap-2 hover:border-stone-700">
                    <a v-if="generation.image_url" :href="generation.image_url" target="_blank">
                        <img :src="generation.image_url" alt="Generation image" class="mb-2">
                    </a>
                    <p v-else class="text-xs">
                        <img src="/placeholder.svg" alt="No image available" class="mb-2">
                    </p>
                    <p class="font-semibold text-xs">{{ generation.prompt }}</p>
                    <p class="flex items-center gap-2 text-xs w-fit py-0.5 p-2 rounded-full pt-1" :class="{
                        'text-green-500 bg-green-950': generation.status === 'completed' || generation.status === 'succeeded',
                        'text-yellow-500 bg-yellow-950': generation.status === 'queued',
                        'text-red-500 bg-red-950': generation.status === 'failed',
                        'text-gray-500 bg-gray-950': generation.status === 'pending'
                    }">
                        {{ generation.status }}
                        <span v-if="generation.status === 'queued'" class="inline-block animate-spin">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </p>
                    <p class="text-xs">Price: {{ generation.formatted_price }}</p>
                    <p class="text-xs">Size: {{ generation.size }}</p>
                    <p class="text-xs">Quality: {{ generation.quality }}</p>
                    <p class="text-xs">Expires at: {{ formatDateToClientTimezone(generation.image_url_expires_at) }}</p>
                </div>
            </div>
        </div>
        <div v-else>
            No generations found.
        </div>
    </Layout>
</template>