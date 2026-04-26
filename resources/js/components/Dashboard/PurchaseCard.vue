<script setup>

defineProps({
    purchaseStats: Object,
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
    <div class="bg-white rounded-xl p-6 shadow mt-4">
        <h2 class="text-lg font-semibold mb-4">Purchases</h2>

        <div class="space-y-3 text-sm">

            <div class="flex justify-between">
                <span>Today</span>
                <span>{{ formatCurrency(purchaseStats.today) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Last 7 days</span>
                <span>{{ formatCurrency(purchaseStats.last7) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Last 31 days</span>
                <div class="flex items-center gap-2">
                    <span
                        :class="purchaseStats.percentageChange > 0 ? 'text-red-500' : 'text-green-500'"
                    >
                        {{ purchaseStats.percentageChange > 0 ? '▲' : '▼' }}
                        {{ purchaseStats.percentageChange }}%
                    </span>
                    <span>{{ formatCurrency(purchaseStats.last31) }}</span>
                </div>
            </div>

            <div class="flex justify-between">
                <span>Last 90 days</span>
                <span>{{ formatCurrency(purchaseStats.last90) }}</span>
            </div>

            <div class="flex justify-between">
                <span>This year</span>
                <span>{{ formatCurrency(purchaseStats.yearToDate) }}</span>
            </div>

            <div class="flex justify-between">
                <span>Last year</span>
                <span>{{ formatCurrency(purchaseStats.lastYear) }}</span>
            </div>

            <div class="flex justify-between pt-3 mt-3 border-t font-semibold">
                <span>Grand total</span>
                <span>{{ formatCurrency(purchaseStats.grandTotal) }}</span>
            </div>

            <div class="flex justify-between pt-3 mt-3 border-t">
                <span>Avg daily (31d)</span>
                <span>{{ formatCurrency(purchaseStats.avgDaily) }}</span>
            </div>

        </div>
    </div>


</template>
