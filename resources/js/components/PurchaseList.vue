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
            <div v-if="selected.length > 0" class="flex gap-2 items-center">
                <!-- Status dropdown -->
                <select v-model="bulkStatus" class="border rounded px-2 py-1">
                    <option value="">Change status</option>
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <!-- Apply button -->
                <button
                    @click="applyBulkStatus"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    Update ({{ selected.length }})
                </button>

                <!-- Existing delete -->
                <button
                    @click="bulkDelete"
                    class="bg-red-600 text-white px-3 py-1 rounded"
                >
                    Delete
                </button>
            </div>
        </div>
        <div class="hidden md:block">

            <table class="w-full border text-sm">
                <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-center"><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
                    <th class="p-2 text-left">Date</th>
                    <th class="p-2 text-left">Name</th>
                    <th class="p-2 text-left">No. Products</th>
                    <th class="p-2 text-left">Status</th>
                    <th class="p-2 text-left">Source</th>
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
                            <span
                                class="px-2 py-1 text-xs rounded"
                                :class="{
                                    'bg-gray-200': purchase.status == 'draft',
                                    'bg-yellow-200': purchase.status == 'awaiting_collection',
                                    'bg-green-200': purchase.status == 'complete'
                                }">
                                    {{ purchase.status_label }}
                            </span>
                        </td>

                        <td class="p-2">
                            {{ purchase.source?.name ?? '—' }}
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
                                class="text-white px-2 py-1 rounded text-xs"
                            >

                                <img
                                    src="/images/xero.svg"
                                    class="w-5 h-5"
                                    :class="'grayscale'"
                                />
                            </button>

                            <img v-else src="/images/xero.svg"
                                class="w-5 h-5"
                                :class="'opacity-100'"
                            />
                        </td>

                    </tr>
                    </tbody>
            </table>
        </div>
        <div class="md:hidden space-y-3">
            <Link
                v-for="purchase in purchases.data"
                :key="purchase.id"
                :href="route('purchases.edit', purchase.id)"
                class="block bg-white rounded-xl shadow p-4 active:scale-[0.98] transition"
            >

                <!-- TOP ROW -->
                <div class="flex justify-between items-center mb-2">
                    <p class="font-medium truncate">
                        {{ purchase.contact?.name || 'Unknown' }}
                    </p>

                    <span class="text-xs px-2 py-1 rounded bg-gray-100">
                        {{ purchase.purchase_date ? formatPretty(purchase.purchase_date) : '-' }}
                    </span>
                </div>

                <!-- DETAILS -->
                <div class="flex justify-between text-sm text-gray-600">

                    <div>
                        {{ purchase.products_count }} items
                    </div>

                    <div class="font-semibold text-black">
                        £{{ purchase.total_amount }}
                    </div>

                </div>
            </Link>
        </div>
        <!-- Pagination -->
        <div class="mt-4 flex gap-2">
            <Link
                v-for="link in purchases.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="px-3 py-1 border rounded"
                :class="{ 'bg-gray-200': link.active }"
            />
        </div>
    </div>
</template>

<script setup lang="ts">

import { ref, watch } from 'vue'
import { useDateFormatter } from '@/composables/useDateFormatter'
import { router, Link } from '@inertiajs/vue3'
import axios from 'axios'


const bulkStatus = ref('')


function sendToXero(id) {
    if (!confirm('Send this purchase to Xero?')) return

    router.post(route('purchases.xero', id), {}, {
        preserveScroll: true,
    })
}

const { formatPretty } = useDateFormatter()

const props = defineProps({
    purchases: Object,
    statusOptions: Array
})

const selected = ref([])
const selectAll = ref(false)

const applyBulkStatus = () => {
    if (!bulkStatus.value) {
        alert('Select a status first')
        return
    }

    axios.post(route('purchases.bulkStatus'), {
        ids: selected.value,
        status: bulkStatus.value
    }).then(() => {
        window.location.reload()
    })
}

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
