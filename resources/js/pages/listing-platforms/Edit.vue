<script setup>
import { useForm, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

const props = defineProps({
    platform: Object,
    attributeGroups: Array,
    categories: Array,

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

const selectedGroupId = ref(props.attributeGroups?.[0]?.id ?? null)

const selectedGroup = computed(() => {
    return props.attributeGroups.find(group => group.id === selectedGroupId.value)
})

const mappingForm = useForm({
    attributes: [],
})

watch(selectedGroup, (group) => {
    mappingForm.attributes = (group?.attributes ?? []).map(attribute => ({
        id: attribute.id,
        name: attribute.name,
        wordpress_term_id: attribute.wordpress_term_id,
        wordpress_slug: attribute.wordpress_slug,
        wordpress_taxonomy: attribute.wordpress_taxonomy,
        wordpress_attribute_id: attribute.wordpress_attribute_id,
    }))
}, { immediate: true })

const saveWordPressMappings = () => {
    mappingForm.put(route('listing-platforms.wordpress-attributes.update', props.platform.id), {
        preserveScroll: true,
    })
}

const syncSelectedGroup = () => {
    router.post(
        route('listing-platforms.attribute-groups.sync-wordpress', {
            listingPlatform: props.platform.id,
            attributeGroup: selectedGroupId.value,
        }),
        {},
        { preserveScroll: true }
    )
}

const categoryMappingForm = useForm({
    categories: props.categories.map(category => ({
        id: category.id,
        name: category.name,
        wordpress_term_id: category.wordpress_term_id,
        wordpress_slug: category.wordpress_slug,
    })),
})

const saveCategoryWordPressMappings = () => {
    categoryMappingForm.put(
        route('listing-platforms.wordpress-categories.update', props.platform.id),
        {
            preserveScroll: true,
        }
    )
}

const syncCategories = () => {
    router.post(
        route('listing-platforms.sync-categories', props.platform.id),
        {},
        { preserveScroll: true }
    )
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

            <button
                type="button"
                @click="syncSelectedGroup"
                class="bg-green-600 text-white px-4 py-2 rounded"
            >
                Sync Selected Group to WordPress
            </button>

            <button
                type="button"
                @click="syncCategories"
                class="bg-purple-600 text-white px-4 py-2 rounded"
            >
                Sync Categories to WordPress
            </button>
        </form>

        <div class="bg-white shadow rounded-xl p-6">

            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-semibold mb-4">WordPress Attribute Mapping</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Attribute Group</label>

                    <select v-model="selectedGroupId" class="w-full border rounded p-2">
                        <option
                            v-for="group in attributeGroups"
                            :key="group.id"
                            :value="group.id"
                        >
                            {{ group.name }}
                        </option>
                    </select>
                </div>

                <div v-if="mappingForm.attributes.length" class="overflow-x-auto">
                    <table class="w-full text-sm border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="p-2 text-left">Attribute</th>
                                <th class="p-2 text-left">WP Term ID</th>
                                <th class="p-2 text-left">WP Slug</th>
                                <th class="p-2 text-left">WP Taxonomy</th>
                                <th class="p-2 text-left">WP Attribute ID</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="attribute in mappingForm.attributes"
                                :key="attribute.id"
                                class="border-t"
                            >
                                <td class="p-2 font-medium">
                                    {{ attribute.name }}
                                </td>

                                <td class="p-2">
                                    <input
                                        v-model="attribute.wordpress_term_id"
                                        class="w-full border rounded p-2"
                                        placeholder="23"
                                    />
                                </td>

                                <td class="p-2">
                                    <input
                                        v-model="attribute.wordpress_slug"
                                        class="w-full border rounded p-2"
                                        placeholder="bi-fold-doors"
                                    />
                                </td>

                                <td class="p-2">
                                    <input
                                        v-model="attribute.wordpress_taxonomy"
                                        class="w-full border rounded p-2"
                                        placeholder="product_cat"
                                    />
                                </td>

                                <td class="p-2">
                                    <input
                                        v-model="attribute.wordpress_attribute_id"
                                        class="w-full border rounded p-2"
                                        placeholder="Only for pa_ attributes"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button
                    type="button"
                    @click="saveWordPressMappings"
                    class="mt-4 bg-green-600 text-white px-4 py-2 rounded"
                    :disabled="mappingForm.processing"
                >
                    Save WordPress Mappings
                </button>
            </div>

            <div class="mt-8 border-t pt-6">
    <h3 class="text-lg font-semibold mb-4">WordPress Category Mapping</h3>

    <div v-if="categoryMappingForm.categories.length" class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 text-left">Category</th>
                    <th class="p-2 text-left">WP Term ID</th>
                    <th class="p-2 text-left">WP Slug</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="category in categoryMappingForm.categories"
                    :key="category.id"
                    class="border-t"
                >
                    <td class="p-2 font-medium">
                        {{ category.name }}
                    </td>

                    <td class="p-2">
                        <input
                            v-model="category.wordpress_term_id"
                            class="w-full border rounded p-2"
                            placeholder="23"
                        />
                    </td>

                    <td class="p-2">
                        <input
                            v-model="category.wordpress_slug"
                            class="w-full border rounded p-2"
                            placeholder="doors"
                        />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <button
        type="button"
        @click="saveCategoryWordPressMappings"
        class="mt-4 bg-purple-600 text-white px-4 py-2 rounded"
        :disabled="categoryMappingForm.processing"
    >
        Save Category Mappings
    </button>
</div>

        </div>
    </AuthenticatedLayout>
</template>