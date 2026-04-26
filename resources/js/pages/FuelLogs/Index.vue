<script setup>
import { ref, watch } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { useDateFormatter } from '@/composables/useDateFormatter'

const { formatPretty } = useDateFormatter()

const props = defineProps({
    fuelLogs: Object
})

const selected = ref([])
const selectAll = ref(false)

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.fuelLogs.data.map(p => p.id)
    } else {
        selected.value = []
    }
}

watch(selected, (val) => {
    if (val.length !== props.fuelLogs.data.length) {
        selectAll.value = false
    } else {
        selectAll.value = true
    }
})

function bulkDelete() {
    if (confirm(`Delete ${selected.value.length} fuel log(s)?`)) {
        router.post(route('fuel-logs.bulk-delete'), {
            ids: selected.value,
        }, {
            onFinish: () => selected.value = [],
        })
    }
}

</script>

<template>
    <AuthenticatedLayout>

         <template #header>
            <h2 class="text-xl font-semibold mb-2">Fuel Logs</h2>
            <Link href="/fuel-logs/create" class="bg-blue-600 text-white px-4 py-2 rounded">
                Add Fuel Log
            </Link>
        </template>

         <div class="py-12">
            <div class="max-w-7xl sm:px-6 lg:px-8 mx-auto">
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-4">

                        <h1 class="text-2xl font-bold mb-4">Fuel Logs</h1>

                        <!-- Bulk Action Button -->
                        <div v-if="selected.length > 0" class="relative">
                            <button @click="bulkDelete" class="bg-red-600 text-white px-3 py-1 rounded">
                                Delete Selected ({{ selected.length }})
                            </button>
                        </div>

                        <div class="bg-white shadow rounded-lg overflow-hidden">
                            <table class="w-full border text-sm">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="p-2 text-center"><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
                                        <th class="p-2 text-left">Date</th>
                                        <th class="p-2 text-left">Vehicle</th>
                                        <th class="p-2 text-left">Mileage</th>
                                        <th class="p-2 text-left">Litres</th>
                                        <th class="p-2 text-left">Cost (£)</th>
                                        <th class="p-2 text-left">£/L</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr v-for="log in fuelLogs.data" :key="log.id" class="border-t">
                                        <td class="p-2 text-center">
                                            <input
                                                type="checkbox"
                                                :value="log.id"
                                                v-model="selected"
                                            />
                                        </td>
                                        <td class="p-2">{{ log.date ? formatPretty(log.date) : '-' }}</td>
                                        <td class="p-2">
                                            {{ log.vehicle?.name }} ({{ log.vehicle?.registration }})
                                        </td>
                                        <td class="p-2">{{ log.mileage }}</td>
                                        <td class="p-2">{{ log.litres }}</td>
                                        <td class="p-2">£{{ log.cost }}</td>
                                        <td class="p-2">
                                            £{{ log.price_per_litre ?? (log.cost / log.litres).toFixed(2) }}
                                        </td>
                                        <td class="p-2">
                                            <Link :href="route('fuel-logs.edit', log.id)" class="text-blue-600 hover:underline">Edit</Link>
                                        </td>
                                    
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex gap-2">
                    <Link
                        v-for="link in fuelLogs.links"
                        :key="link.label"
                        :href="link.url || ''"
                        v-html="link.label"
                        class="px-3 py-1 border rounded"
                        :class="{ 'bg-gray-200': link.active }"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>