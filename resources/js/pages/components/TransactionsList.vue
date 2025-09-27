<template>
    <div>
        <h3>Transactions</h3>
        <ul>
            <li v-for="tx in transactions" :key="tx.id">
                <div>
                    <small>{{ new Date(tx.created_at).toLocaleString() }}</small>
                </div>
                <div>
                    <strong v-if="tx.sender_id === myId">Sent</strong>
                    <strong v-else>Received</strong>
                    : {{ tx.amount }} (fee: {{ tx.commission_fee }}) — from {{ tx.sender_id }} to {{ tx.receiver_id }}
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    transactions: Array
});

const page = usePage();
const user = page.props.auth.user;

const myId = user.id;
</script>
