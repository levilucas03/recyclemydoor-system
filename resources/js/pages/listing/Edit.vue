<script setup>
import { useForm, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    listing: Object,
    products: Array,
    platforms: Array,
    selected_platform_ids: Array,
})

const findingLinkId = ref(null)


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

const findWordPressProduct = (linkId) => {
    findingLinkId.value = linkId

    router.post(route('listing-platform-links.find-wordpress-product', linkId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({
                only: ['listing'],
            })
        },
        onFinish: () => {
            findingLinkId.value = null
        },
    })
}

const republish = (linkId) => {
    router.post(route('listing-platform-links.republish', linkId), {}, {
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

        

        <form @submit.prevent="submit" class="">

            <div class="max-w-7xl mx-auto mt-10 grid grid-cols-1 lg:grid-cols-4 gap-6">

                <div class="lg:col-span-3">

                    <!-- Listing Details -->
                    <div class="space-y-6  bg-white shadow rounded-xl p-5 bg-white">
                        <h3 class="text-lg font-semibold">Listing Details</h3>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Listing Title</label>
                            <input v-model="form.title" class="w-full border rounded p-2" />
                            <div v-if="form.errors.title" class="text-red-500 text-sm mt-1">
                                {{ form.errors.title }}
                            </div>
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
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Notes</label>
                            <textarea v-model="form.notes" class="w-full border rounded p-2" rows="4"></textarea>
                        </div>

                        <div class="mt-8 flex gap-3 items-center border-t pt-6">
                            <button
                                class="bg-blue-600 text-white px-4 py-2 rounded"
                                :disabled="form.processing"
                            >
                                Save Listing
                            </button>

                            <Link :href="route('listings.index')" class="px-4 py-2 rounded border">
                                Cancel
                            </Link>
                        </div>
                    </div>
                </div>
                <aside class="lg:col-span-1 space-y-4">

                    <!-- Platform Settings -->
                    <div class="bg-white shadow rounded-xl p-5">
                        <h3 class="text-lg font-semibold mb-4">Platforms</h3>

                        <div
                            v-for="link in platformLinks"
                            :key="link.id"
                            class="rounded-xl border bg-white p-5 mb-4"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="font-semibold">
                                        {{ link.platform?.name }}
                                    </h4>

                                    <p class="text-sm text-gray-500">
                                        {{ link.external_id ? `Linked ID: ${link.external_id}` : 'Not linked yet' }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold"
                                    :class="link.external_id
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-gray-100 text-gray-500'"
                                >
                                    {{ link.external_id ? 'Live / Linked' : 'Draft / Not linked' }}
                                </span>
                            </div>

                            <!-- WordPress Options -->
                            <div v-if="link.platform?.slug === 'wordpress'" class="space-y-4">

                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        :checked="link?.sync_images ?? true"
                                        @change="toggleSyncImages(link.id, $event.target.checked)"
                                    />
                                    Sync photos
                                </label>

                                <div class="flex gap-3">
                                    <button
                                        type="button"
                                        @click="findWordPressProduct(link.id)"
                                        :disabled="findingLinkId === link.id"
                                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black disabled:opacity-60"
                                    >
                                        {{ findingLinkId === link.id ? 'Finding...' : 'Find' }}
                                    </button>

                                    <button
                                        type="button"
                                        @click="republish(link.id)"
                                        class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700"
                                    >
                                        Push 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div> 

        </form>
    </AuthenticatedLayout>
</template>