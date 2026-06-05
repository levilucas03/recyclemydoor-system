<script setup>

defineProps({
    profitStats: Object,
});

// helper to format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(value ?? 0);
};

</script>

<template>

<div class="bg-white rounded-xl p-6 shadow">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Profit Summary</h3>

        <span class="text-xs bg-gray-100 px-2 py-1 rounded">
            {{ profitStats.product_count }} products
        </span>
    </div>

     <form method="get" class="flex gap-2 mb-4">
        <input type="date" name="start_date" :value="profitStats.start_date" class="border rounded p-2">
        <input type="date" name="end_date" :value="profitStats.end_date" class="border rounded p-2">

        <button class="bg-gray-900 text-white px-4 rounded">
            Filter
        </button>
    </form>


    <div class="space-y-3 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Sales Revenue</span>
            <span class="font-semibold">{{ formatCurrency(profitStats.sales_revenue) }}</span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-500">Purchase Cost</span>
            <span class="font-semibold text-red-600">
                - {{ formatCurrency(profitStats.purchase_cost) }}
            </span>
        </div>

        <hr>

        <div class="flex justify-between text-lg">
            <span class="font-semibold">Gross Profit</span>
            <span class="font-bold text-green-600">
                {{ formatCurrency(profitStats.gross_profit) }}
            </span>
        </div>

        <div class="text-sm text-gray-500">
            Margin: {{ profitStats.margin }}%
        </div>
    </div>
</div>
</template>