<script setup lang="ts">
import { Card } from '@/components/ui/card';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import LeviTry from '@/components/LeviTry.vue';
import { Head } from '@inertiajs/vue3';
import ListingList from '@/components/ListingList.vue';

defineProps({
    listings: Array,
})

</script>

<template>
    <Head title="Listings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Listings</h2>
            <a :href="route('listings.create')" class="text-blue-600 hover:underline">Add Listing</a>

        </template>

        <div class="py-12">
            <div class="max-w-7xl sm:px-6 lg:px-8 mx-auto">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4">
                        <ListingList :listings=listings />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
