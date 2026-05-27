<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import GenerateTitle from '@/components/GenerateTitle.vue'
import EbayHtmlBuilder from '@/components/EbayHtmlBuilder.vue'

const getPrice = (type) => {
    return props.product.prices.find(p => p.type === type)?.price ?? ''
}

// ---------------------
// PROPS
// ---------------------
const props = defineProps({
    product: Object,
    brands: Array,
    materials: Array,
    colours: Array,
    conditions: Array,
    openings: Array,
    configurations: Array,
    categories: Array,
    parts: Array,
    statuses: Array,
    image: null,
})

// ---------------------
// FORM
// ---------------------
const form = useForm({
    id: props.product.id,
    sku: props.product.sku,
    width: props.product.width,
    height: props.product.height,
    depth: props.product.depth,
    title: props.product.title,
    status: props.product.status ?? 'pending',

    brand_id: props.product.brand_id,
    material_id: props.product.material_id,
    colour_id: props.product.colour_id,
    condition_id: props.product.condition_id,
    opening_id: props.product.opening_id,
    configuration_id: props.product.configuration_id,
    category_ids: props.product.categories?.map(c => c.id) || [],


    purchase_price: getPrice('purchase'),
    website_price: getPrice('website'),
    sold_price: getPrice('sold'),
    initial_price: getPrice('initial'),

    part_ids: props.product.attributes
        ?.filter(attr => attr.group?.slug === 'parts')
        .map(attr => attr.id) || [],
})



// console.log(props.product.purchase_price);

// ---------------------
// CONFIGURATION
// ---------------------
const selectedConfigParent = ref(null)

const configParents = computed(() => props.configurations)

const configChildren = computed(() => {
    return configParents.value.find(p => p.id === selectedConfigParent.value)?.children || []
})

// Auto select config parent
watch(
    () => form.configuration_id,
    () => {
        const parent = props.configurations.find(p =>
            p.children?.some(c => c.id === form.configuration_id)
        )
        if (parent) selectedConfigParent.value = parent.id
    },
    { immediate: true }
)

// ---------------------
// CATEGORIES
// ---------------------
const selectedCategoryParent = ref(null)

const categoryParents = computed(() => props.categories)

const categoryChildren = computed(() => {
    return categoryParents.value.find(p => p.id === selectedCategoryParent.value)?.children || []
})

// 🔥 FIX: Auto select parent based on saved children
watch(
    () => props.product.categories,
    () => {
        if (!props.product.categories?.length) return

        const parent = props.categories.find(p =>
            p.children?.some(child =>
                props.product.categories.some(c => c.id === child.id)
            )
        )

        if (parent) {
            selectedCategoryParent.value = parent.id
        }
    },
    { immediate: true }
)

// ---------------------
// PARTS
// ---------------------
const selectedPartParent = ref(null)

const partChildren = computed(() => {
    return props.parts.find(p => p.id === selectedPartParent.value)?.children || []
})
watch(
    () => props.product.attributes,
    () => {
        if (!props.product.attributes?.length) return

        const selectedPartIds = props.product.attributes
            .filter(attr => attr.group?.slug === 'parts')
            .map(attr => attr.id)

        const parent = props.parts.find(p =>
            p.children?.some(child => selectedPartIds.includes(child.id))
        )

        if (parent) {
            selectedPartParent.value = parent.id
        }
    },
    { immediate: true }
)

form._method = 'PUT'

router.post(route('products.update', form.id), form, {
    forceFormData: true,
})

// ---------------------
// AUTOSAVE
// ---------------------
const autoSave = debounce(() => {
    router.post(route('products.update', { product: form.id }), form, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    })
}, 800)

watch(() => ({ ...form }), autoSave, { deep: true })

function manualSave() {
    router.put(route('products.update', { product: form.id }), form, {
        onSuccess: () => {
            router.visit(route('products.index'))
        }
    })
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold">Edit {{ form.title }}</h2>
            <span class="inline-block px-2 py-1 text-xs rounded bg-gray-100">
                {{ statuses.find(s => s.value === form.status)?.label }}
            </span>
        </template>

        <form @submit.prevent="manualSave">

            <!-- <pre>{{ form }}</pre> -->

            <!-- GENERAL + ATTRIBUTES -->
            <div class="max-w-5xl mx-auto mt-10 grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- GENERAL -->
                <div class="p-6 bg-white shadow rounded-xl">
                    <h2 class="mb-4 font-semibold">General</h2>

                    <Link
                        v-if="product.purchase"
                        :href="route('purchases.edit', product.purchase.id)"
                        class="text-indigo-600 hover:underline"
                    >
                        View Purchase
                    </Link>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>

                        <select
                            v-model="form.status"
                            class="w-full border rounded p-2"
                        >
                            <option v-for="s in statuses" :key="s.value" :value="s.value">
                                {{ s.label }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Product Image
                        </label>

                        <input
                            type="file"
                            @change="e => form.image = e.target.files[0]"
                            class="w-full border rounded p-2"
                        />
                    </div>

                    <div v-if="product.primary_image" class="mb-4">
                        <img
                            :src="`/storage/${product.primary_image.path}`"
                            class="w-32 h-32 object-cover rounded"
                        />
                    </div>

                    

                    <input v-model="form.sku" placeholder="SKU" class="w-full border p-2 mb-2" />
                    <input v-model="form.title" placeholder="Title" class="w-full border p-2 mb-2" />
                    <GenerateTitle
                        :material-id="form.material_id"
                        :colour-id="form.colour_id"
                        :category-ids="form.category_ids"
                        :materials="materials"
                        :colours="colours"
                        :categories="categories"
                        @generated="form.title = $event"
                    />
                    <input v-model="form.width" placeholder="Width" class="w-full border p-2 mb-2" />
                    <input v-model="form.height" placeholder="Height" class="w-full border p-2 mb-2" />
                    <input v-model="form.depth" placeholder="Depth" class="w-full border p-2" />
                </div>

                <!-- ATTRIBUTES -->
                <div class="p-6 bg-white shadow rounded-xl">
                    <h2 class="mb-4 font-semibold">Attributes</h2>

                    <select v-model="form.brand_id" class="w-full border p-2 mb-2">
                        <option :value="null">Brand</option>
                        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>

                    <select v-model="form.material_id" class="w-full border p-2 mb-2">
                        <option :value="null">Material</option>
                        <option v-for="m in materials" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>

                    <select v-model="form.colour_id" class="w-full border p-2 mb-2">
                        <option :value="null">Colour</option>
                        <option v-for="c in colours" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <select v-model="form.condition_id" class="w-full border p-2 mb-2">
                        <option :value="null">Condition</option>
                        <option v-for="c in conditions" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <select v-model="form.opening_id" class="w-full border p-2 mb-2">
                        <option :value="null">Opening</option>
                        <option v-for="o in openings" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>

                    <h2 class="mb-4 font-semibold mt-2 ">Prices</h2>

                    <div class="grid grid-cols-2 gap-4 ">
    
                        <label>
                            Purchase
                            <input v-model="form.purchase_price" class="border p-2 w-full" />
                        </label>
                        <label>
                            Inital 
                            <input v-model="form.initial_price" class="border p-2 w-full" />
                        </label>
                        <label>
                            Website 
                            <input v-model="form.website_price" class="border p-2 w-full" />
                        </label>
                        <label>
                            Sold Price
                            <input v-model="form.sold_price" class="border p-2 w-full" />
                        </label>


                       
                        

                    </div>

                </div>

                
            </div>

            <!-- CONFIGURATION -->
            <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
                <h2 class="mb-4 font-semibold">Configuration</h2>

                <select v-model="selectedConfigParent" class="w-full border p-2 mb-4">
                    <option :value="null">Select Type</option>
                    <option v-for="p in configParents" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </select>

                <div v-if="configChildren.length" class="flex flex-wrap gap-2">
                    <label v-for="child in configChildren" :key="child.id" class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer">
                        <input type="radio" :value="child.id" v-model="form.configuration_id" />
                        {{ child.name }}
                    </label>
                </div>
            </div>

            <!-- CATEGORIES -->
            <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
                <h2 class="mb-4 font-semibold">Categories</h2>

                <select v-model="selectedCategoryParent" class="w-full border p-2 mb-4">
                    <option :value="null">Select Category</option>
                    <option v-for="p in categoryParents" :key="p.id" :value="p.id">
                        {{ p.name }}
                    </option>
                </select>

                <div v-if="categoryChildren.length" class="flex flex-wrap gap-2">
                    <label v-for="child in categoryChildren" :key="child.id" class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer">
                        <input type="checkbox" :value="child.id" v-model="form.category_ids" />
                        {{ child.name }}
                    </label>
                </div>
            </div>
            <!-- PARTS -->
            <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
                <h2 class="mb-4 font-semibold text-lg">Parts</h2>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">

                    <label
                        v-for="part in parts"
                        :key="part.id"
                        class="flex items-center gap-2 border px-3 py-2 rounded cursor-pointer hover:bg-gray-50"
                    >
                        <input
                            type="checkbox"
                            :value="part.id"
                            v-model="form.part_ids"
                        />
                        {{ part.name }}
                    </label>

                </div>

                <!-- optional validation -->
                <div v-if="form.errors.part_ids" class="text-red-500 text-sm mt-2">
                    {{ form.errors.part_ids }}
                </div>
            </div>
            

            <!-- SAVE -->
            <div class="max-w-5xl mx-auto mt-6">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">
                    Save & Exit
                </button>
            </div>

        </form>

        <EbayHtmlBuilder :product="product" />
        
    </AuthenticatedLayout>
</template>