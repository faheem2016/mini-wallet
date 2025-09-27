<template>
    <div>
        <h3>Send Money</h3>
        <form @submit.prevent="submit">
            <div>
                <label class="label-ma">Receiver ID</label>
                <input class="input-ma" v-model.number="receiverId" type="number" />
                <div v-if="errors['receiver_id']?.length" style="color:red;margin-top:10px">{{ errors['receiver_id'][0] }}</div>
            </div>
            <div>
                <label class="label-ma">Amount</label>
                <input class="input-ma" v-model.number="amount" type="number" step="0.01" />
                <div v-if="errors['amount']?.length" style="color:red;margin-top:10px">{{ errors['amount'][0] }}</div>
            </div>
            <br>
            <button class="button-ma" :disabled="loading">{{ loading ? 'Sending...' : 'Send' }}</button>
            <div v-if="error" style="color:red;margin-top:10px">{{ error }}</div>
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
const errors = ref([]);

async function submit() {
    error.value = '';
    loading.value = true;
    try {
        await axios.post('/api/transactions', {
            receiver_id: receiverId.value,
            amount: amount.value
        });
        errors.value = [];
        emit('transfer-success');
    } catch (e) {
        if (e.response?.data?.errors) {
            console.log(e.response.data.errors)
            errors.value = e.response.data.errors;
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
