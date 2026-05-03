<script setup>
import { ref, computed } from 'vue'
import { router, Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    sales: Object
})

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.sales.data.map(s => s.id)
    } else {
        selected.value = []
    }
}

function bulkDelete() {
    if (!confirm('Delete selected sales?')) return

    router.post('/sales/bulk-delete', {
        ids: selected.value
    })
}

function goToSale(sale) {
    router.visit(`/sales/${sale.id}/edit`)
}
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Sales</h2>
            <a :href="route('sales.create')" class="text-blue-600 hover:underline">Add Sales</a>
        </template>
        <div class="py-6 md:py-12">
            <div class="max-w-7xl sm:px-6 lg:px-8 mx-auto">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4">

                        <!-- Bulk Actions -->
                        <div class="flex justify-between items-center mb-4">
                            <div v-if="selected.length > 0">
                                <button
                                    @click="bulkDelete"
                                    class="bg-red-600 text-white px-3 py-1 rounded"
                                >
                                    Delete Selected ({{ selected.length }})
                                </button>
                            </div>
                        </div>

                        <!-- Desktop -->
                        <div class="hidden md:block">
                            <table class="w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 text-center">
                                            <input type="checkbox" v-model="selectAll" @change="toggleAll" />
                                        </th>
                                        <th class="p-2 text-left">Ref</th>
                                        <th class="p-2 text-left">Customer</th>
                                        <th class="p-2 text-left">Date</th>
                                        <th class="p-2 text-left">Status</th>
                                        <th class="p-2 text-right">Total</th>
                                        <th class="p-2 text-center">Paid</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="sale in sales.data"
                                        :key="sale.id"
                                        class="border-t hover:bg-gray-50 cursor-pointer"
                                        @click="goToSale(sale)"
                                    >
                                        <!-- Checkbox -->
                                        <td class="p-2 text-center" @click.stop>
                                            <input
                                                type="checkbox"
                                                :value="sale.id"
                                                v-model="selected"
                                            />
                                        </td>

                                        <!-- Ref -->
                                        <td class="p-2 font-medium">
                                            #{{ sale.id }}
                                        </td>

                                        <!-- Customer -->
                                        <td class="p-2">
                                            {{ sale.contact?.name || 'No customer' }}
                                        </td>

                                        <!-- Date -->
                                        <td class="p-2">
                                            {{ sale.invoice_date || '-' }}
                                        </td>

                                        <!-- Status -->
                                        <td class="p-2">
                                            <span
                                                class="px-2 py-1 text-xs rounded"
                                                :class="{
                                                    'bg-gray-200': !sale.status_id,
                                                    'bg-yellow-200': sale.status_id == 1,
                                                    'bg-green-200': sale.status_id == 2
                                                }"
                                            >
                                                {{ sale.status_id == 2 ? 'Paid' : 'Pending' }}
                                            </span>
                                        </td>

                                        <!-- Total -->
                                        <td class="p-2 text-right font-semibold">
                                            £{{ Number(sale.total_amount).toFixed(2) }}
                                        </td>

                                        <!-- Paid -->
                                        <td class="p-2 text-center">
                                            <span v-if="sale.fully_paid" class="text-green-600">✔</span>
                                            <span v-else class="text-gray-400">✖</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                            <!-- Mobile -->
                        <div class="md:hidden space-y-3">
                            <div
                                v-for="sale in sales.data"
                                :key="sale.id"
                                class="border p-3 rounded shadow-sm"
                                @click="goToSale(sale)"
                            >
                                <div class="flex justify-between">
                                    <div class="font-semibold">#{{ sale.id }}</div>
                                    <div class="font-bold">
                                        £{{ Number(sale.total_amount).toFixed(2) }}
                                    </div>
                                </div>

                                <div class="text-sm text-gray-600">
                                    {{ sale.contact?.name || 'No customer' }}
                                </div>

                                <div class="text-sm">
                                    {{ sale.invoice_date || '-' }}
                                </div>

                                <div class="flex justify-between items-center mt-2">
                                    <span
                                        class="px-2 py-1 text-xs rounded bg-gray-200"
                                    >
                                        {{ sale.status_id == 2 ? 'Paid' : 'Pending' }}
                                    </span>

                                    <span v-if="sale.fully_paid" class="text-green-600">✔ Paid</span>
                                    <span v-else class="text-gray-400">Unpaid</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   </AuthenticatedLayout>
</template>