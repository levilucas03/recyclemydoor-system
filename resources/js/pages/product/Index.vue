<script setup lang="ts">
import { Card } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { ref, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3';
import ProductList from '@/components/ProductList.vue';

const props = defineProps({
    products: Array,
    filters: Object,
    test: String
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

        

        



        <div class="py-6 md:py-12">
            <div class="max-w-7xl sm:px-6 lg:px-8 mx-auto">

                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4">

                        <div class="flex justify-between items-center mb-4">

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

                        <ProductList :products="products" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
