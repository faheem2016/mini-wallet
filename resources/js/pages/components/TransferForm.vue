<template>
    <div>
        <h3>Send Money</h3>
        <form @submit.prevent="submit">
            <div>
                <label class="label-ma">Receiver ID</label>
                <input class="input-ma" v-model.number="receiverId" type="number" />
            </div>
            <div>
                <label class="label-ma">Amount</label>
                <input class="input-ma" v-model.number="amount" type="number" step="0.01" />
            </div>
            <br>
            <button class="button-ma" :disabled="loading">{{ loading ? 'Sending...' : 'Send' }}</button>
            <div v-if="error" style="color:red;margin-top:10px">{{ error }}</div>
            <div v-if="success" style="color:green;margin-top:10px">{{ success }}</div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
const props = defineProps({
    userId: Number
});
const emit = defineEmits(['transfer-success']);

const receiverId = ref('');
const amount = ref('');
const loading = ref(false);
const error = ref('');
const success = ref('');

async function submit() {
    error.value = '';
    success.value = '';
    loading.value = true;
    try {
        const resp = await axios.post('/api/transactions', {
            receiver_id: receiverId,
            amount: amount
        });
        success.value = 'Transfer succeeded';
        emit('transfer-success');
    } catch (e) {
        if (e.response?.data?.errors) {
            error.value = Object.values(e.response.data.errors).flat().join(', ');
        } else if (e.response?.data?.message) {
            error.value = e.response.data.message;
        } else {
            error.value = 'An error occurred';
        }
    } finally {
        loading.value = false;
    }
}
</script>
