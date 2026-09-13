<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    jobs: {
        type: Array,
        default: () => [],
    },
})

const selected = ref([])
const startPostcode = ref('NN10 9LL')
const filter = ref('all')

const filteredJobs = computed(() => {
    if (filter.value === 'collection') {
        return props.jobs.filter(job => job.type === 'collection')
    }

    if (filter.value === 'delivery') {
        return props.jobs.filter(job => job.type === 'delivery')
    }

    return props.jobs
})

const selectedJobs = computed(() => {
    return props.jobs.filter(job => selected.value.includes(job.id))
})

const allVisibleSelected = computed(() => {
    if (!filteredJobs.value.length) {
        return false
    }

    return filteredJobs.value.every(job =>
        selected.value.includes(job.id)
    )
})

function cleanPostcode(postcode) {
    if (!postcode) {
        return ''
    }

    return String(postcode)
        .trim()
        .toUpperCase()
}

function isLikelyUkPostcode(postcode) {
    if (!postcode) {
        return false
    }

    const value = cleanPostcode(postcode)

    return /^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/.test(value)
}

function openGoogleMapsRoute() {
    if (!selectedJobs.value.length) {
        alert('Please select at least one collection or delivery.')
        return
    }

    if (!startPostcode.value) {
        alert('Please enter a starting postcode.')
        return
    }

    const validJobs = selectedJobs.value
        .filter(job => job.postcode)
        .map(job => ({
            ...job,
            postcode: cleanPostcode(job.postcode),
        }))

    if (!validJobs.length) {
        alert('None of the selected jobs have a postcode.')
        return
    }

    const postcodes = validJobs.map(job => job.postcode)

    const destination = postcodes[postcodes.length - 1]

    const waypoints = postcodes.slice(0, -1)

    const params = new URLSearchParams({
        api: '1',
        origin: cleanPostcode(startPostcode.value),
        destination: destination,
        travelmode: 'driving',
    })

    if (waypoints.length) {
        params.set('waypoints', waypoints.join('|'))
    }

    const url = `https://www.google.com/maps/dir/?${params.toString()}`

    window.open(url, '_blank')
}

function selectAllVisible() {
    const ids = filteredJobs.value.map(job => job.id)

    if (allVisibleSelected.value) {
        selected.value = selected.value.filter(
            id => !ids.includes(id)
        )

        return
    }

    selected.value = [
        ...new Set([
            ...selected.value,
            ...ids,
        ]),
    ]
}

function clearSelected() {
    selected.value = []
}

function selectCollections() {
    selected.value = props.jobs
        .filter(job => job.type === 'collection')
        .map(job => job.id)
}

function selectDeliveries() {
    selected.value = props.jobs
        .filter(job => job.type === 'delivery')
        .map(job => job.id)
}

function jobTypeLabel(job) {
    return job.type === 'collection'
        ? 'Collection'
        : 'Delivery'
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        Collections & Deliveries
                    </h2>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ jobs.length }} transport jobs waiting
                    </p>
                </div>

                <div
                    v-if="selected.length"
                    class="text-sm font-medium text-gray-600"
                >
                    {{ selected.length }} selected
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <!-- ROUTE BAR -->
            <div class="mb-5 rounded-xl border border-gray-200 bg-white p-4">
                <div class="flex flex-col gap-3 md:flex-row md:items-end">

                    <div class="flex-1">
                        <label
                            for="start-postcode"
                            class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500"
                        >
                            Route starts from
                        </label>

                        <input
                            id="start-postcode"
                            v-model="startPostcode"
                            type="text"
                            placeholder="NN10 9LL"
                            class="w-full rounded-lg border-gray-300 text-base uppercase focus:border-gray-900 focus:ring-gray-900"
                        >
                    </div>

                    <div class="md:w-56">
                        <button
                            type="button"
                            @click="openGoogleMapsRoute"
                            class="w-full rounded-lg bg-gray-900 px-5 py-2.5 font-medium text-white transition hover:bg-black"
                        >
                            Open Route
                            <span v-if="selected.length">
                                ({{ selected.length }})
                            </span>
                        </button>
                    </div>

                </div>
            </div>

            <!-- FILTERS -->
            <div class="mb-4 flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    @click="filter = 'all'"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="
                        filter === 'all'
                            ? 'bg-gray-900 text-white'
                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                    "
                >
                    All
                </button>

                <button
                    type="button"
                    @click="filter = 'collection'"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="
                        filter === 'collection'
                            ? 'bg-gray-900 text-white'
                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                    "
                >
                    Collections
                </button>

                <button
                    type="button"
                    @click="filter = 'delivery'"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition"
                    :class="
                        filter === 'delivery'
                            ? 'bg-gray-900 text-white'
                            : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                    "
                >
                    Deliveries
                </button>

                <div class="ml-auto flex flex-wrap gap-2">

                    <button
                        v-if="selected.length"
                        type="button"
                        @click="clearSelected"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Clear
                    </button>

                    <button
                        type="button"
                        @click="selectAllVisible"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        {{ allVisibleSelected ? 'Deselect All' : 'Select All' }}
                    </button>

                </div>
            </div>

            <!-- JOB CARDS -->
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">

                <div
                    v-for="job in filteredJobs"
                    :key="job.id"
                    class="rounded-xl border bg-white p-4 transition"
                    :class="
                        selected.includes(job.id)
                            ? 'border-gray-900 ring-1 ring-gray-900'
                            : 'border-gray-200 hover:border-gray-300'
                    "
                >
                    <div class="flex items-start gap-3">

                        <!-- CHECKBOX -->
                        <div class="pt-1">
                            <input
                                v-model="selected"
                                type="checkbox"
                                :value="job.id"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </div>

                        <!-- MAIN -->
                        <div class="min-w-0 flex-1">

                            <!-- TOP ROW -->
                            <div class="flex flex-wrap items-center gap-2">

                                <span
                                    class="rounded px-2 py-1 text-[11px] font-semibold uppercase tracking-wide"
                                    :class="
                                        job.type === 'collection'
                                            ? 'bg-orange-100 text-orange-800'
                                            : 'bg-blue-100 text-blue-800'
                                    "
                                >
                                    {{ jobTypeLabel(job) }}
                                </span>

                                <span class="text-sm font-bold text-gray-900">
                                    {{ job.reference }}
                                </span>

                            </div>

                            <!-- CUSTOMER -->
                            <div
                                v-if="job.name"
                                class="mt-2 truncate text-sm text-gray-500"
                            >
                                {{ job.name }}
                            </div>

                            <!-- POSTCODE -->
                            <div class="mt-2 flex items-center gap-2">

                                <div
                                    class="text-lg font-bold"
                                    :class="
                                        isLikelyUkPostcode(job.postcode)
                                            ? 'text-gray-900'
                                            : 'text-red-600'
                                    "
                                >
                                    {{ job.postcode || 'No postcode' }}
                                </div>

                                <span
                                    v-if="job.postcode && !isLikelyUkPostcode(job.postcode)"
                                    class="rounded bg-red-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-red-600"
                                >
                                    Check postcode
                                </span>

                            </div>

                            <!-- ADDRESS -->
                            <div
                                v-if="job.address && job.address !== job.postcode"
                                class="mt-1 line-clamp-1 text-xs text-gray-400"
                            >
                                {{ job.address }}
                            </div>

                            <!-- ITEMS -->
                            <div
                                v-if="job.items?.length"
                                class="mt-3 line-clamp-2 text-sm leading-5 text-gray-600"
                            >
                                <template
                                    v-for="(item, index) in job.items"
                                    :key="index"
                                >
                                    <span>
                                        {{ item.qty }} × {{ item.name }}
                                    </span>

                                    <span
                                        v-if="index < job.items.length - 1"
                                        class="mx-1 text-gray-300"
                                    >
                                        •
                                    </span>
                                </template>
                            </div>

                            <!-- ACTION -->
                            <div class="mt-4 border-t border-gray-100 pt-3">

                                <Link
                                    v-if="job.type === 'delivery'"
                                    :href="route('sales.edit', job.record_id)"
                                    class="text-sm font-medium text-blue-600 hover:underline"
                                >
                                    View Sale
                                </Link>

                                <Link
                                    v-else
                                    :href="route('purchases.edit', job.record_id)"
                                    class="text-sm font-medium text-blue-600 hover:underline"
                                >
                                    View Purchase
                                </Link>

                            </div>

                        </div>
                    </div>
                </div>

                <!-- EMPTY STATE -->
                <div
                    v-if="!filteredJobs.length"
                    class="col-span-full rounded-xl border border-gray-200 bg-white p-10 text-center text-gray-500"
                >
                    No collections or deliveries waiting.
                </div>

            </div>

        </div>
    </AuthenticatedLayout>
</template>