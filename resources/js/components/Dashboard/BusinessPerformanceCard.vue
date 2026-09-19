<script setup lang="ts">

import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    businessPerformance: {
        type: Object,
        required: true,
    }, 
    dailyTrend: Array,
})

const stats = ref({ ...props.businessPerformance })

const selectedPeriod = ref('31')

const customFrom = ref(props.businessPerformance.from ?? '')
const customTo = ref(props.businessPerformance.to ?? '')

const loading = ref(false)

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(Number(value ?? 0))
}

const loadPeriod = async (period) => {

    selectedPeriod.value = period

    // Don't request until custom dates are supplied
    if (period === 'custom') {
        return
    }

    loading.value = true

    try {

        const response = await axios.get(
            route('dashboard.business-performance'),
            {
                params: {
                    period: period,
                }
            }
        )

        stats.value = response.data

    } catch (error) {

        console.error('Unable to load business performance', error)

    } finally {

        loading.value = false
    }
}

const loadCustom = async () => {

    if (!customFrom.value || !customTo.value) {
        return
    }

    loading.value = true

    try {

        const response = await axios.get(
            route('dashboard.business-performance'),
            {
                params: {
                    period: 'custom',
                    from: customFrom.value,
                    to: customTo.value,
                }
            }
        )

        stats.value = response.data

    } catch (error) {

        console.error('Unable to load custom period', error)

    } finally {

        loading.value = false
    }
}

</script>


<template>

       

    <div class="bg-white rounded-xl p-6 shadow mt-4">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-5">

            <div>
                <h3 class="text-lg font-semibold">
                    Business Performance
                </h3>

                <p class="text-xs text-gray-500 mt-1">
                    Income vs direct business costs
                </p>
            </div>

            <div
                v-if="loading"
                class="text-xs text-gray-400"
            >
                Loading...
            </div>

        </div>


        <!-- PERIOD BUTTONS -->
        <div class="flex flex-wrap gap-2 mb-6">

            <button
                type="button"
                @click="loadPeriod('today')"
                :class="selectedPeriod === 'today'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-600'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium"
            >
                Today
            </button>

            <button
                type="button"
                @click="loadPeriod('7')"
                :class="selectedPeriod === '7'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-600'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium"
            >
                7 Days
            </button>

            <button
                type="button"
                @click="loadPeriod('31')"
                :class="selectedPeriod === '31'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-600'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium"
            >
                31 Days
            </button>

            <button
                type="button"
                @click="loadPeriod('90')"
                :class="selectedPeriod === '90'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-600'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium"
            >
                90 Days
            </button>

            <button
                type="button"
                @click="loadPeriod('custom')"
                :class="selectedPeriod === 'custom'
                    ? 'bg-gray-900 text-white'
                    : 'bg-gray-100 text-gray-600'"
                class="px-3 py-1.5 rounded-lg text-xs font-medium"
            >
                Custom
            </button>

        </div>


        <!-- CUSTOM DATE RANGE -->
        <div
            v-if="selectedPeriod === 'custom'"
            class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6 bg-gray-50 p-4 rounded-lg"
        >

            <div>
                <label class="block text-xs text-gray-500 mb-1">
                    From
                </label>

                <input
                    v-model="customFrom"
                    type="date"
                    class="border rounded-lg p-2 w-full text-sm"
                />
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">
                    To
                </label>

                <input
                    v-model="customTo"
                    type="date"
                    class="border rounded-lg p-2 w-full text-sm"
                />
            </div>

            <div class="flex items-end">

                <button
                    type="button"
                    @click="loadCustom"
                    :disabled="loading || !customFrom || !customTo"
                    class="bg-gray-900 text-white rounded-lg px-4 py-2 w-full text-sm disabled:opacity-50"
                >
                    Apply
                </button>

            </div>

        </div>


        <!-- INCOME -->
        <div class="mb-5">

            <div class="text-xs uppercase tracking-wide text-gray-400 mb-3">
                Income
            </div>

            <div class="flex justify-between items-center">

                <span class="text-sm">
                    Sales
                </span>

                <span class="font-semibold text-green-600">
                    {{ formatCurrency(stats.income?.sales) }}
                </span>

            </div>

        </div>


        <!-- OUTGOINGS -->
        <div class="border-t pt-5">

            <div class="text-xs uppercase tracking-wide text-gray-400 mb-3">
                Outgoings
            </div>

            <div class="space-y-3 text-sm">

                <div class="flex justify-between">
                    <span>Purchases</span>

                    <span>
                        {{ formatCurrency(stats.outgoings?.purchases) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Parts</span>

                    <span>
                        {{ formatCurrency(stats.outgoings?.parts) }}
                    </span>
                </div>

                <div class="flex justify-between">
                    <span>Fuel</span>

                    <span>
                        {{ formatCurrency(stats.outgoings?.fuel) }}
                    </span>
                </div>

                <div class="flex justify-between pt-3 border-t font-semibold">
                    <span>Total Outgoings</span>

                    <span class="text-red-600">
                        {{ formatCurrency(stats.outgoings?.total) }}
                    </span>
                </div>

            </div>

        </div>


        <!-- PROFIT -->
        <div class="border-t mt-5 pt-5">

            <div class="flex justify-between items-end">

                <div>
                    <div class="text-sm text-gray-500">
                        Profit
                    </div>

                    <div
                        class="text-2xl font-bold mt-1"
                        :class="stats.profit >= 0
                            ? 'text-green-600'
                            : 'text-red-600'"
                    >
                        {{ formatCurrency(stats.profit) }}
                    </div>
                </div>

                <div class="text-right">

                    <div class="text-xs text-gray-500">
                        Margin
                    </div>

                    <div
                        class="font-semibold"
                        :class="stats.margin >= 0
                            ? 'text-green-600'
                            : 'text-red-600'"
                    >
                        {{ stats.margin ?? 0 }}%
                    </div>

                </div>

            </div>

        </div>


        <!-- DATE RANGE -->
        <div class="mt-5 pt-4 border-t text-xs text-gray-400 text-center">

            {{ stats.from }} — {{ stats.to }}

        </div>

    </div>

 

</template>