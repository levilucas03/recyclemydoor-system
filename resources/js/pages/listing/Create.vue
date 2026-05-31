<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    products: Array,
    platforms: Array,
    selectedProductId: Number,
})

const form = useForm({
    title: '',
    notes: '',
    platform_id: [],
    product_id: props.selectedProductId ?? null,
})

const submit = () => {
    form.post(route('listings.store'))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Create Listing</h2>
        </template>

        <form @submit.prevent="submit" class="max-w-4xl mx-auto mt-10 bg-white shadow rounded-xl p-6">

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Listing Title</label>
                <input v-model="form.title" class="w-full border rounded p-2" />
                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                    {{ form.errors.title }}
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea v-model="form.notes" class="w-full border rounded p-2" rows="4"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Product</label>
                <select v-model="form.product_id" class="w-full border rounded p-2">
                    <option :value="null">Select product</option>
                    <option
                        v-for="product in products"
                        :key="product.id"
                        :value="product.id"
                    >
                        {{ product.sku }} - {{ product.title }}
                    </option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-3">Platforms</label>

                <div v-if="platforms.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label
                        v-for="platform in platforms"
                        :key="platform.id"
                        class="border rounded-lg p-4 flex items-center gap-3 cursor-pointer hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            :value="platform.id"
                            v-model="form.platform_ids"
                        />

                        <div>
                            <div class="font-medium">{{ platform.name }}</div>
                            <div class="text-xs text-gray-500">{{ platform.slug }}</div>
                        </div>
                    </label>
                </div>

                <p v-else class="text-sm text-gray-500">
                    No active listing platforms. Configure WordPress first.
                </p>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">
                Create Listing
            </button>

        </form>
    </AuthenticatedLayout>
</template>