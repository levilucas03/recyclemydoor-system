<script setup>
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

defineProps({
    platforms: Array,
})
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Listing Platforms</h2>
        </template>

        <div class="max-w-5xl mx-auto mt-10 bg-white shadow rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 text-left">Platform</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="platform in platforms" :key="platform.id" class="border-t">
                        <td class="p-3 font-medium">{{ platform.name }}</td>

                        <td class="p-3">
                            <span
                                class="px-2 py-1 rounded text-xs"
                                :class="platform.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                            >
                                {{ platform.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="p-3 text-right">
                            <Link
                                :href="route('listing-platforms.edit', platform.id)"
                                class="text-blue-600 hover:underline"
                            >
                                Configure
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>