<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    parts: Object,
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(value ?? 0)
}

const destroy = (id) => {
    if (confirm('Delete this part?')) {
        router.delete(route('parts.destroy', id))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold">Parts</h2>

                <Link
                    :href="route('parts.create')"
                    class="px-4 py-2 bg-black text-white rounded"
                >
                    Add Part
                </Link>
            </div>
        </template>

        <div class="max-w-6xl mx-auto mt-8 bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="p-3">Name</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Qty</th>
                        <th class="p-3">Total Cost</th>
                        <th class="p-3">Unit Cost</th>
                        <th class="p-3">Purchased</th>
                        <th class="p-3 text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="part in parts.data"
                        :key="part.id"
                        class="border-t"
                    >
                        <td class="p-3 font-medium">{{ part.name }}</td>
                        <td class="p-3">{{ part.sku ?? '-' }}</td>
                        <td class="p-3">{{ part.total_quantity }}</td>
                        <td class="p-3">{{ formatCurrency(part.total_cost) }}</td>
                        <td class="p-3">{{ formatCurrency(part.unit_cost) }}</td>
                        <td class="p-3">{{ part.purchased_at ?? '-' }}</td>

                        <td class="p-3 text-right space-x-2">
                            <Link
                                :href="route('parts.edit', part.id)"
                                class="text-blue-600"
                            >
                                Edit
                            </Link>

                            <button
                                @click="destroy(part.id)"
                                class="text-red-600"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>