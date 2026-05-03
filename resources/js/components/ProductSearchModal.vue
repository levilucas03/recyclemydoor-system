<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const emit = defineEmits(['select', 'close'])

const search = ref('')
const results = ref([])
let timer = null

watch(search, (value) => {
    clearTimeout(timer)

    if (!value || value.length < 2) {
        results.value = []
        return
    }

    timer = setTimeout(async () => {
        const res = await axios.get(route('products.search'), {
            params: { q: value }
        })
        results.value = res.data
    }, 300)
})

function select(product) {
    emit('select', product)
    emit('close')
}
</script>

<template>
<div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">


    <div class="bg-white w-full max-w-2xl rounded shadow p-4">

        <div class="flex justify-between mb-3">
            <h2 class="font-semibold text-lg">Select Product</h2>
            <button @click="$emit('close')">✕</button>
        </div>

        <!-- Search -->
        <input
            v-model="search"
            placeholder="Search product..."
            class="border p-2 w-full rounded"
        />

        <!-- Results -->
        <div class="mt-3 max-h-96 overflow-y-auto">

            <div
                v-for="product in results"
                :key="product.id"
                @click="select(product)"
                class="flex items-center gap-3 p-2 border-b hover:bg-gray-100 cursor-pointer"
            >
                <!-- Image -->
                <img
                    :src="product.image || '/placeholder.png'"
                    class="w-12 h-12 object-cover rounded"
                />

                <!-- Info -->
                <div class="flex-1">
                    <div class="font-medium">{{ product.title }}</div>
                    <div class="text-sm text-gray-500">
                        {{ product.size }}
                    </div>
                </div>

                <!-- Price -->
                <div class="font-semibold">
                    £{{ product.price }}
                </div>
            </div>

        </div>
    </div>
</div>
</template>
