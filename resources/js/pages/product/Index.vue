<script setup lang="ts">
import { Card } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import LeviTry from '@/components/LeviTry.vue';
import { Head } from '@inertiajs/vue3';
import ProductList from '@/components/ProductList.vue';

defineProps({
    products: Array,
    test: String
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
                        <ProductList :products="products" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
