<template>
    <div>
        <div class="flex justify-between items-center mb-4">
            <!-- Bulk Action Button -->
            <div v-if="selected.length > 0" class="relative">
                <button @click="bulkDelete" class="bg-red-600 text-white px-3 py-1 rounded">
                    Delete Selected ({{ selected.length }})
                </button>
            </div>
        </div>

        <div class="hidden md:block">
            <table class="w-full border text-sm">
                <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 text-center">
                        <input type="checkbox" v-model="selectAll" @change="toggleAll" />
                    </th>
                    <th></th>
                    <th class="p-2 text-left">Sku</th>
                    <th class="p-2 text-left">Title</th>
                    <th class="p-2 text-left">Size</th>
                    <th class="p-2 text-left">Category</th>
                    <th class="p-2 text-left">Status</th>
                    <th></th>
                    <th></th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="product in products.data" :key="product.id" class="border-t">
                    <td class="p-2 text-center">
                        <input type="checkbox" :value="product.id" v-model="selected" />
                    </td>

                    <td class="p-2">
                        <img
                            v-if="product.primary_image"
                            :src="`/storage/${product.primary_image.path}`"
                            class="w-14 h-14 object-cover rounded cursor-pointer"
                            @click.stop="openPreview(`/storage/${product.primary_image.path}`)"
                        />
                        <div v-else class="w-14 h-14 bg-gray-200 rounded"></div>
                    </td>

                    <td class="p-2">{{ product.sku }}</td>
                    <td class="p-2">{{ product.title }}</td>
                    <td class="p-2">{{ product.width }} x {{ product.height }}</td>
                    <td class="p-2">{{ product.categories?.[0]?.name }}</td>
                    <td class="p-2">{{ product.status }}</td>
                    <td>
                        <div class="mt-2 flex items-center gap-2">
                            <Link
                                v-if="hasWooCommerceId(product)"
                                :href="route('listings.edit', product.listing.id)"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-purple-100 text-purple-700 hover:bg-purple-200"
                                title="Published to WooCommerce"
                            >
                                W
                            </Link>

                            <Link
                                v-else-if="product.listing"
                                :href="route('listings.edit', product.listing.id)"
                                class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200"
                                title="Listing created but not pushed"
                            >
                                W
                            </Link>

                            <Link
                                v-else
                                :href="route('listings.create', { product_id: product.id })"
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-500 hover:bg-gray-200"
                                title="Create listing for this product"
                            >
                                Not listed
                            </Link>
                        </div>
                    </td>

                    <td class="p-2">
                        <Link :href="route('products.edit', product.id)" class="text-blue-600">
                            Edit
                        </Link>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            <Link
                v-for="product in products.data"
                :key="product.id"
                :href="route('products.edit', product.id)"
                class="block bg-white rounded-xl shadow p-3 active:scale-[0.98] transition"
            >
                <div class="flex gap-3 items-center">

                    <!-- IMAGE -->
                    <div class="w-16 h-16 flex-shrink-0">
                        <img
                            v-if="product.primary_image"
                            :src="`/storage/${product.primary_image.path}`"
                            class="w-full h-full object-cover rounded cursor-pointer"
                            @click.stop.prevent="openPreview(`/storage/${product.primary_image.path}`)"
                        />
                        
                        <div v-else class="w-full h-full bg-gray-200 rounded"></div>
                    </div>

                    <!-- TEXT -->
                    <div class="flex-1 min-w-0">
                        <p class="font-medium truncate">
                            {{ product.title }}
                        </p>

                        <p class="text-sm text-gray-500">
                            {{ product.categories?.[0]?.name }}
                        </p>
                    </div>

                    <!-- OPTIONAL CHEVRON -->
                    <div class="text-gray-400">
                        →
                    </div>

                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex gap-2">
            <Link
                v-for="link in products.links"
                :key="link.label"
                :href="link.url || ''"
                v-html="link.label"
                class="px-3 py-1 border rounded"
                :class="{ 'bg-gray-200': link.active }"
            />
        </div>
    </div>

    <ImagePreview
        :src="previewImage"
        :show="showPreview"
        @close="showPreview = false"
    />
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'

import ImagePreview from '@/components/ImagePreview.vue'

const previewImage = ref<string | null>(null)
const showPreview = ref(false)

const hasWooCommerceId = (product) => {
    return product.listing?.platform_links?.some(link => link.external_id)
}

const openPreview = (src: string) => {
    previewImage.value = src
    showPreview.value = true
}

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
