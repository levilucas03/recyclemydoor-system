<script setup>
const props = defineProps({
    delivery: Object
})

const money = (val) => {
    return Number(val || 0).toFixed(2)
}
</script>

<template>
    <div class="bg-white rounded-xl p-6 shadow mt-4">

        <h2 class="text-lg font-semibold mb-4">
            Delivery Coverage
        </h2>

        <!-- WEEK -->
        <div class="mb-6">

            <p class="text-sm text-gray-500">This Week</p>

            <div class="mt-2 space-y-1 text-sm">

                <div class="flex justify-between">
                    <span>Delivery Revenue</span>
                    <strong>£{{ money(delivery.this_week_revenue) }}</strong>
                </div>

                <div class="flex justify-between">
                    <span>Fuel Spend</span>
                    <strong>£{{ money($page.props.fuel.this_week) }}</strong>
                </div>

                <div class="flex justify-between border-t pt-2">
                    <span>Difference</span>

                    <strong
                        :class="delivery.weekly_profit >= 0
                            ? 'text-green-600'
                            : 'text-red-600'"
                    >
                        £{{ money(delivery.weekly_profit) }}
                    </strong>
                </div>

                <div class="text-xs text-gray-500 mt-1">
                    {{ delivery.weekly_coverage }}% fuel coverage
                </div>

            </div>
        </div>

        <!-- MONTH -->
        <div>

            <p class="text-sm text-gray-500">This Month</p>

            <div class="mt-2 space-y-1 text-sm">

                <div class="flex justify-between">
                    <span>Delivery Revenue</span>
                    <strong>£{{ money(delivery.this_month_revenue) }}</strong>
                </div>

                <div class="flex justify-between">
                    <span>Fuel Spend</span>
                    <strong>£{{ money($page.props.fuel.this_month) }}</strong>
                </div>

                <div class="flex justify-between border-t pt-2">
                    <span>Difference</span>

                    <strong
                        :class="delivery.monthly_profit >= 0
                            ? 'text-green-600'
                            : 'text-red-600'"
                    >
                        £{{ money(delivery.monthly_profit) }}
                    </strong>
                </div>

                <div class="text-xs text-gray-500 mt-1">
                    {{ delivery.monthly_coverage }}% fuel coverage
                </div>

                <div class="mb-4 pb-4 border-b">

                    <p class="text-sm text-gray-500">
                        Total Delivery Revenue
                    </p>

                    <p class="text-2xl font-bold">
                        £{{ money(delivery.total_revenue) }}
                    </p>

                </div>

            </div>
        </div>

    </div>
</template>