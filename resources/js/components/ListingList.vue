<script setup lang="ts">

import dayjs from 'dayjs'
import advancedFormat from 'dayjs/plugin/advancedFormat'
import { router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

dayjs.extend(advancedFormat)

const props = defineProps({
    listings: Object,
})

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.listings.data.map(p => p.id)
    } else {
        selected.value = []
    }
}

function getPrice(listing, type) {
    const product = listing.products?.[0]

    if (!product?.prices) {
        return 0
    }

    const price = product.prices.find(price => price.type === type)

    return Number(price?.price ?? 0)
}

watch(selected, (val) => {
    if (val.length !== props.listings.data.length) {
        selectAll.value = false
    } else {
        selectAll.value = true
    }
})

function bulkDelete() {
    if (confirm(`Delete ${selected.value.length} lisitng(s)?`)) {
        router.post(route('listings.bulk-delete'), {
            ids: selected.value,
        }, {
            onFinish: () => selected.value = [],
        })
    }
}

</script>

<template>

    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Products</h1>

        <!-- Bulk Action Button -->
        <div v-if="selected.length > 0" class="relative">
            <button @click="bulkDelete" class="bg-red-600 text-white px-3 py-1 rounded">
                Delete Selected ({{ selected.length }})
            </button>
        </div>
    </div>

    <table id="productList" class=" shadow rounded-lg min-w-full">
        <thead class="bg-gray-100 text-gray-700 text-sm text-left uppercase">
        <tr class="bg-gray-100">
            <th class="p-2 text-center"><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
            <th class="p-2 text-left">Date</th>
            <th class="p-2 text-left">Title</th>
            <th class="p-2 text-left">Website</th>
            <th class="p-2 text-left">eBay</th>
            <th class="p-2 text-left">Actions</th>
        </tr>
        </thead>
        <tbody class="text-sm divide-gray-200 divide-y">
            <tr v-for="listing in listings.data" :key="listing.id" class="border-t">
                <td class="p-2 text-center">
                    <input
                        type="checkbox"
                        :value="listing.id"
                        v-model="selected"
                    />
                </td>
                <td class="p-2">{{ dayjs(listing.created_at).format('Do MMM YY') }}</td>
                <td class="p-2">{{ listing.title }}</td>
               
                <td class="p-2 font-medium">
                    £{{ getPrice(listing, 'website').toFixed(2) }}
                </td>

                <td class="p-2 font-medium">
                    £{{ getPrice(listing, 'ebay').toFixed(2) }}
                </td>
                <td class="p-2">
                    <Link :href="route('listings.edit', listing.id)" class="text-blue-600 hover:underline">Edit</Link>
                    <div v-for="link in listing.platform_links" :key="link.id">
                        <button
                            v-if="link.status !== 'published'"
                            @click="router.post(route('listing-platform-links.publish', link.id))"
                            class="bg-green-600 text-white px-3 py-1 rounded text-xs"
                        >
                            Push to {{ link.platform.name }}
                        </button>

                        <span v-else class="text-green-600 text-xs">
                            Published to {{ link.platform.name }}
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

</template>

<style scoped>

#productList th {
    background-color: #9ec8cb;
}

#productList tr:nth-child(even){background-color: #f2f2f2;}

</style>
