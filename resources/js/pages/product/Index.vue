<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { ref, watch } from 'vue'
import { Head, router, Link } from '@inertiajs/vue3'
import ProductList from '@/components/ProductList.vue'

const props = defineProps({
    products: Object,
    filters: Object,
    test: String,
    stats: Object,
})

const search = ref(props.filters?.search || '')

let timer = null

watch(search, (value) => {
    clearTimeout(timer)

    timer = setTimeout(() => {
        router.get(route('products.index'), {
            search: value,
            status: props.filters?.status || null,
        }, {
            preserveState: true,
            replace: true,
        })
    }, 300)
})

const statusLink = (status = null) => {
    return route('products.index', {
        search: props.filters?.search || null,
        status,
    })
}
</script>

<template>
    <Head title="Products" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Products
            </h2>
        </template>

        <div class="max-w-7xl mx-auto mt-10 grid grid-cols-1 lg:grid-cols-4 gap-6">

            <div class="lg:col-span-3">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center p-3 gap-3">

                        <input
                            v-model="search"
                            placeholder="Search products..."
                            class="border p-2 rounded w-full md:w-80"
                        />

                        <Link
                            :href="route('products.create')"
                            class="bg-blue-600 text-white px-4 py-2 rounded whitespace-nowrap"
                        >
                            + Product
                        </Link>

                    </div>

                    <div class="p-4 pt-0">
                        <ProductList :products="products" />
                    </div>
                </div>
            </div>

            <aside class="lg:col-span-1 space-y-4">

                <div class="bg-white shadow rounded-xl p-5">
                    <h3 class="font-semibold mb-4">Stock Overview</h3>

                    <div class="space-y-2 text-sm">

                        <Link
                            :href="statusLink(null)"
                            class="flex justify-between p-2 rounded hover:bg-gray-100"
                            :class="{ 'bg-gray-100 font-semibold': !filters?.status }"
                        >
                            <span>Total Items</span>
                            <strong>{{ stats.total }}</strong>
                        </Link>

                        <Link
                            :href="statusLink('listed')"
                            class="flex justify-between p-2 rounded hover:bg-gray-100"
                            :class="{ 'bg-gray-100 font-semibold': filters?.status === 'listed' }"
                        >
                            <span>Listed</span>
                            <strong>{{ stats.listed }}</strong>
                        </Link>

                        <Link
                            :href="statusLink('pending')"
                            class="flex justify-between p-2 rounded hover:bg-gray-100"
                            :class="{ 'bg-gray-100 font-semibold': filters?.status === 'pending' }"
                        >
                            <span>Pending</span>
                            <strong>{{ stats.pending }}</strong>
                        </Link>

                        <Link
                            :href="statusLink('sold')"
                            class="flex justify-between p-2 rounded hover:bg-gray-100"
                            :class="{ 'bg-gray-100 font-semibold': filters?.status === 'sold' }"
                        >
                            <span>Sold</span>
                            <strong>{{ stats.sold }}</strong>
                        </Link>

                        <Link
                            :href="statusLink('not_sold')"
                            class="flex justify-between p-2 rounded hover:bg-gray-100 border-t pt-3"
                            :class="{ 'bg-gray-100 font-semibold': filters?.status === 'not_sold' }"
                        >
                            <span>Not Sold</span>
                            <strong>{{ stats.not_sold }}</strong>
                        </Link>

                    </div>
                </div>

                <div class="bg-white shadow rounded-xl p-5">
                    <h3 class="font-semibold mb-2">Stock Purchase Value</h3>

                    <p class="text-2xl font-bold">
                        £{{ Number(stats.not_sold_purchase_value || 0).toFixed(2) }}
                    </p>

                    <p class="text-xs text-gray-500 mt-1">
                        Value of unsold stock based on purchase price.
                    </p>
                </div>

            </aside>
        </div>
    </AuthenticatedLayout>
</template>