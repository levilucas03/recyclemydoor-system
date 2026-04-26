<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    fuel: Object
})

const percentageChange = (current, previous) => {
    if (!previous) return 0
    return (((current - previous) / previous) * 100).toFixed(1)
}

const difference = (current, previous) => {
    return current - previous
}

const money = (val) => {
    return Number(val || 0).toFixed(2)
}
</script>

<template>
    <div class="bg-white rounded-xl p-6 shadow mt-4">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Fuel Spend</h2>
        </div>

        <div class="space-y-4">

            <!-- Weekly -->
            <div>
                <p class="text-sm text-gray-500">This Week</p>

                <p class="text-xl font-bold">£{{ money(fuel.this_week) }}</p>

                <div class="mt-1 text-sm">
                    <span
                        v-if="difference(fuel.this_week, fuel.last_week) > 0"
                        class="text-red-500 font-medium"
                    >
                        ↑ £{{ money(difference(fuel.this_week, fuel.last_week)) }} more than last week
                    </span>

                    <span
                        v-else
                        class="text-green-500 font-medium"
                    >
                        ↓ £{{ money(Math.abs(difference(fuel.this_week, fuel.last_week))) }} less than last week
                    </span>

                    <p class="text-xs text-gray-500 mt-1">
                        Last week: £{{ money(fuel.last_week) }}
                    </p>
                </div>
                <Link
                    :href="route('fuel-logs.index', { period: 'this_week' })"
                    class="text-xs text-blue-600 mt-2 inline-block"
                >
                    View this week's logs →
                </Link>
            </div>

            <!-- Monthly -->
            <div>
                <p class="text-sm text-gray-500">This Month</p>

                <p class="text-xl font-bold">£{{ money(fuel.this_month) }}</p>

                <div class="mt-1 text-sm">
                    <span
                        v-if="difference(fuel.this_month, fuel.last_month) > 0"
                        class="text-red-500 font-medium"
                    >
                        ↑ £{{ money(difference(fuel.this_month, fuel.last_month)) }} more than last month
                    </span>

                    <span
                        v-else
                        class="text-green-500 font-medium"
                    >
                        ↓ £{{ money(Math.abs(difference(fuel.this_month, fuel.last_month))) }} less than last month
                    </span>

                    <p class="text-xs text-gray-500 mt-1">
                        Last month: £{{ money(fuel.last_month) }}
                    </p>
                </div>
                <Link
                    :href="route('fuel-logs.index', { period: 'this_month' })"
                    class="text-xs text-blue-600 mt-2 inline-block"
                >
                    View this month's logs →
                </Link>

                <div class="mb-4 pb-4 border-b">
                    <p class="text-sm text-gray-500">Total Fuel Spend</p>
                    <p class="text-2xl font-bold">£{{ money(fuel.total) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>