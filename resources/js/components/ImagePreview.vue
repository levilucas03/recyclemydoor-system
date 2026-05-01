<script setup>
const props = defineProps({
    src: String,
    show: Boolean
})

const emit = defineEmits(['close'])
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 bg-black/80 flex items-center justify-center z-50"
        @click="emit('close')"
    >
        <img
            :src="src"
            class="max-w-[90%] max-h-[90%] rounded shadow-lg"
        />
    </div>
</template>