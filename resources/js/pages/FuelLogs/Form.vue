<script setup>
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    vehicles: Array,
    fuelLog: Object
})

const form = useForm({
    vehicle_id: props.fuelLog?.vehicle_id ?? '',
    date: props.fuelLog?.date ?? new Date().toISOString().split('T')[0],
    mileage: props.fuelLog?.mileage ?? '',
    litres: props.fuelLog?.litres ?? '',
    cost: props.fuelLog?.cost ?? '',
    location: props.fuelLog?.location ?? '',
    notes: props.fuelLog?.notes ?? '',
})

const submit = () => {
    if (props.fuelLog) {
        form.put(route('fuel-logs.update', props.fuelLog.id))
    } else {
        form.post(route('fuel-logs.store'))
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold"> {{ fuelLog ? 'Edit Fuel Log' : 'Add Fuel Log' }}</h2>
        </template>

            <form @submit.prevent="submit" class="max-w-4xl mx-auto py-8 space-y-6">
                

                <div class="bg-white p-6 rounded shadow">

                    <!-- Vehicle -->
                    <div>
                        <label class="block mb-2">Vehicle</label>
                        <select v-model="form.vehicle_id" class="w-full border p-2 mb-2 rounded"
                            :class="{ 'border-red-500': form.errors.vehicle_id }">
                            <option value="">Select vehicle</option>
                            <option v-for="v in vehicles" :key="v.id" :value="v.id">
                                {{ v.name }} ({{ v.registration }})
                            </option>
                        </select>
                        <div v-if="form.errors.vehicle_id" class="text-red-500 text-sm">
                            {{ form.errors.vehicle_id }}
                        </div>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block mb-2">Date</label>
                        <input type="date" v-model="form.date" class="w-full border p-2 mb-2 rounded" :class="{ 'border-red-500': form.errors.date }" />
                        <div v-if="form.errors.date" class="text-red-500 text-sm">
                            {{ form.errors.date }}
                        </div>
                    </div>

                    <!-- Mileage -->
                    <div>
                        <label class="block mb-2">Mileage</label>
                        <input type="number" v-model="form.mileage" class="w-full border p-2 mb-2 rounded" :class="{ 'border-red-500': form.errors.mileage }" />
                        <div v-if="form.errors.mileage" class="text-red-500 text-sm">
                            {{ form.errors.mileage }}
                        </div>
                    </div>

                    <!-- Litres -->
                    <div>
                        <label class="block mb-2">Litres</label>
                        <input type="number" step="0.01" v-model="form.litres" class="w-full border p-2 mb-2 rounded" />
                    </div>

                    <!-- Cost -->
                    <div>
                        <label class="block mb-2">Total Cost (£)</label>
                        <input type="number" step="0.01" v-model="form.cost" class="w-full border p-2 mb-2 rounded" />
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block mb-2">Location</label>
                        <input type="text" v-model="form.location" class="w-full border p-2 mb-2 rounded" />
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block mb-2">Notes</label>
                        <textarea v-model="form.notes" class="w-full border p-2 mb-2 rounded"></textarea>
                    </div>

                    <!-- Submit -->
                    <div>
                        <button
                            type="submit"
                            class="bg-blue-600 text-white px-4 py-2 rounded"
                            :disabled="form.processing"
                        >
                            {{ fuelLog ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </form>

    </AuthenticatedLayout>
</template>