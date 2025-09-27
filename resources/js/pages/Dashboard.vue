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

const page = usePage();
const user = page.props.auth.user;

const userId = user.id;
const token = user.api_token;

axios.defaults.baseURL = 'http://mini-wallet.test';
axios.defaults.headers.common.Authorization = `Bearer ${token}`;

const balance = ref('0.00');
const transactions = ref([]);

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
            }
        });
});

function onTransferSuccess() {
    fetchTransactions();
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div style="max-width:900px;margin:40px auto;font-family:Arial,Helvetica,sans-serif">
                <h1>Mini Wallet</h1>
                <div style="display:flex;gap:20px;">
                    <div style="flex: 1;">
                        <transfer-form :userId="userId" @transfer-success="onTransferSuccess" />
                    </div>
                    <div style="flex: 1;">
                        <div style="margin-bottom:10px">
                            <strong>Balance:</strong> ${{ balanceFormatted }}
                        </div>
                        <transactions-list :transactions="transactions" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
