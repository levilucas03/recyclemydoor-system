<script setup lang="ts">
import { Card } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import LeviTry from '@/components/LeviTry.vue';
import { Head } from '@inertiajs/vue3';
import PurchaseList from '@/components/PurchaseList.vue';
import { useDateFormatter } from '@/composables/useDateFormatter'

const { formatPretty } = useDateFormatter()

defineProps({
    purchases: Array,
    test: String,
    statusOptions: Array,
})

</script>

<template>
    <Head title="Dashboard" />
    

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Purchase</h2>
            <a :href="route('purchases.create')" class="text-blue-600 hover:underline">Add Purchase</a>
        </template>

        <div class="py-6 md:py-12">
            <div class="max-w-7xl sm:px-6 lg:px-8 mx-auto">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4">
                        <PurchaseList :purchases="purchases" :status-options="statusOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
