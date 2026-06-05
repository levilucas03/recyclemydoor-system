<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    listing: Object,
    products: Array,
    platforms: Array,
    selected_platform_ids: Array,
})

const currentProduct = props.listing.products?.[0] ?? null
const platformLinks = props.listing.platform_links ?? []

const form = useForm({
    title: props.listing.title ?? '',
    notes: props.listing.notes ?? '',
    product_id: currentProduct?.id ?? null,
    platform_ids: props.selected_platform_ids ?? [],
})

const submit = () => {
    form.put(route('listings.update', props.listing.id), {
        onSuccess: () => {
            router.visit(route('listings.edit', props.listing.id))
        },
    })
}

const republish = (linkId) => {
    router.post(route('listing-platform-links.republish', linkId), {}, {
        preserveScroll: true,
    })
}

const findWordPressProduct = (linkId) => {
    router.post(route('listing-platform-links.find-wordpress-product', linkId), {}, {
        preserveScroll: true,
    })
}

const toggleSyncImages = (linkId, value) => {
    router.put(route('listing-platform-links.sync-images', linkId), {
        sync_images: value,
    }, {
        preserveScroll: true,
    })
}

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Edit Listing</h2>

                <Link :href="route('listings.index')" class="text-sm text-blue-600">
                    Back to listings
                </Link>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-4xl mx-auto mt-10 bg-white shadow rounded-xl p-6">

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Listing Title</label>
                <input v-model="form.title" class="w-full border rounded p-2" />
                <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                    {{ form.errors.title }}
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Notes</label>
                <textarea v-model="form.notes" class="w-full border rounded p-2" rows="4"></textarea>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Product</label>
                <select v-model="form.product_id" class="w-full border rounded p-2">
                    <option :value="null">Select product</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                        {{ product.sku }} - {{ product.title }}
                    </option>
                </select>

                <div
                    v-if="currentProduct"
                    class="mt-3 rounded-lg border bg-gray-50 p-4 flex items-center justify-between gap-4"
                >
                    <div class="flex items-center gap-3 min-w-0">
                        <img
                            v-if="currentProduct.primary_image"
                            :src="`/storage/${currentProduct.primary_image.path}`"
                            class="w-14 h-14 object-cover rounded"
                        />

                        <div v-else class="w-14 h-14 bg-gray-200 rounded"></div>

                        <div class="min-w-0">
                            <p class="font-medium truncate">
                                {{ currentProduct.sku }} - {{ currentProduct.title }}
                            </p>

                            <p class="text-sm text-gray-500">
                                Click through to update product details, images, prices or attributes.
                            </p>
                        </div>
                    </div>

                    <Link
                        :href="route('products.edit', currentProduct.id)"
                        class="shrink-0 rounded bg-gray-900 px-3 py-2 text-sm text-white hover:bg-black"
                    >
                        Edit Product
                    </Link>
                </div>

                <button
                    v-for="link in platformLinks"
                    :key="link.id"
                    type="button"
                    @click="findWordPressProduct(link.id)"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black"
                >
                    Find {{ link.platform?.name }} Product
                </button>

                <div v-for="link in platformLinks" :key="link.id" class="mt-3 text-sm">
                    <span v-if="link.external_id" class="text-green-600">
                        Linked WordPress ID: {{ link.external_id }}
                    </span>

                    <span v-else class="text-gray-500">
                        Not linked yet
                    </span>
                </div>

                <div v-if="platformLinks.length" class="space-y-3">
                    <div
                        v-for="link in platformLinks"
                        :key="link.id"
                        class="border rounded-lg p-4"
                    >
                        <label class="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                :checked="link?.sync_images ?? true"
                                @change="toggleSyncImages(link.id, $event.target.checked)"
                            />
                            Sync photos
                        </label>
                    </div>
                </div>

            </div>

            

            <div class="mb-6">
                <label class="block text-sm font-medium mb-3">Platforms</label>

                <div v-if="platforms.length" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label
                        v-for="platform in platforms"
                        :key="platform.id"
                        class="border rounded-lg p-4 flex items-center gap-3 cursor-pointer hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            :value="platform.id"
                            v-model="form.platform_ids"
                        />

                        <div>
                            <div class="font-medium">{{ platform.name }}</div>
                            <div class="text-xs text-gray-500">{{ platform.slug }}</div>
                        </div>
                    </label>
                </div>

                <p v-else class="text-sm text-gray-500">
                    No active listing platforms. Configure WordPress first.
                </p>
            </div>

            <div class="flex gap-3 items-center">
                <div v-if="platformLinks.length" class="flex gap-3">
                    <button
                        v-for="link in platformLinks"
                        :key="link.id"
                        type="button"
                        @click="republish(link.id)"
                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                    >
                        Republish {{ link.platform?.name ?? 'Platform' }}
                    </button>
                </div>

                <button class="bg-blue-600 text-white px-4 py-2 rounded" :disabled="form.processing">
                    Save Listing
                </button>

                <Link :href="route('listings.index')" class="px-4 py-2 rounded border">
                    Cancel
                </Link>
            </div>

        </form>
    </AuthenticatedLayout>
</template>