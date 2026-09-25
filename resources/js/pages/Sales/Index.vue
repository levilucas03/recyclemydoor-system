<script setup>
import { ref, watch } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { useDateFormatter } from '@/composables/useDateFormatter'
import debounce from 'lodash/debounce'

const { formatPretty } = useDateFormatter()

const props = defineProps({
    sales: Object,
    summary: Object,
    statusCounts: {
        type: [Object, Array],
        default: () => ({}),
    },
    categories: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
})

/*
|--------------------------------------------------------------------------
| FILTER STATE
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const category = ref(props.filters?.category || '')
const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

const sidebarOpen = ref(true)
const mobileFiltersOpen = ref(false)

/*
|--------------------------------------------------------------------------
| SELECTION
|--------------------------------------------------------------------------
*/

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.sales.data.map(sale => sale.id)
    } else {
        selected.value = []
    }
}

function toggleSale(id) {
    if (selected.value.includes(id)) {
        selected.value = selected.value.filter(item => item !== id)
    } else {
        selected.value.push(id)
    }

    selectAll.value =
        props.sales.data.length > 0 &&
        props.sales.data.every(sale => selected.value.includes(sale.id))
}

/*
|--------------------------------------------------------------------------
| FILTERING
|--------------------------------------------------------------------------
*/

function filterPayload() {
    return {
        search: search.value || undefined,
        status: status.value || undefined,
        category: category.value || undefined,
        start_date: startDate.value || undefined,
        end_date: endDate.value || undefined,
    }
}

function applyFilters() {
    router.get(
        route('sales.index'),
        filterPayload(),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

function setStatus(value) {
    status.value = value
    applyFilters()
}

function setCategory(value) {
    category.value = value
    applyFilters()
}

function applyDates() {
    applyFilters()
}

function clearDates() {
    startDate.value = ''
    endDate.value = ''
    applyFilters()
}

function clearFilters() {
    search.value = ''
    status.value = ''
    category.value = ''
    startDate.value = ''
    endDate.value = ''

    router.get(
        route('sales.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

watch(
    search,
    debounce(() => {
        applyFilters()
    }, 300)
)

/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

function goToSale(sale) {
    router.visit(route('sales.edit', sale.id))
}

function sendToXero(id) {
    router.post(
        route('sales.xero.push', id),
        {},
        {
            preserveScroll: true,
        }
    )
}

function syncEbaySales() {
    router.post(
        route('ebay.sync-sales'),
        {},
        {
            preserveScroll: true,
        }
    )
}

function bulkDelete() {
    if (!selected.value.length) {
        return
    }

    if (!confirm(`Delete ${selected.value.length} selected sales?`)) {
        return
    }

    router.post('/sales/bulk-delete', {
        ids: selected.value,
    })
}

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function money(value) {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(Number(value || 0))
}

function statusCount(key) {
    return Number(props.statusCounts?.[key] || 0)
}

function totalStatusCount() {
    return Object.values(props.statusCounts || {})
        .reduce((total, value) => total + Number(value || 0), 0)
}

function productItems(sale) {
    return (sale.items || []).filter(item => item.type === 'product')
}

function productQuantity(sale) {
    return productItems(sale)
        .reduce((total, item) => total + Number(item.qty || 0), 0)
}

/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE
|--------------------------------------------------------------------------
|
| Adjust the final property here if your ProductImage model uses
| something other than url/path.
|
*/

function productImage(product) {
    return product?.primary_image?.path ?? null
}

/*
|--------------------------------------------------------------------------
| CONTACT
|--------------------------------------------------------------------------
*/

function contactName(contact) {
    if (!contact) {
        return 'No customer'
    }

    if (contact.name) {
        return contact.name
    }

    return [
        contact.first_name,
        contact.last_name,
    ]
        .filter(Boolean)
        .join(' ') || 'No customer'
}

function addressLines(sale) {
    if (!sale) {
        return []
    }

    /*
    |--------------------------------------------------------------------------
    | Add/remove fields here to match your Contact table.
    |--------------------------------------------------------------------------
    */

    console.log(sale);

    return [
        sale.address_1 ?? '',
        sale.postcode,
        
    ].filter(Boolean)
}

function statusClasses(status) {
    switch (status) {
        case 'complete':
            return 'bg-green-100 text-green-700'

        case 'awaiting_delivery':
            return 'bg-orange-100 text-orange-700'

        case 'draft':
            return 'bg-gray-100 text-gray-600'

        case 'cancelled':
            return 'bg-red-100 text-red-700'

        default:
            return 'bg-blue-100 text-blue-700'
    }
}

const syncingWebsite = ref(false)

function syncWebsiteSales() {

    if (syncingWebsite.value) {
        return
    }

    syncingWebsite.value = true

    router.post(
        route('woocommerce.sync-sales'),
        {},
        {
            preserveScroll: true,

            onFinish: () => {
                syncingWebsite.value = false
            }
        }
    )
}
</script>


<template>

    <Head title="Sales" />

    <AuthenticatedLayout>

        <!-- HEADER -->

        <template #header>

            <div class="flex items-center justify-between gap-4">

                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        Sales
                    </h2>

                    <p class="mt-1 hidden text-sm text-gray-500 sm:block">
                        Manage orders and review sales performance
                    </p>
                </div>


                <div class="flex items-center gap-2">

                    <button
    type="button"
    @click="syncWebsiteSales"
    :disabled="syncingWebsite"
    class="inline-flex items-center gap-2 px-4 py-2 bg-[#173A2F] text-white rounded-lg text-sm font-medium hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
>
    <svg
        v-if="!syncingWebsite"
        xmlns="http://www.w3.org/2000/svg"
        class="w-4 h-4"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
    >
        <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 4v6h6M20 20v-6h-6M5.64 18.36A9 9 0 0020 12M4 12a9 9 0 0114.36-6.36"
        />
    </svg>

    <svg
        v-else
        class="w-4 h-4 animate-spin"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
    >
        <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
        />

        <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
        />
    </svg>

    {{ syncingWebsite ? 'Syncing...' : 'Sync Website Sales' }}
</button>

                    <button
                        type="button"
                        @click="syncEbaySales"
                        class="hidden rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:inline-flex"
                    >
                        Sync eBay
                    </button>


                    <Link
                        :href="route('sales.create')"
                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800"
                    >
                        Add Sale
                    </Link>

                </div>

            </div>

        </template>


        <div class="py-5 md:py-8">

            <div class="mx-auto max-w-[1600px] px-3 sm:px-6 lg:px-8">


                <!-- =====================================================
                     SUMMARY
                ====================================================== -->

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                    <!-- REVENUE -->

                    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">

                        <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Revenue
                        </div>

                        <div class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">
                            {{ money(summary?.revenue) }}
                        </div>

                        <div class="mt-2 text-xs text-gray-500">
                            {{ summary?.sales_count || 0 }}
                            sales
                        </div>

                    </div>


                    <!-- PRODUCTS -->

                    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">

                        <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Products Sold
                        </div>

                        <div class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">
                            {{ summary?.products_sold || 0 }}
                        </div>

                        <div class="mt-2 text-xs text-gray-500">
                            During selected period
                        </div>

                    </div>


                    <!-- COST -->

                    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">

                        <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Actual Cost
                        </div>

                        <div class="mt-2 text-xl font-bold text-gray-900 sm:text-2xl">
                            {{ money(summary?.total_cost) }}
                        </div>

                        <div class="mt-2 text-xs text-gray-500">

                            {{ money(summary?.product_cost) }} stock

                            <span v-if="Number(summary?.refurb_cost) > 0">
                                +
                                {{ money(summary?.refurb_cost) }}
                                refurb
                            </span>

                        </div>

                    </div>


                    <!-- PROFIT -->

                    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">

                        <div class="text-xs font-medium uppercase tracking-wide text-gray-400">
                            Gross Profit
                        </div>

                        <div
                            class="mt-2 text-xl font-bold sm:text-2xl"
                            :class="
                                Number(summary?.gross_profit) >= 0
                                    ? 'text-green-600'
                                    : 'text-red-600'
                            "
                        >
                            {{ money(summary?.gross_profit) }}
                        </div>

                        <div class="mt-2 text-xs text-gray-500">
                            {{ Number(summary?.margin || 0).toFixed(1) }}%
                            margin
                        </div>

                    </div>

                </div>


                <!-- =====================================================
                     MOBILE FILTER BUTTON
                ====================================================== -->

                <div class="mt-4 flex gap-2 lg:hidden">

                    <button
                        type="button"
                        @click="mobileFiltersOpen = !mobileFiltersOpen"
                        class="flex flex-1 items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium"
                    >
                        <span>Filters</span>

                        <span>
                            {{ mobileFiltersOpen ? '−' : '+' }}
                        </span>
                    </button>


                    <button
                        type="button"
                        @click="syncEbaySales"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium sm:hidden"
                    >
                        Sync
                    </button>

                </div>


                <!-- =====================================================
                     MAIN
                ====================================================== -->

                <div class="mt-4 flex items-start gap-5">


                    <!-- =================================================
                         DESKTOP SIDEBAR
                    ================================================== -->

                    <aside
                        class="hidden shrink-0 lg:block"
                        :class="
                            sidebarOpen
                                ? 'w-64'
                                : 'w-12'
                        "
                    >

                        <!-- COLLAPSE -->

                        <button
                            type="button"
                            @click="sidebarOpen = !sidebarOpen"
                            class="mb-3 flex w-full items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-500 hover:bg-gray-100"
                        >

                            <span class="text-lg">
                                {{ sidebarOpen ? '‹' : '›' }}
                            </span>

                            <span v-if="sidebarOpen">
                                Collapse
                            </span>

                        </button>


                        <div
                            v-if="sidebarOpen"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white"
                        >

                            <!-- STATUS -->

                            <div class="border-b border-gray-100 p-3">

                                <div class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Status
                                </div>


                                <button
                                    type="button"
                                    @click="setStatus('')"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm"
                                    :class="
                                        !status
                                            ? 'bg-gray-100 font-semibold text-gray-900'
                                            : 'text-gray-600 hover:bg-gray-50'
                                    "
                                >
                                    <span>All sales</span>

                                    <span>
                                        {{ totalStatusCount() }}
                                    </span>
                                </button>


                                <button
                                    v-for="(count, key) in statusCounts"
                                    :key="key"
                                    type="button"
                                    @click="setStatus(key)"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm capitalize"
                                    :class="
                                        status === key
                                            ? 'bg-gray-100 font-semibold text-gray-900'
                                            : 'text-gray-600 hover:bg-gray-50'
                                    "
                                >
                                    <span>
                                        {{ key.replaceAll('_', ' ') }}
                                    </span>

                                    <span>
                                        {{ count }}
                                    </span>
                                </button>

                            </div>


                            <!-- CATEGORIES -->

                            <div class="border-b border-gray-100 p-3">

                                <div class="mb-2 px-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Categories
                                </div>


                                <button
                                    type="button"
                                    @click="setCategory('')"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm"
                                    :class="
                                        !category
                                            ? 'bg-gray-100 font-semibold text-gray-900'
                                            : 'text-gray-600 hover:bg-gray-50'
                                    "
                                >
                                    <span>All products</span>

                                    <span>
                                        {{ summary?.products_sold || 0 }}
                                    </span>
                                </button>


                                <button
                                    v-for="item in categories"
                                    :key="item.id"
                                    type="button"
                                    @click="setCategory(item.id)"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm"
                                    :class="
                                        String(category) === String(item.id)
                                            ? 'bg-gray-100 font-semibold text-gray-900'
                                            : 'text-gray-600 hover:bg-gray-50'
                                    "
                                >
                                    <span class="truncate pr-2">
                                        {{ item.name }}
                                    </span>

                                    <span class="shrink-0">
                                        {{ item.count }}
                                    </span>
                                </button>

                            </div>


                            <!-- DATE -->

                            <div class="p-4">

                                <div class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Date Range
                                </div>


                                <label class="block">

                                    <span class="text-xs text-gray-500">
                                        From
                                    </span>

                                    <input
                                        v-model="startDate"
                                        type="date"
                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                    >

                                </label>


                                <label class="mt-3 block">

                                    <span class="text-xs text-gray-500">
                                        To
                                    </span>

                                    <input
                                        v-model="endDate"
                                        type="date"
                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                    >

                                </label>


                                <button
                                    type="button"
                                    @click="applyDates"
                                    class="mt-3 w-full rounded-lg bg-gray-900 px-3 py-2 text-sm font-semibold text-white"
                                >
                                    Apply Dates
                                </button>


                                <button
                                    v-if="startDate || endDate"
                                    type="button"
                                    @click="clearDates"
                                    class="mt-2 w-full px-3 py-2 text-xs font-medium text-gray-500 hover:text-gray-900"
                                >
                                    Clear dates
                                </button>

                            </div>

                        </div>

                    </aside>


                    <!-- =================================================
                         SALES AREA
                    ================================================== -->

                    <main class="min-w-0 flex-1">


                        <!-- MOBILE FILTER PANEL -->

                        <div
                            v-if="mobileFiltersOpen"
                            class="mb-4 rounded-xl border border-gray-200 bg-white p-4 lg:hidden"
                        >

                            <!-- STATUS -->

                            <div>

                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Status
                                </div>

                                <div class="mt-2 flex flex-wrap gap-2">

                                    <button
                                        type="button"
                                        @click="setStatus('')"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium"
                                        :class="
                                            !status
                                                ? 'border-gray-900 bg-gray-900 text-white'
                                                : 'border-gray-200 text-gray-600'
                                        "
                                    >
                                        All
                                        {{ totalStatusCount() }}
                                    </button>


                                    <button
                                        v-for="(count, key) in statusCounts"
                                        :key="key"
                                        type="button"
                                        @click="setStatus(key)"
                                        class="rounded-full border px-3 py-1.5 text-xs font-medium capitalize"
                                        :class="
                                            status === key
                                                ? 'border-gray-900 bg-gray-900 text-white'
                                                : 'border-gray-200 text-gray-600'
                                        "
                                    >
                                        {{ key.replaceAll('_', ' ') }}
                                        {{ count }}
                                    </button>

                                </div>

                            </div>


                            <!-- CATEGORY -->

                            <div class="mt-5">

                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Category
                                </div>

                                <select
                                    v-model="category"
                                    @change="applyFilters"
                                    class="mt-2 w-full rounded-lg border-gray-300 text-sm"
                                >
                                    <option value="">
                                        All products
                                    </option>

                                    <option
                                        v-for="item in categories"
                                        :key="item.id"
                                        :value="item.id"
                                    >
                                        {{ item.name }} ({{ item.count }})
                                    </option>
                                </select>

                            </div>


                            <!-- DATE -->

                            <div class="mt-5 grid grid-cols-2 gap-3">

                                <label>

                                    <span class="text-xs text-gray-500">
                                        From
                                    </span>

                                    <input
                                        v-model="startDate"
                                        type="date"
                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                    >

                                </label>


                                <label>

                                    <span class="text-xs text-gray-500">
                                        To
                                    </span>

                                    <input
                                        v-model="endDate"
                                        type="date"
                                        class="mt-1 w-full rounded-lg border-gray-300 text-sm"
                                    >

                                </label>

                            </div>


                            <div class="mt-4 flex gap-2">

                                <button
                                    type="button"
                                    @click="applyDates"
                                    class="flex-1 rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white"
                                >
                                    Apply
                                </button>


                                <button
                                    type="button"
                                    @click="clearFilters"
                                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600"
                                >
                                    Reset
                                </button>

                            </div>

                        </div>


                        <!-- SEARCH / RESULT HEADER -->

                        <div class="rounded-xl border border-gray-200 bg-white">

                            <div class="border-b border-gray-100 p-4">

                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

                                    <!-- SEARCH -->

                                    <div class="relative flex-1">

                                        <input
                                            v-model="search"
                                            type="search"
                                            placeholder="Search customer, phone, postcode, SKU or product..."
                                            class="w-full rounded-lg border-gray-300 px-4 py-2.5 text-sm"
                                        >

                                    </div>


                                    <!-- RESET -->

                                    <button
                                        v-if="
                                            search ||
                                            status ||
                                            category ||
                                            startDate ||
                                            endDate
                                        "
                                        type="button"
                                        @click="clearFilters"
                                        class="text-sm font-medium text-gray-500 hover:text-gray-900"
                                    >
                                        Reset
                                    </button>

                                </div>

                            </div>


                            <!-- RESULTS / BULK -->

                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3">

                                <div class="flex items-center gap-3">

                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleAll"
                                        class="rounded border-gray-300"
                                    >

                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ sales.total }} sales
                                    </div>

                                </div>


                                <div class="flex items-center gap-3">

                                    <button
                                        v-if="selected.length"
                                        type="button"
                                        @click="bulkDelete"
                                        class="text-sm font-medium text-red-600 hover:text-red-700"
                                    >
                                        Delete {{ selected.length }}
                                    </button>


                                    <div class="hidden text-sm font-semibold text-gray-900 sm:block">
                                        {{ money(summary?.revenue) }}
                                    </div>

                                </div>

                            </div>


                            <!-- =========================================
                                 DESKTOP SALES
                            ========================================== -->

                            <div class="hidden md:block">

                                <!-- TABLE HEADER -->

                                <div class="grid grid-cols-[110px_minmax(0,1fr)_70px_110px_130px_80px] gap-4 border-b border-gray-100 bg-gray-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">

                                    <div>
                                        Date
                                    </div>

                                    <div>
                                        Order
                                    </div>

                                    <div class="text-center">
                                        Qty
                                    </div>

                                    <div class="text-right">
                                        Total
                                    </div>

                                    <div>
                                        Status
                                    </div>

                                    <div class="text-right">
                                        Actions
                                    </div>

                                </div>


                                <!-- SALES -->

                                <div class="divide-y divide-gray-100">

                                    <div
                                        v-for="sale in sales.data"
                                        :key="sale.id"
                                        class="grid cursor-pointer grid-cols-[110px_minmax(0,1fr)_70px_110px_130px_80px] gap-4 px-4 py-3 transition hover:bg-gray-50"
                                        @click="goToSale(sale)"
                                    >

                                        <!-- DATE -->

                                        <div>

                                            <div class="text-sm font-semibold text-gray-900">
                                                {{
                                                    sale.invoice_date
                                                        ? formatPretty(sale.invoice_date)
                                                        : '—'
                                                }}
                                            </div>

                                            <div class="mt-1 text-xs text-gray-400">
                                                #{{ sale.id }}
                                            </div>

                                            <span
                                                v-if="sale.is_private"
                                                class="mt-2 inline-flex rounded bg-purple-100 px-2 py-1 text-[10px] font-medium text-purple-700"
                                            >
                                                Private
                                            </span>

                                        </div>


                                        <!-- ORDER -->

                                        <div class="min-w-0">

                                            <!-- CUSTOMER -->

                                            <div class="flex flex-wrap items-center gap-2">

                                                <span class="font-semibold text-gray-900">
                                                    {{ contactName(sale.contact) }}
                                                </span>

                                                <span
                                                    v-if="sale.source?.name"
                                                    class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-medium text-gray-500"
                                                >
                                                    {{ sale.source.name }}
                                                </span>

                                            </div>


                                            <!-- PRODUCTS -->

                                            <div
                                                v-if="productItems(sale).length"
                                                class="mt-3"
                                            >
                                                <div class="flex items-start gap-3">

                                                    <!-- FIRST 3 PRODUCTS -->

                                                    <div
                                                        v-for="item in productItems(sale).slice(0, 3)"
                                                        :key="item.id"
                                                        class="flex min-w-0 max-w-[220px] items-center gap-2"
                                                    >

                                                        <!-- IMAGE -->

                                                        <div class="shrink-0">

                                                            <img
                                                                v-if="productImage(item.product)"
                                                                :src="'/storage/' + productImage(item.product)"
                                                                :alt="item.product?.title"
                                                                class="h-14 w-14 rounded-md border border-gray-200 object-cover"
                                                            >

                                                            <div
                                                                v-else
                                                                class="flex h-14 w-14 items-center justify-center rounded-md border border-gray-200 bg-gray-50 text-[9px] text-gray-400"
                                                            >
                                                                No image
                                                            </div>

                                                        </div>


                                                        <!-- PRODUCT -->

                                                        <div class="min-w-0">

                                                            <div
                                                                class="line-clamp-2 text-xs font-medium leading-4 text-gray-900"
                                                            >
                                                                {{
                                                                    item.product?.title ||
                                                                    item.description ||
                                                                    'Product'
                                                                }}
                                                            </div>

                                                            <div
                                                                v-if="item.product?.sku"
                                                                class="mt-0.5 text-[10px] text-gray-400"
                                                            >
                                                                {{ item.product.sku }}
                                                            </div>

                                                            <div
                                                                v-if="Number(item.qty) > 1"
                                                                class="mt-0.5 text-[10px] text-gray-500"
                                                            >
                                                                Qty {{ item.qty }}
                                                            </div>

                                                        </div>

                                                    </div>


                                                    <!-- MORE PRODUCTS -->

                                                    <div
                                                        v-if="productItems(sale).length > 3"
                                                        class="flex h-14 min-w-[58px] shrink-0 items-center justify-center rounded-md border border-gray-200 bg-gray-50 px-2"
                                                    >
                                                        <span class="text-xs font-semibold text-gray-500">
                                                            +{{ productItems(sale).length - 3 }}
                                                            more
                                                        </span>
                                                    </div>

                                                </div>

                                            </div>


                                            <!-- OTHER ITEMS -->

                                            <div
                                                v-for="item in (sale.items || []).filter(item => item.type === 'other')"
                                                :key="`other-${item.id}`"
                                                class="mt-2 text-sm text-gray-600"
                                            >
                                                {{ item.description || 'Other item' }}
                                            </div>


                                            <!-- ADDRESS -->

                                            <div
                                                v-if="addressLines(sale.contact).length"
                                                class="mt-3 text-xs leading-5 text-gray-500"
                                            >

                                               <div
                                                    v-if="addressLines(sale.contact).length"
                                                    class="mt-2 truncate text-[11px] text-gray-500"
                                                >
                                                    {{ addressLines(sale.contact).join(', ') }}
                                                </div>

                                            </div>

                                        </div>


                                        <!-- QUANTITY -->

                                        <div class="text-center text-sm font-medium text-gray-900">
                                            {{ productQuantity(sale) }}
                                        </div>


                                        <!-- TOTAL -->

                                        <!-- <div class="text-right">

                                            <div class="text-sm font-bold text-gray-900">
                                                {{ money(sale.total_amount) }}
                                            </div>

                                            <div
                                                v-if="sale.fully_paid"
                                                class="mt-1 text-xs font-medium text-green-600"
                                            >
                                                Paid
                                            </div>

                                            <div
                                                v-else
                                                class="mt-1 text-xs text-gray-400"
                                            >
                                                Unpaid
                                            </div>

                                        </div> -->


                                        <!-- STATUS -->

                                        <div>

                                            <span
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                                                :class="statusClasses(sale.status)"
                                            >
                                                {{
                                                    sale.status_label ||
                                                    sale.status?.replaceAll('_', ' ')
                                                }}
                                            </span>

                                        </div>


                                        <!-- ACTIONS -->

                                        <div
                                            class="flex items-start justify-end gap-2"
                                            @click.stop
                                        >

                                            <!-- XERO -->

                                            <button
                                                v-if="!sale.xero_id"
                                                type="button"
                                                @click="sendToXero(sale.id)"
                                                title="Send to Xero"
                                                class="rounded-lg border border-gray-200 p-2 hover:bg-gray-50"
                                            >
                                                <img
                                                    src="/images/xero.svg"
                                                    class="h-5 w-5 grayscale"
                                                    alt="Xero"
                                                >
                                            </button>


                                            <img
                                                v-else
                                                src="/images/xero.svg"
                                                class="m-2 h-5 w-5"
                                                alt="Synced to Xero"
                                                title="Synced to Xero"
                                            >


                                            <!-- EDIT -->

                                            <Link
                                                :href="route('sales.edit', sale.id)"
                                                class="rounded-lg border border-gray-200 px-2.5 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-50"
                                            >
                                                Edit
                                            </Link>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- =========================================
                                 MOBILE / FOLD
                            ========================================== -->

                            <div class="divide-y divide-gray-100 md:hidden">

                                <div
                                    v-for="sale in sales.data"
                                    :key="sale.id"
                                    class="p-4"
                                >

                                    <!-- TOP -->

                                    <div class="flex items-start justify-between gap-3">

                                        <div>

                                            <div class="text-xs text-gray-400">
                                                {{
                                                    sale.invoice_date
                                                        ? formatPretty(sale.invoice_date)
                                                        : '—'
                                                }}
                                                ·
                                                #{{ sale.id }}
                                            </div>


                                            <div class="mt-1 font-semibold text-gray-900">
                                                {{ contactName(sale.contact) }}
                                            </div>

                                        </div>


                                        <div class="text-right">

                                            <div class="text-lg font-bold text-gray-900">
                                                {{ money(sale.total_amount) }}
                                            </div>

                                            <span
                                                class="mt-1 inline-flex rounded-full px-2 py-1 text-[10px] font-semibold"
                                                :class="statusClasses(sale.status)"
                                            >
                                                {{
                                                    sale.status_label ||
                                                    sale.status?.replaceAll('_', ' ')
                                                }}
                                            </span>

                                        </div>

                                    </div>


                                    <!-- PRODUCTS -->

                                    <div class="mt-4 space-y-3">

                                        <div
                                            v-for="item in productItems(sale)"
                                            :key="item.id"
                                            class="flex gap-3"
                                        >

                                            <!-- IMAGE -->

                                            <div class="shrink-0">

                                                <img
                                                    v-if="productImage(item.product)"
                                                    :src="'/storage/' + productImage(item.product)"
                                                    :alt="item.product?.title"
                                                    class="h-20 w-24 rounded-lg border border-gray-200 object-cover"
                                                >

                                                <div
                                                    v-else
                                                    class="flex h-20 w-24 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-[10px] text-gray-400"
                                                >
                                                    No image
                                                </div>

                                            </div>


                                            <!-- INFO -->

                                            <div class="min-w-0 flex-1">

                                                <div class="text-sm font-semibold leading-snug text-gray-900">
                                                    {{
                                                        item.product?.title ||
                                                        item.description ||
                                                        'Product'
                                                    }}
                                                </div>

                                                <div
                                                    v-if="item.product?.sku"
                                                    class="mt-1 text-xs text-gray-400"
                                                >
                                                    {{ item.product.sku }}
                                                </div>

                                                <div
                                                    v-if="Number(item.qty) > 1"
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Qty {{ item.qty }}
                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    <!-- ADDRESS -->

                                    <div
                                        v-if="addressLines(sale.contact).length"
                                        class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-xs leading-5 text-gray-600"
                                    >

                                        <div
                                            v-for="line in addressLines(sale.contact)"
                                            :key="line"
                                        >
                                            {{ line }}
                                        </div>

                                    </div>


                                    <!-- BOTTOM -->

                                    <div class="mt-4 flex items-center justify-between gap-3">

                                        <div class="flex items-center gap-2">

                                            <span
                                                v-if="sale.source?.name"
                                                class="text-xs font-medium text-gray-500"
                                            >
                                                {{ sale.source.name }}
                                            </span>

                                            <!-- <span
                                                v-if="sale.fully_paid"
                                                class="text-xs font-medium text-green-600"
                                            >
                                                Paid
                                            </span>

                                            <span
                                                v-else
                                                class="text-xs text-gray-400"
                                            >
                                                Unpaid
                                            </span> -->

                                        </div>


                                        <!-- ACTIONS -->

                                        <div
                                            class="flex items-center gap-2"
                                            @click.stop
                                        >

                                            <button
                                                v-if="!sale.xero_id"
                                                type="button"
                                                @click="sendToXero(sale.id)"
                                                class="rounded-lg border border-gray-200 p-2"
                                            >
                                                <img
                                                    src="/images/xero.svg"
                                                    class="h-5 w-5 grayscale"
                                                    alt="Xero"
                                                >
                                            </button>


                                            <img
                                                v-else
                                                src="/images/xero.svg"
                                                class="h-5 w-5"
                                                alt="Synced to Xero"
                                            >


                                            <Link
                                                :href="route('sales.edit', sale.id)"
                                                class="rounded-lg bg-gray-900 px-3 py-2 text-xs font-semibold text-white"
                                            >
                                                View
                                            </Link>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- EMPTY -->

                            <div
                                v-if="!sales.data.length"
                                class="px-6 py-16 text-center"
                            >

                                <div class="font-semibold text-gray-900">
                                    No sales found
                                </div>

                                <div class="mt-1 text-sm text-gray-500">
                                    Try changing your search or filters.
                                </div>

                            </div>

                        </div>


                        <!-- =================================================
                             PAGINATION
                        ================================================== -->

                        <div
                            v-if="sales.links?.length"
                            class="mt-4 flex flex-wrap gap-2"
                        >

                            <Link
                                v-for="link in sales.links"
                                :key="link.label"
                                :href="link.url || ''"
                                v-html="link.label"
                                preserve-scroll
                                preserve-state
                                class="rounded-lg border px-3 py-2 text-sm"
                                :class="[
                                    link.active
                                        ? 'border-gray-900 bg-gray-900 text-white'
                                        : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50',

                                    !link.url
                                        ? 'pointer-events-none opacity-40'
                                        : ''
                                ]"
                            />

                        </div>

                    </main>

                </div>

            </div>

        </div>

    </AuthenticatedLayout>

</template>