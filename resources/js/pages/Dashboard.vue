<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Echo from 'laravel-echo';
import TransferForm from './components/TransferForm.vue';
import TransactionsList from './components/TransactionsList.vue';
import Alert from './components/Alert.vue';
import { LoaderCircle } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth.user;

const userId = user.id;
const token = user.api_token;

axios.defaults.baseURL = 'http://mini-wallet.test';
axios.defaults.headers.common.Authorization = `Bearer ${token}`;

const balance = ref('0.00');
const transactions = ref([]);

const showAlert = ref(false)
const alertMessage = ref('')
const loading = ref(false)

function fetchTransactions() {
    axios.get('/api/transactions')
        .then(res => {
            balance.value = res.data.balance;
            transactions.value = res.data.transactions.data || [];
        })
        .catch(err => console.error(err));
}

const balanceFormatted = computed(() => {
    return parseFloat(balance.value)
        .toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
});

onMounted(() => {
    fetchTransactions();

    const echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
        forceTLS: true,
    });

    // Listen to private channel for this user
    echo.private(`user.${userId}`)
        .listen('.transaction.created', (e) => {
            const tx = e.transaction;
            // Update UI reactively
            if (tx.sender_id === userId || tx.receiver_id === userId) {
                // Prefer fetching server-side balance to avoid rounding issues
                fetchTransactions();
                loading.value = false
            }
        });
});

function onTransferSuccess() {
    alertMessage.value = '✅ Transfer completed successfully!'
    showAlert.value = true

    loading.value = true

    // auto-hide after 3s
    setTimeout(() => showAlert.value = false, 3000)
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="container-ma">
                <h1 style="text-align:center; margin-bottom: 30px;">💳 Mini Wallet</h1>

                <Alert v-if="showAlert" :message="alertMessage" type="success" />

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                    <transfer-form :userId="userId" @transfer-success="onTransferSuccess" />

                    <div>
                        <div class="card-ma" style="text-align:center;">
                            <h2 class="flex items-center justify-center gap-2">
                                Current Balance
                                <LoaderCircle
                                    v-if="loading"
                                    class="h-4 w-4 animate-spin text-gray-500"
                                />
                            </h2>

                            <p class="text-3xl font-bold text-blue-600">
                                {{ balanceFormatted }}
                            </p>
                        </div>

                        <transactions-list :transactions="transactions" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>

h1, h2, h3 {
    font-weight: bold;
    margin: 0 0 24px 0;
}

.container-ma {
    max-width: 1100px;
    margin: 40px auto;
    padding: 20px;
}

.card-ma {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.button-ma {
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 18px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.button-ma:hover {
    background: #1d4ed8;
}

.input-ma {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    margin-top: 4px;
    font-size: 14px;
}

.label-ma {
    font-weight: 600;
    font-size: 14px;
}

.error-ma {
    color: #dc2626;
    margin-top: 8px;
}

.success-ma {
    color: #16a34a;
    margin-top: 8px;
}

</style>
