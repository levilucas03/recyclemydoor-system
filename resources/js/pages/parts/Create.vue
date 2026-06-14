<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    name: '',
    sku: '',
    notes: '',
    total_quantity: 1,
    total_cost: 0,
    purchased_at: '',
})

const submit = () => {
    form.post(route('parts.store'))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Add Part</h2>
        </template>

        <form
            @submit.prevent="submit"
            class="max-w-3xl mx-auto mt-8 bg-white rounded-xl shadow p-6 space-y-4"
        >
            <div>
                <label class="block text-sm font-medium">Name</label>
                <input v-model="form.name" class="w-full border rounded p-2" />
                <div v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium">SKU</label>
                <input v-model="form.sku" class="w-full border rounded p-2" />
            </div>

            <div>
                <label class="block text-sm font-medium">Quantity Bought</label>
                <input type="number" v-model="form.total_quantity" class="w-full border rounded p-2" />
            </div>

            <div>
                <label class="block text-sm font-medium">Total Cost</label>
                <input type="number" step="0.01" v-model="form.total_cost" class="w-full border rounded p-2" />
            </div>

            <div>
                <label class="block text-sm font-medium">Purchased At</label>
                <input type="date" v-model="form.purchased_at" class="w-full border rounded p-2" />
            </div>

            <div>
                <label class="block text-sm font-medium">Notes</label>
                <textarea v-model="form.notes" class="w-full border rounded p-2"></textarea>
            </div>

            <div class="flex justify-between">
                <Link :href="route('parts.index')" class="px-4 py-2 border rounded">
                    Cancel
                </Link>

                <button class="px-4 py-2 bg-black text-white rounded">
                    Save Part
                </button>
            </div>
        </form>
    </AuthenticatedLayout>
</template>