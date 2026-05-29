<script setup lang="ts">
import { Card } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3';
import ProductList from '@/components/ProductList.vue';

const props = defineProps({
    products: Array,
    filters: Object,
    test: String,
    stats: Object
})

const search = ref(props.filters?.search || '')

let timer = null

watch(search, (value) => {

    clearTimeout(timer)

    timer = setTimeout(() => {

        router.get('/products', {
            search: value
        }, {
            preserveState: true,
            replace: true
        })

    }, 300)

})

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Products</h2>
            <!-- <a :href="route('products.create')" class="text-blue-600 hover:underline">Add Product</a> -->
        </template>
        
        <div class="max-w-7xl mx-auto mt-10 grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Main product list -->
            <div class="lg:col-span-3">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="flex justify-between items-center p-3">

                        <input
                            v-model="search"
                            placeholder="Search products..."
                            class="border p-2 rounded w-80"
                        />

                        <a
                            href="/products/create"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                        >
                            + Product
                        </a>

                    </div>

                    <div class="p-4 pt-0">
                        <ProductList :products="products" />

                    </div>
                </div>
            </div>
            <!-- Sidebar stats -->
            <aside class="lg:col-span-1 space-y-4">

                <div class="bg-white shadow rounded-xl p-5">
                    <h3 class="font-semibold mb-4">Stock Overview</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span>Total Items</span>
                            <strong>{{ stats.total }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span>Listed</span>
                            <strong>{{ stats.listed }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span>Pending</span>
                            <strong>{{ stats.pending }}</strong>
                        </div>

                        <div class="flex justify-between">
                            <span>Sold</span>
                            <strong>{{ stats.sold }}</strong>
                        </div>

                        <div class="flex justify-between border-t pt-3">
                            <span>Not Sold</span>
                            <strong>{{ stats.not_sold }}</strong>
                        </div>
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
