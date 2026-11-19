<script setup lang="ts">
import { Link, useHttp, usePage } from '@inertiajs/vue3';
import { logout } from '@/routes/user';
import { ref, onMounted, computed, watch } from 'vue';

declare global {
    interface Window {
        Echo: any;
    }
}

const props = defineProps({
    auth: Object
})
const page = usePage();
const walletBalance = ref<any>(null);
const http = useHttp();
const buyDemoCredit = () => {
    try {
        http.post('/dashboard/buy-demo-credit')
    } catch (error) {
        console.error('Failed to buy demo credit:', error);
    }
};
const wallet = computed(() => {
    if (walletBalance.value) {
        return walletBalance.value;
    }
    if (props.auth?.user?.wallet) {
        return props.auth.user.wallet;
    }
    return (page.props.auth as any)?.user?.wallet;
});

watch(
    () => props.auth?.user?.wallet,
    (newWallet) => {
        if (newWallet && !walletBalance.value) {
            walletBalance.value = newWallet;
        }
    },
    { immediate: true }
);

onMounted(() => {
    if (props.auth?.user?.id && window.Echo) {
        try {
            window.Echo.private(`user.${props.auth.user.id}`)
                .listen('BalanceUpdate', (data: any) => {
                    walletBalance.value = {
                        balance: data.balance,
                        formatted_balance: data.formatted_balance,
                    };
                });
        } catch (error) {
            console.error('Failed to listen to BalanceUpdate:', error);
        }
    }
});
</script>
<template>
    <nav
        class="sticky top-0 w-full md:w-[calc(100%-16rem)] md:ml-64 p-4 text-white bg-stone-950 py-5 border-b border-stone-900 flex items-end justify-end">
        <div v-if="props.auth?.user" class="flex items-center gap-4">
            <span v-if="wallet?.balance !== undefined && wallet?.balance !== null" class="badge-pill-primary text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                {{ wallet.formatted_balance || `$${wallet.balance}` }}
            </span>
            <button @click="buyDemoCredit" class="btn-pill">
                Buy
            </button>
            <div class="flex items-center gap-2">
                <div class="text-white inline-flex items-center gap-2">
                    <span class="hidden md:flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-7 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        {{ props.auth.user.name }}
                    </span>
                </div>
                <Link :href="logout()" class="btn-pill">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25" />
                    </svg>
                </Link>
            </div>
        </div>
    </nav>
</template>