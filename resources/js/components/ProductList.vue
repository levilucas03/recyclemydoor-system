<template>
    <div>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Products</h1>

            <!-- Bulk Action Button -->
            <div v-if="selected.length > 0" class="relative">
                <button @click="bulkDelete" class="bg-red-600 text-white px-3 py-1 rounded">
                    Delete Selected ({{ selected.length }})
                </button>
            </div>
        </div>

        <table class="w-full border text-sm">
            <thead>
            <tr class="bg-gray-100">
                <th class="p-2 text-center"><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
                <th></th>
                <th class="p-2 text-left">Title</th>
                <th class="p-2 text-left">Size</th>
                <th class="p-2 text-left">Category</th>
                <th class="p-2 text-left"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in products.data" :key="product.id" class="border-t">
                <td class="p-2 text-center">
                    <input
                        type="checkbox"
                        :value="product.id"
                        v-model="selected"
                    />
                </td>
                <!-- IMAGE -->
                <td class="p-2">
                    <img
                        v-if="product.primary_image"
                        :src="`/storage/${product.primary_image.path}`"
                        class="w-14 h-14 object-cover rounded"
                    />
                    <div v-else class="w-14 h-14 bg-gray-200 rounded"></div>
                </td>

               

                <td class="p-2">{{ product.title }}</td>
                <td class="p-2">{{ product.width }} x {{ product.height }}</td>
                <td class="p-2">{{ product.categories?.[0]?.name }}</td>
                <td class="p-2">
                    <Link :href="route('products.edit', product.id)" class="text-blue-600 hover:underline">Edit</Link>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
    products: Object,
})

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.products.data.map(p => p.id)
    } else {
        selected.value = []
    }
}

watch(selected, (val) => {
    if (val.length !== props.products.data.length) {
        selectAll.value = false
    } else {
        selectAll.value = true
    }
})

function bulkDelete() {
    if (confirm(`Delete ${selected.value.length} product(s)?`)) {
        router.post(route('products.bulk-delete'), {
            ids: selected.value,
        }, {
            onFinish: () => selected.value = [],
        })
    }
}
</script>
