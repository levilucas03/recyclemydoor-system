<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Comparison from '@/Components/Analytics/Comparison.vue'
import { router, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    BarElement,
} from 'chart.js'

import { Line, Bar } from 'vue-chartjs'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
)

const props = defineProps({
    filters: Object,
    summary: Object,
    activitySummary: Object,
    activityFeed: Array,
    comparison: Object,
    trend: Array,
    trendGrouping: String,
    stockHealth: Object,
    
})

const activityChartData = computed(() => {

    return {
        labels: props.trend.map(item => {

            return new Date(
                item.date + 'T00:00:00'
            ).toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
            })

        }),

        datasets: [

            {
                label: 'Money In',

                data: props.trend.map(
                    item => item.money_in
                ),

                borderColor: '#16a34a',
                backgroundColor: 'rgba(22, 163, 74, 0.08)',

                borderWidth: 2,

                tension: 0.35,

                pointRadius: 3,
                pointHoverRadius: 6,

                fill: false,
            },


            {
                label: 'Money Out',

                data: props.trend.map(
                    item => item.money_out
                ),

                borderColor: '#dc2626',
                backgroundColor: 'rgba(220, 38, 38, 0.08)',

                borderWidth: 2,

                tension: 0.35,

                pointRadius: 3,
                pointHoverRadius: 6,

                fill: false,
            },


            {
                label: 'Gross Profit',

                data: props.trend.map(
                    item => item.gross_profit
                ),

                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.08)',

                borderWidth: 2,

                tension: 0.35,

                pointRadius: 3,
                pointHoverRadius: 6,

                fill: false,
            },

        ],
    }

})

const activityChartOptions = {

    responsive: true,

    maintainAspectRatio: false,

    interaction: {
        mode: 'index',
        intersect: false,
    },

    plugins: {

        legend: {
            position: 'top',

            labels: {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
            },
        },

        tooltip: {

            callbacks: {

                label: function (context) {

                    return `${context.dataset.label}: ${money(
                        context.raw
                    )}`

                },

            },

        },

    },

    scales: {

        x: {
            grid: {
                display: false,
            },
        },

        y: {

            beginAtZero: true,

            ticks: {

                callback: function (value) {

                    return new Intl.NumberFormat(
                        'en-GB',
                        {
                            style: 'currency',
                            currency: 'GBP',
                            maximumFractionDigits: 0,
                        }
                    ).format(value)

                },

            },

        },

    },

}

const spendingChartData = computed(() => {

    return {

        labels: props.trend.map(
            item => item.label
        ),

        datasets: [

            {
                label: 'Stock Purchases',

                data: props.trend.map(
                    item => item.purchases
                ),

                backgroundColor: '#f59e0b',

                borderRadius: 4,

                stack: 'spending',
            },

            {
                label: 'Parts',

                data: props.trend.map(
                    item => item.parts
                ),

                backgroundColor: '#8b5cf6',

                borderRadius: 4,

                stack: 'spending',
            },

            {
                label: 'Fuel',

                data: props.trend.map(
                    item => item.fuel
                ),

                backgroundColor: '#3b82f6',

                borderRadius: 4,

                stack: 'spending',
            },

        ],

    }

})

const spendingChartOptions = {

    responsive: true,

    maintainAspectRatio: false,

    interaction: {
        mode: 'index',
        intersect: false,
    },

    plugins: {

        legend: {

            position: 'top',

            labels: {
                usePointStyle: true,
                boxWidth: 8,
                boxHeight: 8,
            },

        },

        tooltip: {

            callbacks: {

                label: function (context) {

                    return `${context.dataset.label}: ${money(
                        context.raw
                    )}`

                },

                footer: function (items) {

                    const total = items.reduce(
                        (sum, item) => sum + Number(item.raw),
                        0
                    )

                    return `Total spent: ${money(total)}`

                },

            },

        },

    },

    scales: {

        x: {

            stacked: true,

            grid: {
                display: false,
            },

        },

        y: {

            stacked: true,

            beginAtZero: true,

            ticks: {

                callback: function (value) {

                    return new Intl.NumberFormat(
                        'en-GB',
                        {
                            style: 'currency',
                            currency: 'GBP',
                            maximumFractionDigits: 0,
                        }
                    ).format(value)

                },

            },

        },

    },

}

const money = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(value ?? 0)
}

const startDate = ref(props.filters.start_date)
const endDate = ref(props.filters.end_date)

const applyDates = () => {
    router.get(
        route('analytics.index'),
        {
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

const selectedRange = ref('30')

const setRange = (range) => {

    selectedRange.value = range

    const today = new Date()

    let start = new Date(today)
    let end = new Date(today)

    if (range === 'today') {
        // already today
    }

    if (range === '7') {
        start.setDate(today.getDate() - 6)
    }

    if (range === '30') {
        start.setDate(today.getDate() - 29)
    }

    if (range === '90') {
        start.setDate(today.getDate() - 89)
    }

    if (range === 'year') {
        start = new Date(today.getFullYear(), 0, 1)
    }

    const formatDate = (date) => {
        const year = date.getFullYear()
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')

        return `${year}-${month}-${day}`
    }

    startDate.value = formatDate(start)
    endDate.value = formatDate(end)

    applyDates()
}

</script>

<template>
    <AuthenticatedLayout>


        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    Analytics
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Business performance and trends
                </p>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            

            <div class="rounded-xl border border-gray-200 bg-white p-5">

                <div class="flex flex-wrap gap-2 mb-3">

    <button
        v-for="range in [
            { key: 'today', label: 'Today' },
            { key: '7', label: '7 Days' },
            { key: '30', label: '30 Days' },
            { key: '90', label: '90 Days' },
            { key: 'year', label: 'This Year' },
        ]"
        :key="range.key"
        type="button"
        @click="setRange(range.key)"
        class="rounded-lg px-4 py-2 text-sm font-medium transition"
        :class="
            selectedRange === range.key
                ? 'bg-gray-900 text-white'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
        "
    >
        {{ range.label }}
    </button>

</div>

                <div class="flex flex-col gap-4 md:flex-row md:items-end">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            From
                        </label>

                       <input
                            v-model="startDate"
                            @change="selectedRange = 'custom'"
                            type="date"
                            class="rounded-lg border-gray-300"
                        >
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">
                            To
                        </label>

                       <input
                            v-model="endDate"
                            @change="selectedRange = 'custom'"
                            type="date"
                            class="rounded-lg border-gray-300"
                        >
                    </div>

                    <button
                        type="button"
                        @click="applyDates"
                        class="rounded-lg bg-gray-900 px-5 py-2.5 font-medium text-white"
                    >
                        Apply
                    </button>

                </div>

            </div>

            <!-- BUSINESS ACTIVITY -->

<div class="mt-6">

    <div class="mb-3">
        <h3 class="text-lg font-semibold text-gray-900">
            Business Activity
        </h3>

        <p class="text-sm text-gray-500">
            Money received and spent during this period
        </p>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

        <!-- MONEY IN -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Money In
            </div>

            <div class="mt-2 text-3xl font-bold text-green-600">
                {{ money(activitySummary.money_in) }}

                <Comparison
                    :value="comparison.activity.money_in"
                />
            </div>

            <div class="mt-4 border-t border-gray-100 pt-3">

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        Sales
                    </span>

                    <span class="font-semibold text-gray-900">
                        {{ money(activitySummary.sales) }}
                    </span>
                </div>

            </div>

        </div>


        <!-- MONEY OUT -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Money Out
            </div>

            <div class="mt-2 text-3xl font-bold text-red-600">
                {{ money(activitySummary.money_out) }}
                <Comparison
                    :value="comparison.activity.money_out"
                    :inverse="true"
                />
            </div>

            <div class="mt-4 space-y-2 border-t border-gray-100 pt-3">

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        Stock Purchases
                    </span>

                    <span class="font-medium text-gray-900">
                        {{ money(activitySummary.purchases) }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        Parts
                    </span>

                    <span class="font-medium text-gray-900">
                        {{ money(activitySummary.parts) }}
                    </span>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">
                        Fuel
                    </span>

                    <span class="font-medium text-gray-900">
                        {{ money(activitySummary.fuel) }}
                    </span>
                </div>

            </div>

        </div>


        <!-- NET ACTIVITY -->
        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Net Activity
            </div>

            <div
                class="mt-2 text-3xl font-bold"
                :class="
                    activitySummary.net_activity >= 0
                        ? 'text-green-600'
                        : 'text-red-600'
                "
            >
                {{ money(activitySummary.net_activity) }}

                <Comparison
                    :value="comparison.activity.net_activity"
                />
            </div>

            <div class="mt-4 border-t border-gray-100 pt-3 text-sm">

                <template v-if="activitySummary.net_activity > 0">
                    <span class="text-gray-500">
                        Money in exceeded spending by
                    </span>

                    <span class="ml-1 font-semibold text-green-600">
                        {{ money(activitySummary.net_activity) }}
                    </span>
                </template>

                <template v-else-if="activitySummary.net_activity < 0">
                    <span class="text-gray-500">
                        Spending exceeded money in by
                    </span>

                    <span class="ml-1 font-semibold text-red-600">
                        {{ money(Math.abs(activitySummary.net_activity)) }}
                    </span>
                </template>

                <template v-else>
                    <span class="text-gray-500">
                        Money in and spending were equal.
                    </span>
                </template>

            </div>

        </div>

    </div>

</div>


<!-- BUSINESS TREND -->

<div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">

    <div class="mb-5">

        <h3 class="text-lg font-semibold text-gray-900">
            Business Trend
        </h3>

       <p class="mt-1 text-sm text-gray-500">

            <template v-if="trendGrouping === 'daily'">
                Daily money in, money out and gross profit
            </template>

            <template v-else-if="trendGrouping === 'weekly'">
                Weekly money in, money out and gross profit
            </template>

            <template v-else>
                Monthly money in, money out and gross profit
            </template>

        </p>

    </div>


    <div class="h-[380px]">

        <Line
            :data="activityChartData"
            :options="activityChartOptions"
        />

    </div>

</div>

<!-- SPENDING BREAKDOWN -->

<div class="mt-6 rounded-xl border border-gray-200 bg-white p-5">

    <div class="mb-5">

        <h3 class="text-lg font-semibold text-gray-900">
            Spending Breakdown
        </h3>

        <p class="mt-1 text-sm text-gray-500">

            <template v-if="trendGrouping === 'daily'">
                Daily spending by category
            </template>

            <template v-else-if="trendGrouping === 'weekly'">
                Weekly spending by category
            </template>

            <template v-else>
                Monthly spending by category
            </template>

        </p>

    </div>


    <div class="h-[340px]">

        <Bar
            :data="spendingChartData"
            :options="spendingChartOptions"
        />

    </div>

</div>

<!-- STOCK HEALTH -->

<div class="mt-8">

    <div class="mb-4">
        <h2 class="text-xl font-semibold text-gray-900">
            Stock Health
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            Current stock value, investment and ageing
        </p>
    </div>


    <!-- MAIN CARDS -->

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

        <!-- CURRENT STOCK -->

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Current Stock
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-900">
                {{ stockHealth.total_stock }}
            </div>

            <div class="mt-3 flex gap-3 text-xs">

                <span class="text-gray-500">
                    Pending
                    <strong class="text-gray-900">
                        {{ stockHealth.pending }}
                    </strong>
                </span>

                <span class="text-gray-500">
                    Listed
                    <strong class="text-gray-900">
                        {{ stockHealth.listed }}
                    </strong>
                </span>

            </div>

        </div>


        <!-- MONEY INVESTED -->

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Money Invested
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-900">
                {{ money(stockHealth.invested_value) }}
            </div>

            <div class="mt-3 space-y-1 text-xs text-gray-500">

                <div class="flex justify-between">
                    <span>Stock</span>

                    <span class="font-medium text-gray-900">
                        {{ money(stockHealth.purchase_value) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Refurb / Parts</span>

                    <span class="font-medium text-gray-900">
                        {{ money(stockHealth.parts_value) }}
                    </span>
                </div>

            </div>

        </div>


        <!-- RETAIL VALUE -->

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Retail Value
            </div>

            <div class="mt-2 text-3xl font-bold text-gray-900">
                {{ money(stockHealth.retail_value) }}
            </div>

            <div class="mt-3 text-xs text-gray-500">
                Current website asking value
            </div>

        </div>


        <!-- POTENTIAL PROFIT -->

        <div class="rounded-xl border border-gray-200 bg-white p-5">

            <div class="text-sm font-medium text-gray-500">
                Potential Gross Profit
            </div>

            <div
                class="mt-2 text-3xl font-bold"
                :class="
                    stockHealth.potential_gross_profit >= 0
                        ? 'text-green-600'
                        : 'text-red-600'
                "
            >
                {{ money(stockHealth.potential_gross_profit) }}
            </div>

            <div class="mt-3 text-xs text-gray-500">
                If sold at current asking prices
            </div>

        </div>

    </div>


    <!-- SECONDARY CARDS -->

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">

        <div class="rounded-xl border border-gray-200 bg-white p-4">

            <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                Average Cost / Product
            </div>

            <div class="mt-1 text-xl font-semibold text-gray-900">
                {{ money(stockHealth.average_cost) }}
            </div>

        </div>


        <div class="rounded-xl border border-gray-200 bg-white p-4">

            <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                Average Asking Price
            </div>

            <div class="mt-1 text-xl font-semibold text-gray-900">
                {{ money(stockHealth.average_retail) }}
            </div>

        </div>

    </div>

</div>

<!-- STOCK AGE -->

<div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="border-b border-gray-100 px-5 py-4">

        <h3 class="font-semibold text-gray-900">
            Stock Age
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            Time since stock was purchased
        </p>

    </div>


    <div class="grid grid-cols-2 divide-x divide-y divide-gray-100 md:grid-cols-5 md:divide-y-0">

        <!-- 0 - 30 -->

        <div class="p-5">

            <div class="text-sm text-gray-500">
                0–30 days
            </div>

            <div class="mt-2 text-2xl font-bold text-gray-900">
                {{ stockHealth.age['0_30'] }}
            </div>

            <div class="mt-1 text-xs text-gray-400">
                Fresh stock
            </div>

        </div>


        <!-- 31 - 60 -->

        <div class="p-5">

            <div class="text-sm text-gray-500">
                31–60 days
            </div>

            <div class="mt-2 text-2xl font-bold text-gray-900">
                {{ stockHealth.age['31_60'] }}
            </div>

            <div class="mt-1 text-xs text-gray-400">
                Normal
            </div>

        </div>


        <!-- 61 - 90 -->

        <div class="p-5">

            <div class="text-sm text-gray-500">
                61–90 days
            </div>

            <div class="mt-2 text-2xl font-bold text-gray-900">
                {{ stockHealth.age['61_90'] }}
            </div>

            <div class="mt-1 text-xs text-gray-400">
                Watch
            </div>

        </div>


        <!-- 91 - 180 -->

        <div class="p-5">

            <div class="text-sm text-gray-500">
                91–180 days
            </div>

            <div class="mt-2 text-2xl font-bold text-orange-600">
                {{ stockHealth.age['91_180'] }}
            </div>

            <div class="mt-1 text-xs text-orange-500">
                Ageing
            </div>

        </div>


        <!-- 180+ -->

        <div class="col-span-2 p-5 md:col-span-1">

            <div class="text-sm text-gray-500">
                180+ days
            </div>

            <div class="mt-2 text-2xl font-bold text-red-600">
                {{ stockHealth.age['180_plus'] }}
            </div>

            <div class="mt-1 text-xs text-red-500">
                Stale stock
            </div>

        </div>

    </div>

</div>

<!-- OLDEST STOCK -->

<div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white">

    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">

        <div>

            <h3 class="font-semibold text-gray-900">
                Oldest Stock
            </h3>

            <p class="mt-1 text-sm text-gray-500">
                Products that have been held the longest
            </p>

        </div>

    </div>


    <!-- DESKTOP -->

    <div class="hidden overflow-x-auto md:block">

        <table class="min-w-full divide-y divide-gray-100">

            <thead class="bg-gray-50">

                <tr>

                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Product
                    </th>

                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Status
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Age
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Cost
                    </th>

                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Asking
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-gray-100">

                <tr
                    v-for="product in stockHealth.oldest"
                    :key="product.id"
                    class="hover:bg-gray-50"
                >

                    <!-- PRODUCT -->

                    <td class="px-5 py-4">

                        <div class="flex ">

                            <div>
                                 <img
    v-if="product.primary_image"
    :src="'/storage/' + product.primary_image.path"
    :alt="product.title"
    class="h-16 w-20 rounded-lg border border-gray-200 object-cove mr-3"
>
                            </div>

                            <div>
 <Link
                            :href="route('products.edit', product.id)"
                            class="text-sm font-semibold text-gray-900 hover:underline"
                        >
                            {{ product.title }}
                        </Link>

                        <div class="mt-1 text-xs text-gray-400">
                            {{ product.sku }}
                            •
                            Purchased {{ product.purchase_date }}
                        </div>
                            </div>

                        </div>

                       

                       

                    </td>


                    <!-- STATUS -->

                    <td class="px-5 py-4">

                        <span
                            class="inline-flex rounded-full px-2 py-1 text-xs font-medium capitalize"
                            :class="
                                product.status === 'listed'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-gray-100 text-gray-600'
                            "
                        >
                            {{ product.status }}
                        </span>

                    </td>


                    <!-- AGE -->

                    <td class="px-5 py-4 text-right">

                        <span
                            class="text-sm font-semibold"
                            :class="{
                                'text-gray-900':
                                    product.age_days <= 90,

                                'text-orange-600':
                                    product.age_days > 90 &&
                                    product.age_days <= 180,

                                'text-red-600':
                                    product.age_days > 180,
                            }"
                        >
                            {{ product.age_days }}d
                        </span>

                    </td>


                    <!-- COST -->

                    <td class="px-5 py-4 text-right">

                        <div class="text-sm font-medium text-gray-900">
                            {{ money(product.total_cost) }}
                        </div>

                        <div
                            v-if="product.parts_cost > 0"
                            class="mt-1 text-xs text-gray-400"
                        >
                            includes
                            {{ money(product.parts_cost) }}
                            refurb
                        </div>

                    </td>


                    <!-- ASKING -->

                    <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">

                        <template v-if="product.website_price > 0">
                            {{ money(product.website_price) }}
                        </template>

                        <span
                            v-else
                            class="font-normal text-gray-400"
                        >
                            —
                        </span>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    <!-- MOBILE -->

    <div class="divide-y divide-gray-100 md:hidden">

        <div
            v-for="product in stockHealth.oldest"
            :key="product.id"
            class="p-4"
        >

            <div class="flex items-start justify-between gap-4">

                <div class="min-w-0">

                    <div class="truncate text-sm font-semibold text-gray-900">
                        {{ product.title }}
                    </div>

                    <div class="mt-1 text-xs text-gray-400">
                        {{ product.sku }}
                    </div>

                </div>


                <div
                    class="shrink-0 text-sm font-bold"
                    :class="{
                        'text-gray-900':
                            product.age_days <= 90,

                        'text-orange-600':
                            product.age_days > 90 &&
                            product.age_days <= 180,

                        'text-red-600':
                            product.age_days > 180,
                    }"
                >
                    {{ product.age_days }}d
                </div>

            </div>


            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">

                <div>

                    <div class="text-xs text-gray-400">
                        Cost
                    </div>

                    <div class="mt-1 font-medium">
                        {{ money(product.total_cost) }}
                    </div>

                </div>


                <div>

                    <div class="text-xs text-gray-400">
                        Asking
                    </div>

                    <div class="mt-1 font-medium">

                        <template v-if="product.website_price > 0">
                            {{ money(product.website_price) }}
                        </template>

                        <span v-else>
                            —
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- ACTIVITY FEED -->

<div class="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white">

    <!-- HEADER -->

    <div class="border-b border-gray-100 px-5 py-4">

        <h3 class="font-semibold text-gray-900">
            Activity
        </h3>

        <p class="mt-1 text-sm text-gray-500">
            {{ activityFeed.length }} transactions during this period
        </p>

    </div>


    <!-- FEED -->

    <div class="divide-y divide-gray-100">

        <div
            v-for="item in activityFeed"
            :key="item.id"
            class="flex items-center gap-3 px-4 py-3 transition hover:bg-gray-50 sm:gap-4 sm:px-5"
        >

            <!-- TYPE -->

            <div class="w-20 shrink-0 sm:w-24">

                <span
                    class="inline-flex rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wide"
                    :class="{
                        'bg-green-100 text-green-700':
                            item.type === 'sale',

                        'bg-orange-100 text-orange-700':
                            item.type === 'purchase',

                        'bg-purple-100 text-purple-700':
                            item.type === 'part',

                        'bg-blue-100 text-blue-700':
                            item.type === 'fuel',
                    }"
                >
                    {{ item.type }}
                </span>

            </div>


            <!-- DETAILS -->

            <div class="min-w-0 flex-1">

                <div class="flex items-center gap-2">

                    <span
                        v-if="item.reference"
                        class="shrink-0 text-sm font-semibold text-gray-900"
                    >
                        {{ item.reference }}
                    </span>

                    <span class="truncate text-sm text-gray-700">
                        {{ item.title }}
                    </span>

                </div>


                <div class="mt-1 flex min-w-0 items-center gap-2 text-xs text-gray-400">

                    <span class="shrink-0">
                        {{ item.date }}
                    </span>

                    <template v-if="item.subtitle">

                        <span>
                            •
                        </span>

                        <span class="truncate">
                            {{ item.subtitle }}
                        </span>

                    </template>

                </div>

            </div>


            <!-- AMOUNT -->

            <div
                class="shrink-0 text-right text-sm font-bold sm:text-base"
                :class="
                    item.direction === 'in'
                        ? 'text-green-600'
                        : 'text-red-600'
                "
            >

                {{ item.direction === 'in' ? '+' : '-' }}

                {{ money(item.amount) }}

            </div>

        </div>


        <!-- EMPTY STATE -->

        <div
            v-if="!activityFeed.length"
            class="px-5 py-12 text-center"
        >

            <div class="text-sm font-medium text-gray-600">
                No activity
            </div>

            <div class="mt-1 text-xs text-gray-400">
                Nothing was recorded during this period.
            </div>

        </div>

    </div>

</div>

<div class=" mt-3">
        <h3 class="text-lg font-semibold text-gray-900">
            Sales Performance
        </h3>

       
    </div>

            <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
                

    <!-- SALES REVENUE -->
    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <div class="text-sm text-gray-500">
            Sales Revenue
        </div>

        <div class="mt-2 text-2xl font-bold text-gray-900">
            {{ money(summary.sales_revenue) }}
        </div>

        <div class="mt-1 text-xs text-gray-400">
            {{ summary.products_sold }} products sold
        </div>

    </div>


    <!-- TOTAL COST -->
    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <div class="text-sm text-gray-500">
            Product Cost
        </div>

        <div class="mt-2 text-2xl font-bold text-gray-900">
            {{ money(summary.total_cost) }}
        </div>

        <div class="mt-1 text-xs text-gray-400">

            {{ money(summary.purchase_cost) }}
            stock

            +

            {{ money(summary.parts_cost) }}
            refurb

        </div>

    </div>


    <!-- GROSS PROFIT -->
    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <div class="text-sm text-gray-500">
            Gross Profit
            
        </div>

        <div
            class="mt-2 text-2xl font-bold"
            :class="
                summary.gross_profit >= 0
                    ? 'text-green-600'
                    : 'text-red-600'
            "
        >
            {{ money(summary.gross_profit) }}

            <Comparison
    :value="comparison.sales.gross_profit"
/>
        </div>

        <div class="mt-1 text-xs text-gray-400">
            {{ summary.margin }}% margin
        </div>

    </div>


    <!-- PRODUCTS SOLD -->
    <div class="rounded-xl border border-gray-200 bg-white p-5">

        <div class="text-sm text-gray-500">
            Products Sold
            
        </div>

        <div class="mt-2 text-2xl font-bold text-gray-900">
            {{ summary.products_sold }}
            <Comparison
                :value="comparison.sales.products_sold"
            />
        </div>

        <div class="mt-1 text-xs text-gray-400">
            {{ money(summary.average_sale) }}
            average sale
        </div>

    </div>

</div>


<!-- BREAKDOWN -->

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

    <div class="rounded-xl border border-gray-200 bg-white p-4">

        <div class="text-xs uppercase tracking-wide text-gray-400">
            Original Stock Cost
        </div>

        <div class="mt-1 text-lg font-semibold">
            {{ money(summary.purchase_cost) }}
        </div>

    </div>


    <div class="rounded-xl border border-gray-200 bg-white p-4">

        <div class="text-xs uppercase tracking-wide text-gray-400">
            Refurb / Parts
        </div>

        <div class="mt-1 text-lg font-semibold">
            {{ money(summary.parts_cost) }}
        </div>

    </div>


    <div class="rounded-xl border border-gray-200 bg-white p-4">

        <div class="text-xs uppercase tracking-wide text-gray-400">
            Average Profit / Product
        </div>

        <div
            class="mt-1 text-lg font-semibold"
            :class="
                summary.average_profit >= 0
                    ? 'text-green-600'
                    : 'text-red-600'
            "
        >
            {{ money(summary.average_profit) }}
        </div>

    </div>

</div>

<pre class="mt-6 overflow-auto rounded-xl bg-gray-900 p-5 text-xs text-white">
{{ stockHealth }}
</pre>





        </div>

    </AuthenticatedLayout>
</template>