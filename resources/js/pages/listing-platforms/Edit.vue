<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    platform: Object,
})

const form = useForm({
    is_active: props.platform.is_active ?? false,
    config: {
        site_url: props.platform.config?.site_url ?? '',
        consumer_key: props.platform.config?.consumer_key ?? '',
        consumer_secret: props.platform.config?.consumer_secret ?? '',
        default_status: props.platform.config?.default_status ?? 'draft',
    },
})

const submit = () => {
    form.put(route('listing-platforms.update', props.platform.id))
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Configure {{ platform.name }}</h2>
        </template>

        <div
            v-if="$page.props.flash?.success"
            class="bg-green-100 text-green-700 p-3 rounded mb-4"
        >
            {{ $page.props.flash.success }}
        </div>

        <div
            v-if="$page.props.flash?.error"
            class="bg-red-100 text-red-700 p-3 rounded mb-4"
        >
            {{ $page.props.flash.error }}
        </div>

        <form @submit.prevent="submit" class="max-w-3xl mx-auto mt-10 bg-white shadow rounded-xl p-6">
            <div class="mb-4 flex items-center gap-2">
                <input id="active" type="checkbox" v-model="form.is_active" />
                <label for="active" class="font-medium">Active</label>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Site URL</label>
                <input v-model="form.config.site_url" class="w-full border rounded p-2" placeholder="https://example.co.uk" />
                <div v-if="form.errors['config.site_url']" class="text-red-500 text-sm mt-1">
                    {{ form.errors['config.site_url'] }}
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Consumer Key</label>
                <input v-model="form.config.consumer_key" class="w-full border rounded p-2" />
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Consumer Secret</label>
                <input v-model="form.config.consumer_secret" class="w-full border rounded p-2" type="password" />
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Default Status</label>
                <select v-model="form.config.default_status" class="w-full border rounded p-2">
                    <option value="draft">Draft</option>
                    <option value="publish">Publish</option>
                </select>
            </div>

            <button
                type="button"
                @click="router.post(route('listing-platforms.test', platform.id))"
                class="ml-2 bg-green-600 text-white px-4 py-2 rounded"
            >
                Test Connection
            </button>

            <button class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">
                Save Platform
            </button>
        </form>
    </AuthenticatedLayout>
</template>