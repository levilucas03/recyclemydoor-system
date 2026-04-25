<template>

    <div v-if="$page.props.flash?.success" class="bg-green-100 p-2 mb-2">
        {{ $page.props.flash.success }}
    </div>

    <div v-if="$page.props.flash?.error" class="bg-red-100 p-2 mb-2">
        {{ $page.props.flash.error }}
    </div>


    <div>
        <div class="flex justify-between items-center mb-4">
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
                <th class="p-2 text-left">Date</th>
                <th class="p-2 text-left">Name</th>
                <th class="p-2 text-left">No. Products</th>
                <th class="p-2 text-left">Price</th>
                <th class="p-2 text-left"></th>
                <th class="p-2 text-left"></th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="purchase in purchases.data" :key="purchase.id" class="border-t">

                    <td class="p-2 text-center">
                        <input
                            type="checkbox"
                            :value="purchase.id"
                            v-model="selected"
                        />
                    </td>

                    <td class="p-2">
                        {{ purchase.purchase_date ? formatPretty(purchase.purchase_date) : '-' }}
                    </td>

                    <td class="p-2">
                        {{ purchase.contact?.name || '—' }}
                    </td>

                    <td class="p-2">
                        {{ purchase.products_count }}
                    </td>

                    <td class="p-2">
                        {{ purchase.total_amount }}
                    </td>

                    <td class="p-2">
                        <Link :href="route('purchases.edit', purchase.id)" class="text-blue-600 hover:underline">
                            Edit
                        </Link>
                    </td>
                    <td class="p-2">
                        <button
                            v-if="!purchase.xero_id"
                            @click="sendToXero(purchase.id)"
                            class="bg-indigo-600 text-white px-2 py-1 rounded text-xs"
                        >
                            Send
                        </button>

                        <span v-else class="text-green-600 text-xs">
                            Sent
                        </span>
                    </td>

                </tr>
                </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">

import { ref, watch } from 'vue'
import { useDateFormatter } from '@/composables/useDateFormatter'
import { router, Link } from '@inertiajs/vue3'


function sendToXero(id) {
    if (!confirm('Send this purchase to Xero?')) return

    router.post(route('purchases.xero', id), {}, {
        preserveScroll: true,
    })
}

const { formatPretty } = useDateFormatter()

const props = defineProps({
    purchases: Object,
})

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (!props.purchases?.data) return

    if (selectAll.value) {
        selected.value = props.purchases.data.map(p => p.id)
    } else {
        selected.value = []
    }
}

watch(selected, (val) => {
    const total = props.purchases?.data?.length || 0

    selectAll.value = val.length === total
})

function bulkDelete() {
    if (confirm(`Delete ${selected.value.length} purchase(s)?`)) {
        router.post(route('purchases.bulk-delete'), {
            ids: selected.value,
        }, {
            onFinish: () => selected.value = [],
        })
    }
}
</script>
