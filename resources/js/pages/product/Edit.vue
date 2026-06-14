<script setup>
import { ref, computed, watch } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import draggable from 'vuedraggable'
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
    trafficDoors: Array,
    openings: Array,
    configurations: Array,
    categories: Array,
    parts: Array,
    statuses: Array,
    image: null,
    images: Array,
    allocateParts: Array,
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

    description: props.product.description ?? '',
    notes: props.product.notes,

    brand_id: props.product.brand_id,
    material_id: props.product.material_id,
    colour_id: props.product.colour_id,
    traffic_door_id: props.product.traffic_door_id,
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

const partForm = useForm({
    part_id: '',
    quantity_used: 1,
})

const submitPart = () => {
    partForm.post(route('products.parts.store', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            partForm.reset()
            partForm.quantity_used = 1
        },
    })
}

const removePart = (allocationId) => {
    if (confirm('Remove this part from product?')) {
        router.delete(route('products.parts.destroy', [
            props.product.id,
            allocationId,
        ]), {
            preserveScroll: true,
        })
    }
}

const refurbTotal = computed(() => {
    return props.product.part_allocations?.reduce((total, item) => {
        return total + Number(item.cost_allocated ?? 0)
    }, 0) ?? 0
})

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
    }).format(value ?? 0)
}



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

router.post(route('products.update', form.id), {
    ...form,
    _method: 'PUT',
}, {
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

// Gallery 

const galleryImages = ref([...(props.product.images ?? [])])
const orderChanged = ref(false)

const markGalleryChanged = () => {
    orderChanged.value = true
}

const saveImageOrder = () => {
    router.post(route('products.images.reorder', props.product.id), {
        images: galleryImages.value.map(image => image.id),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            orderChanged.value = false
        },
    })
}

const deleteImage = (imageId) => {
    if (!confirm('Delete this image?')) return

    router.delete(route('products.images.destroy', imageId), {
        preserveScroll: true,
        onSuccess: () => {
            galleryImages.value = galleryImages.value.filter(image => image.id !== imageId)
            orderChanged.value = true
        },
    })
}

watch(
    () => props.product.images,
    (newImages) => {
        galleryImages.value = [...(newImages ?? [])]
    },
    { deep: true }
)
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

                    <Link
                        v-if="product.listing"
                        :href="route('listings.edit', product.listing.id)"
                        class="text-purple-600 hover:underline"
                    >
                        View Listing
                    </Link>

                    <Link
                        v-else
                        :href="route('listings.create', { product_id: product.id })"
                        class="text-gray-500 hover:text-purple-600"
                    >
                        Create Listing
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

                        <div v-if="product.primary_image" class="mb-4">
                            <img
                                :src="`/storage/${product.primary_image.path}`"
                                class="w-32 h-32 object-cover rounded"
                            />
                        </div>
                    </div>

                    
                 

            

                   

                    

                    
                    <label>
                        SKU
                        <input v-model="form.sku" placeholder="SKU" class="w-full border p-2 mb-2" />
                    </label>
                    <label>
                        Title
                        <input v-model="form.title" placeholder="Title" class="w-full border p-2 mb-2" />
                    </label>
                    
                    <GenerateTitle
                        :material-id="form.material_id"
                        :colour-id="form.colour_id"
                        :category-ids="form.category_ids"
                        :materials="materials"
                        :colours="colours"
                        :categories="categories"
                        @generated="form.title = $event"
                    />
                    <label class="mb-2 mt-2 d-block">
                        <h2>Width</h2>
                        <input v-model="form.width" placeholder="Width" class="w-full border p-2 mb-2" />
                    </label>
                    
                    <label class="mb-2 d-block">
                        <h2>Height</h2>
                        <input v-model="form.height" placeholder="Height" class="w-full border p-2 mb-2" />
                    </label>
                     <label class="mb-2 d-block">
                        <h2>Depth</h2>
                        <input v-model="form.depth" placeholder="Depth" class="w-full border p-2" />
                     </label>
                    
                </div>

                <!-- ATTRIBUTES -->
                <div class="p-6 bg-white shadow rounded-xl">
                    <h2 class="mb-4 font-semibold">Attributes</h2>

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

                    <select v-model="form.brand_id" class="w-full border p-2 mb-2 mt-4">
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

                     <select v-model="form.traffic_door_id" class="w-full border p-2 mb-2">
                        <option :value="null">Traffic Door</option>
                        <option v-for="c in trafficDoors" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <select v-model="form.condition_id" class="w-full border p-2 mb-2">
                        <option :value="null">Condition</option>
                        <option v-for="c in conditions" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>

                    <select v-model="form.opening_id" class="w-full border p-2 mb-2">
                        <option :value="null">Opening</option>
                        <option v-for="o in openings" :key="o.id" :value="o.id">{{ o.name }}</option>
                    </select>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Gallery Images
                        </label>

                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            @change="e => form.images = Array.from(e.target.files)"
                            class="w-full border rounded p-2"
                        />
                    </div>

                    <div v-if="galleryImages.length" class="mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm text-gray-500">
                                Drag images to reorder. The first image will be the primary image.
                            </p>

                            <button
                                v-if="orderChanged"
                                type="button"
                                @click="saveImageOrder"
                                class="bg-green-600 text-white px-4 py-2 rounded text-sm"
                            >
                                Save Image Order
                            </button>
                        </div>

                        <draggable
                            v-model="galleryImages"
                            item-key="id"
                            class="grid grid-cols-2 md:grid-cols-4 gap-4"
                            @change="markGalleryChanged"
                        >
                            <template #item="{ element, index }">
                                <div class="relative border rounded-lg overflow-hidden bg-white shadow-sm">
                                    <img
                                        :src="`/storage/${element.path}`"
                                        class="w-full h-32 object-cover"
                                    />

                                    <span
                                        v-if="index === 0"
                                        class="absolute top-2 left-2 bg-green-600 text-white text-xs px-2 py-1 rounded"
                                    >
                                        Primary
                                    </span>

                                    <button
                                        type="button"
                                        @click="deleteImage(element.id)"
                                        class="absolute top-2 right-2 bg-white/90 text-red-600 rounded-full w-8 h-8 flex items-center justify-center shadow hover:bg-red-50"
                                        title="Delete image"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </draggable>
                    </div>

                    

                </div>

                
            </div>

             <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
                <h2 class="mb-4 font-semibold">Description</h2>
                <textarea
                    v-model="form.description"
                    class="w-full border rounded p-3 min-h-[200px]"
                    placeholder="Enter product description..."
                ></textarea>
                
            </div>

            

            <!-- Prices -->
            <div class="max-w-5xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
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

            <div class="bg-white rounded-xl shadow p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-semibold">Refurb Parts</h3>
            <p class="text-sm text-gray-500">
                Assign parts used to refurb this product.
            </p>
        </div>

        <div class="text-right">
            <div class="text-sm text-gray-500">Total Refurb Cost</div>
            <div class="text-xl font-bold">
                {{ formatCurrency(refurbTotal) }}
            </div>
        </div>
    </div>

    <form @submit.prevent="submitPart" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div>
            <label class="block text-sm font-medium mb-1">Part</label>

            <select v-model="partForm.part_id" class="w-full border rounded p-2">
                <option value="">Select part</option>

                <option
                    v-for="part in allocateParts"
                    :key="part.id"
                    :value="part.id"
                    :disabled="part.available_quantity <= 0"
                >
                    {{ part.name }}
                    -
                    {{ formatCurrency(part.unit_cost) }}
                    each
                    -
                    {{ part.available_quantity }} available
                </option>
            </select>

            <div v-if="partForm.errors.part_id" class="text-red-600 text-sm">
                {{ partForm.errors.part_id }}
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Quantity Used</label>

            <input
                v-model="partForm.quantity_used"
                type="number"
                min="1"
                class="w-full border rounded p-2"
            />

            <div v-if="partForm.errors.quantity_used" class="text-red-600 text-sm">
                {{ partForm.errors.quantity_used }}
            </div>
        </div>

        <div class="flex items-end">
            <button
                type="submit"
                class="w-full bg-black text-white rounded px-4 py-2"
                :disabled="partForm.processing"
            >
                Add Part Cost
            </button>
        </div>
    </form>

    <div class="border rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">Part</th>
                    <th class="p-3">Qty Used</th>
                    <th class="p-3">Unit Cost</th>
                    <th class="p-3">Total</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="allocation in product.part_allocations"
                    :key="allocation.id"
                    class="border-t"
                >
                    <td class="p-3 font-medium">
                        {{ allocation.part?.name }}
                    </td>

                    <td class="p-3">
                        {{ allocation.quantity_used }}
                    </td>

                    <td class="p-3">
                        {{ formatCurrency(allocation.unit_cost) }}
                    </td>

                    <td class="p-3 font-semibold">
                        {{ formatCurrency(allocation.cost_allocated) }}
                    </td>

                    <td class="p-3 text-right">
                        <button
                            type="button"
                            @click="removePart(allocation.id)"
                            class="text-red-600"
                        >
                            Remove
                        </button>
                    </td>
                </tr>

                <tr v-if="!product.part_allocations?.length">
                    <td colspan="5" class="p-4 text-center text-gray-500">
                        No refurb parts added yet.
                    </td>
                </tr>
            </tbody>
        </table>
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