<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ProductModal from '@/components/ProductModal.vue'
import ImagePreview from '@/components/ImagePreview.vue'

// --------------------
// PROPS
// --------------------
const props = defineProps({
    purchase: Object,
    categories: Array,
    materials: Array,
    colours: Array,
    statusOptions: Array,
    sources: Array,
})

// --------------------
// MAP PRODUCTS
// --------------------
const mappedProducts = (props.purchase?.products || []).map((product: any) => ({
    id: product.id,
    title: product.title ?? '',
    sku: product.sku ?? '',
    width: product.width ?? '',
    height: product.height ?? '',
    primary_image: product.primary_image ?? '',

    category_ids: product.categories?.map((c: any) => c.id) || [],
    material_id: product.attributes?.find((a: any) => a.group?.slug === 'material')?.id || null,
    colour_id: product.attributes?.find((a: any) => a.group?.slug === 'colour')?.id || null,

    price: product.purchase_price
        ?? product.prices?.find((p: any) => p.type === 'purchase')?.price
        ?? '',
}))

// --------------------
// FORM
// --------------------
const form = useForm({
    contact: {
        first_name: props.purchase?.contact?.first_name || '',
        last_name: props.purchase?.contact?.last_name || '',
        email: props.purchase?.contact?.email || '',
        phone: props.purchase?.contact?.phone || '',
        type: props.purchase?.contact?.type || 'general_public',
    },

    contact_id: props.purchase?.contact?.id || null,

    purchase_date: props.purchase?.purchase_date || '',
    status: props.purchase?.status || '',
    notes: props.purchase?.notes || '',
    collection_notes: props.purchase?.collection_notes || '',
    driver_notes: props.purchase?.driver_notes || '',
    source_id: props.purchase?.source_id ?? null,

    address_1: props.purchase?.collection_address_1 || '',
    address_2: props.purchase?.collection_address_2 || '',
    town_city: props.purchase?.collection_town_city || '',
    postcode: props.purchase?.collection_postcode || '',

    products: mappedProducts,
})

// --------------------
// CONTACT SEARCH
// --------------------
const search = ref(props.purchase?.contact?.name || '')
const results = ref([])
const selectedContact = ref<any | null>(props.purchase?.contact || null)

let debounceTimer: any = null

watch(search, (value) => {
    if (selectedContact.value) return

    clearTimeout(debounceTimer)

    if (!value || value.length < 2) {
        results.value = []
        return
    }

    debounceTimer = setTimeout(async () => {
        const res = await axios.get(route('contacts.search'), {
            params: { q: value }
        })
        results.value = res.data
    }, 300)
})

function selectContact(contact: any) {
    selectedContact.value = contact
    form.contact_id = contact.id
    search.value = contact.name
    results.value = []

    form.address_1 = contact.deliver_address_1 || ''
    form.address_2 = contact.deliver_address_2 || ''
    form.town_city = contact.deliver_town || ''
    form.postcode = contact.deliver_postcode || ''
}

function clearContact() {
    selectedContact.value = null
    form.contact_id = null
    search.value = ''

    form.address_1 = ''
    form.address_2 = ''
    form.town_city = ''
    form.postcode = ''
}


const previewImage = ref<string | null>(null)
const showPreview = ref(false)

const openPreview = (src: string) => {
    previewImage.value = src
    showPreview.value = true
}

// --------------------
// PRODUCTS
// --------------------
const showProductModal = ref(false)
const editingIndex = ref<number | null>(null)

function openCreateProduct() {
    editingIndex.value = null
    showProductModal.value = true
}

function editProduct(index: number) {
    editingIndex.value = index
    showProductModal.value = true
}

function saveProduct(product: any) {
    if (editingIndex.value !== null) {
        form.products.splice(editingIndex.value, 1, {
            ...form.products[editingIndex.value],
            ...product,
        })
    } else {
        form.products.push(product)
    }

    showProductModal.value = false
}

function deleteProduct(index: number) {
    form.products.splice(index, 1)
}

// --------------------
// HELPERS
// --------------------
function getMaterialName(id: number | null) {
    return props.materials.find((m: any) => m.id === id)?.name || ''
}

function getColourName(id: number | null) {
    return props.colours.find((c: any) => c.id === id)?.name || ''
}

function getCategoryName(product: any) {
    const allChildren = props.categories.flatMap((p: any) => p.children || [])
    const match = allChildren.find((c: any) =>
        product.category_ids?.includes(c.id)
    )
    return match?.name || ''
}

// --------------------
// TOTAL
// --------------------
function total() {
    return form.products
        .reduce((sum, p: any) => sum + (parseFloat(p.price) || 0), 0)
        .toFixed(2)
}

console.log(form.products)

// --------------------
// SUBMIT
// --------------------
function submit() {
    if (form.products.length === 0) {
        alert('Please add at least one product')
        return
    }

    if (form.contact_id) {
        form.contact = {} as any
    }

    form.put(route('purchases.update', props.purchase.id))
}
</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold">Edit Purchase</h2>
    </template>

    <!-- <pre>{{ form.products }}</pre> -->

    <form @submit.prevent="submit" class="max-w-4xl mx-auto py-8 space-y-6">

        <!-- PURCHASE DETAILS -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Purchase Details</h3>

            <input type="date" v-model="form.purchase_date" class="border p-2 rounded w-full" />

             <select v-model="form.status" class="border rounded p-2 w-full">
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>

            <select v-model="form.source_id" class="border rounded p-2 w-full">
                <option :value="null">Select source</option>

                <option
                    v-for="source in sources"
                    :key="source.id"
                    :value="source.id"
                >
                    {{ source.name }}
                </option>
            </select>
        </div>

        <!-- CONTACT -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Contact</h3>

            <input v-model="search" placeholder="Search contact..." class="border p-2 rounded w-full" />

            <div v-if="results.length" class="border mt-1 rounded bg-white shadow">
                <div v-for="contact in results" :key="contact.id" @click="selectContact(contact)" class="p-2 hover:bg-gray-100 cursor-pointer">
                    {{ contact.name }}
                </div>
            </div>

            <div v-if="selectedContact" class="mt-4 p-3 bg-green-100 rounded flex justify-between">
                <span>{{ selectedContact.name }}</span>
                <button type="button" @click="clearContact">✕</button>
            </div>
        </div>

        <!-- ADDRESS -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Collection Address</h3>

            <input v-model="form.address_1" placeholder="Address 1" class="border p-2 rounded w-full mb-2" />
            <input v-model="form.address_2" placeholder="Address 2" class="border p-2 rounded w-full mb-2" />

            <div class="grid grid-cols-2 gap-4">
                <input v-model="form.town_city" placeholder="Town / City" class="border p-2 rounded" />
                <input v-model="form.postcode" placeholder="Postcode" class="border p-2 rounded" />
            </div>
        </div>

        <!-- PRODUCTS -->
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between mb-4">
                <h3 class="text-lg font-semibold">Products</h3>
                <button type="button" @click="openCreateProduct" class="bg-blue-600 text-white px-3 py-1 rounded">
                    + Add Product
                </button>
            </div>

            <ProductModal
                v-if="showProductModal"
                :key="editingIndex !== null ? editingIndex : 'new'"
                :product="editingIndex !== null ? form.products[editingIndex] : null"
                :categories="categories"
                :materials="materials"
                :colours="colours"
                @close="showProductModal = false"
                @save="saveProduct"
            />


            <div v-for="(product, index) in form.products" :key="index"
                class="border p-3 rounded mb-2 flex justify-between items-center">

                <div class="text-sm flex items-center">
                    <span class="mx-2 ">
                        <img
                            v-if="product.primary_image"
                            :src="`/storage/${product.primary_image.path}`"
                            class="w-14 h-14 object-cover rounded cursor-pointer"
                            @click.stop="openPreview(`/storage/${product.primary_image.path}`)"
                        />
                        <div v-else class="w-14 h-14 bg-gray-200 rounded"></div>
                    </span>

                    <span class="font-semibold">
                        {{ product.sku }}
                    </span>

                    <span class="mx-2">•</span>
                    
                    <span class="font-semibold">
                        {{ product.title }}
                        
                    </span>

                    <span class="mx-2">•</span>

                    {{ product.width }} x {{ product.height }} mm

                    <span class="mx-2">•</span>

                    <span class="font-semibold text-green-600">£{{ product.price }}</span>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="router.visit(route('products.edit', product.id))"
                        class="text-blue-600"
                    >
                        View
                    </button>
                    <button type="button" @click="editProduct(index)" class="text-blue-600">
                        Edit
                    </button>
                    <button type="button" @click="deleteProduct(index)" class="text-red-600">
                        Delete
                    </button>
                </div>
            </div>
        </div>

         <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Notes</h3>

            <label class="block text-sm mb-1">General Notes</label>
            <textarea
                v-model="form.notes"
                class="border p-2 rounded w-full"
                :class="{ 'border-red-500': form.errors.notes }">
            </textarea>
            <div v-if="form.errors.notes" class="text-red-500 text-sm">
                {{ form.errors.notes }}
            </div>

            <label class="block text-sm mb-1">Collection Notes</label>
            <textarea
                v-model="form.collection_notes"
                class="border p-2 rounded w-full"
                :class="{ 'border-red-500': form.errors.collection_notes }">
            </textarea>
            <div v-if="form.errors.collection_notes" class="text-red-500 text-sm">
                {{ form.errors.collection_notes }}
            </div>

            <label class="block text-sm mb-1">Driver Notes</label>
            <textarea
                v-model="form.driver_notes"
                class="border p-2 rounded w-full"
                :class="{ 'border-red-500': form.errors.driver_notes }">
            </textarea>
            <div v-if="form.errors.driver_notes" class="text-red-500 text-sm">
                {{ form.errors.driver_notes }}
            </div>

           
        </div>

        <!-- TOTAL -->
        <div class="bg-white p-6 rounded shadow flex justify-between">
            <strong>Total: £{{ total() }}</strong>

            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">
                Update Purchase
            </button>
        </div>

        

    </form>

    <button
    @click="router.post(route('purchases.xero', purchase.id))"
    class="bg-indigo-600 text-white px-3 py-1 rounded"
>
    Send to Xero
</button>
</AuthenticatedLayout>

 <ImagePreview
        :src="previewImage"
        :show="showPreview"
        @close="showPreview = false"
    />
    
</template>