<template>
    <div
        v-if="visible"
        :class="['p-4 mb-4 rounded-lg font-medium', typeClass]"
    >
        {{ message }}
    </div>
</template>

<script setup>
import { computed } from 'vue'

// ✅ capture props into a variable
const props = defineProps({
    message: { type: String, required: true },
    type: { type: String, default: 'success' },
    visible: { type: Boolean, default: true },
})

// ✅ now use props.type
const typeClass = computed(() => {
    switch (props.type) {
        case 'error': return 'bg-red-100 text-red-700'
        case 'warning': return 'bg-yellow-100 text-yellow-700'
        case 'info': return 'bg-blue-100 text-blue-700'
        default: return 'bg-green-100 text-green-700'
    }
})
</script>
