<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FuelCard from '@/Components/Dashboard/FuelCard.vue';
import PurchaseCard from '@/Components/Dashboard/PurchaseCard.vue';
import SaleCard from '@/Components/Dashboard/SaleCard.vue';
import ProfitCard from '@/Components/Dashboard/ProfitCard.vue';
import { Head } from '@inertiajs/vue3';
import DeliveryCoverageCard from '@/Components/Dashboard/DeliveryCoverageCard.vue';
import VehicleMileageCard from '@/Components/Dashboard/VehicleMileageCard.vue';

defineProps({
    purchaseStats: Object,
    fuel: Object,
    delivery: Object,
    salesStats: Object,
    profitStats: Object,
    vehicleMileageStats: {
        type: Array,
        default: () => []
    }
});

// helper to format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(value ?? 0);
};

const percentageChange = (current, previous) => {
    if (!previous) return 0
    return (((current - previous) / previous) * 100).toFixed(1)
}

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <PurchaseCard :purchaseStats="purchaseStats" />
            <FuelCard :fuel="fuel" />
            <DeliveryCoverageCard :delivery="delivery" />
            <SaleCard :salesStats="salesStats" />
            <ProfitCard :profitStats="profitStats" />
            <VehicleMileageCard
                :vehicle-mileage-stats="vehicleMileageStats"
            />
            


            
        </div>
    </AuthenticatedLayout>
</template>
