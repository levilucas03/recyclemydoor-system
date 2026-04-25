<template>
    <div class="max-w-xl mx-auto p-6 bg-white rounded shadow">
        <h1 class="text-xl font-bold mb-4">Create Listing</h1>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <label class="block mb-1">Title</label>
                <input v-model="form.title" type="text" class="w-full border rounded p-2" />
                <div v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</div>
            </div>

            <div class="mb-4">
                <label class="block mb-1">Notes</label>
                <textarea v-model="form.notes" class="w-full border rounded p-2"></textarea>
                <div v-if="form.errors.notes" class="text-red-500 text-sm">{{ form.errors.notes }}</div>
            </div>

            <div class="mb-4">
                <label class="block mb-1">Assign a Product</label>
                <select v-model="form.product_id" class="w-full border rounded p-2">
                    <option value="">-- Select Product --</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.sku }} - {{ product.title }} ({{ product.width }} x {{ product.height }})
                    </option>
                </select>
                <div v-if="form.errors.product_id" class="text-red-500 text-sm">{{ form.errors.product_id }}</div>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                Create Listing
            </button>
        </form>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

defineProps({ products: Array })

const form = useForm({
    title: '',
    notes: '',
    product_id: '',
})

function submit() {
    form.post(route('listings.store'))
}
</script>
