<script setup>

defineProps({
    salesStats: Object,
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

    <div class="bg-white rounded-xl p-6 shadow mb-4">

        <h3 class="text-lg font-semibold mb-4">Sales</h3>

        <div class="space-y-4 text-sm">

            <!-- TODAY -->
            <div class="flex justify-between items-center">
                <div>
                    <div>Today</div>
                    <div class="text-xs text-gray-500">
                        {{ salesStats.today.products }} products
                    </div>
                </div>

                <div class="font-medium">
                    {{ formatCurrency(salesStats.today.revenue) }}
                </div>
            </div>

            <!-- 7 DAYS -->
            <div class="flex justify-between items-center">
                <div>
                    <div>Last 7 days</div>
                    <div class="text-xs text-gray-500">
                        {{ salesStats.last_7_days.products }} products
                    </div>
                </div>

                <div class="font-medium">
                    {{ formatCurrency(salesStats.last_7_days.revenue) }}
                </div>
            </div>

            <!-- 31 DAYS -->
            <div class="flex justify-between items-center">
                <div>
                    <div>Last 31 days</div>

                    <div class="text-xs text-gray-500">
                        {{ salesStats.last_31_days.products }} products
                    </div>
                </div>

                <div class="flex items-center gap-2">

                    <span
                        v-if="salesStats.percentageChange !== null"
                        :class="salesStats.percentageChange > 0
                            ? 'text-green-500'
                            : 'text-red-500'"
                        class="text-xs font-semibold"
                    >
                        {{ salesStats.percentageChange > 0 ? '▲' : '▼' }}
                        {{ Math.abs(salesStats.percentageChange) }}%
                    </span>

                    <span class="font-medium">
                        {{ formatCurrency(salesStats.last_31_days.revenue) }}
                    </span>
                </div>
            </div>

            <!-- 90 DAYS -->
            <div class="flex justify-between items-center">
                <div>
                    <div>Last 90 days</div>

                    <div class="text-xs text-gray-500">
                        {{ salesStats.last_90_days.products }} products
                    </div>
                </div>

                <div class="font-medium">
                    {{ formatCurrency(salesStats.last_90_days.revenue) }}
                </div>
            </div>

            <!-- TOTAL -->
            <div class="flex justify-between items-center pt-3 mt-3 border-t font-semibold">

                <div>
                    <div>Grand total</div>

                    <div class="text-xs text-gray-500 font-normal">
                        {{ salesStats.total.products }} products
                    </div>
                </div>

                <div class="text-base">
                    {{ formatCurrency(salesStats.total.revenue) }}
                </div>
            </div>
        </div>
    </div>
</template>
