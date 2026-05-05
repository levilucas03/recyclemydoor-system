<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ProductModal from '@/components/ProductModal.vue'

const showProductModal = ref(false)
const editingIndex = ref<number | null>(null)

const props = defineProps({
    categories: Array,
    materials: Array,
    colours: Array,
    statusOptions: Array,
    sources : Array
})

// --------------------
// FORM
// --------------------
const form = useForm({
    contact: {
        first_name: '',
        last_name: '',
        email: '',
        mobile: '',
        type: 'general_public',
    },
    purchase_date: new Date().toISOString().split('T')[0],
    address_1: '',
    address_2: '',
    town_city: '',
    postcode: '',
    products: [],
    contact_id: null,
    status: 'draft',
    notes: '',
    driver_notes: '',
    collection_notes: '',
    source_id: null,
})

// --------------------
// SEARCH
// --------------------
const search = ref('')
const results = ref([])
const selectedContact = ref<any | null>(null)

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

    form.address_1 = contact.address_1 || ''
    form.address_2 = contact.address_2 || ''
    form.town_city = contact.town_city || ''
    form.postcode = contact.postcode || ''
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

// --------------------
// PRODUCTS
// --------------------
function addProduct() {
    form.products.push({ name: '', price: '' })
}

function removeProduct(index: number) {
    form.products.splice(index, 1)
}

// --------------------
// TOTAL
// --------------------
function total() {
    return form.products
        .reduce((sum, p) => sum + (parseFloat(p.price as any) || 0), 0)
        .toFixed(2)
}

// --------------------
// SUBMIT
// --------------------
function submit() {
    if (form.contact_id) {
        form.contact = {} as any // remove new contact data
    }

    form.post(route('purchases.store'), {
        onError: (errors) => {
            console.log(errors)
        }
    })
}

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
        // ✏️ Update existing
        form.products[editingIndex.value] = product
    } else {
        // ➕ Create new
        form.products.push(product)
    }

    showProductModal.value = false
}

function deleteProduct(index: number) {
    form.products.splice(index, 1)
}

</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold">Create Purchase</h2>
    </template>

    <form @submit.prevent="submit" class="max-w-4xl mx-auto py-8 space-y-6">

        <!-- DEBUG -->
        <!-- <pre>{{ form.errors }}</pre> -->

        <!-- <pre>{{ statusOptions }}</pre> -->

        <!-- PURCHASE DETAILS -->
        <div class="bg-white p-6 rounded shadow">
            <h3 class="text-lg font-semibold mb-4">Purchase Details</h3>

            <label class="block text-sm mb-1">Purchase Date</label>
            <input
                type="date"
                v-model="form.purchase_date"
                class="border p-2 rounded w-full"
                :class="{ 'border-red-500': form.errors.purchase_date }"
            />
            <div v-if="form.errors.purchase_date" class="text-red-500 text-sm">
                {{ form.errors.purchase_date }}
            </div>

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

            <!-- SEARCH -->
            <input
                v-model="search"
                placeholder="Search contact..."
                class="border p-2 rounded w-full"
            />

            <!-- RESULTS -->
            <div v-if="results.length" class="border mt-1 rounded bg-white shadow">
                <div
                    v-for="contact in results"
                    :key="contact.id"
                    @click="selectContact(contact)"
                    class="p-2 hover:bg-gray-100 cursor-pointer"
                >
                    {{ contact.name }}
                </div>
            </div>

            <!-- SELECTED -->
            <div v-if="selectedContact" class="mt-4 p-3 bg-green-100 rounded flex justify-between items-center">
                <span>{{ selectedContact.name }}</span>
                <button type="button" @click="clearContact" class="text-red-600">✕</button>
            </div>

            <!-- NEW CONTACT -->
            <div v-if="!selectedContact" class="grid grid-cols-2 gap-4 mt-4">

                <div>
                    <input
                        v-model="form.contact.first_name"
                        placeholder="First Name *"
                        class="border p-2 rounded w-full"
                        :class="{ 'border-red-500': form.errors['contact.first_name'] }"
                    />
                    <div v-if="form.errors['contact.first_name']" class="text-red-500 text-sm">
                        {{ form.errors['contact.first_name'] }}
                    </div>
                </div>

                <div>
                    <input
                        v-model="form.contact.last_name"
                        placeholder="Last Name"
                        class="border p-2 rounded w-full"
                    />
                </div>

                <div>
                    <input
                        v-model="form.contact.email"
                        placeholder="Email"
                        class="border p-2 rounded w-full"
                        :class="{ 'border-red-500': form.errors['contact.email'] }"
                    />
                    <div v-if="form.errors['contact.email']" class="text-red-500 text-sm">
                        {{ form.errors['contact.email'] }}
                    </div>
                </div>

                <div>
                    <input
                        v-model="form.contact.mobile"
                        placeholder="Phone"
                        class="border p-2 rounded w-full"
                    />
                </div>

                <div class="col-span-2">
                    <select
                        v-model="form.contact.type"
                        class="border p-2 rounded w-full"
                        :class="{ 'border-red-500': form.errors['contact.type'] }"
                    >
                        <option value="general_public">General Public</option>
                        <option value="supplier">Supplier</option>
                        <option value="company">Company</option>
                    </select>
                    <div v-if="form.errors['contact.type']" class="text-red-500 text-sm">
                        {{ form.errors['contact.type'] }}
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
    <h3 class="text-lg font-semibold mb-4">Collection Address</h3>

    <div class="grid grid-cols-2 gap-4">

        <div class="col-span-2">
            <input
                v-model="form.address_1"
                placeholder="Address Line 1"
                class="border p-2 rounded w-full"
            />
        </div>

        <div class="col-span-2">
            <input
                v-model="form.address_2"
                placeholder="Address Line 2"
                class="border p-2 rounded w-full"
            />
        </div>

        <div>
            <input
                v-model="form.town_city"
                placeholder="Town / City"
                class="border p-2 rounded w-full"
            />
        </div>

        <div>
            <input
                v-model="form.postcode"
                placeholder="Postcode"
                class="border p-2 rounded w-full"
            />
        </div>

    </div>
</div>

        <!-- PRODUCTS -->
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between mb-4">
                <h3 class="text-lg font-semibold">Products</h3>
                <button 
                    type="button" 
                    @click="openCreateProduct"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
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

           <div v-for="(product, index) in form.products" :key="index">

                <div class="border p-3 rounded mb-2 flex justify-between items-center">

                    <div class="text-sm">
                        <span class="font-semibold">{{ product.title }}</span>

                        <span class="text-gray-500 mx-2">•</span>

                        <span class="text-gray-600">
                            {{ product.width }} x {{ product.height }} mm
                        </span>

                        <span class="text-gray-500 mx-2">•</span>

                        <span class="text-gray-600">
                            £{{ product.price }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <button @click="editProduct(index)" class="text-blue-600 text-sm">Edit</button>
                        <button @click="deleteProduct(index)" class="text-red-600 text-sm">Delete</button>
                    </div>

                </div>

            </div>

            <div v-if="form.errors.products" class="text-red-500 text-sm">
                {{ form.errors.products }}
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
        <div class="bg-white p-6 rounded shadow flex justify-between items-center">
            <strong>Total: £{{ total() }}</strong>

            <button 
                type="submit"
                :disabled="form.processing"
                class="bg-green-600 text-white px-6 py-2 rounded"
            >
                {{ form.processing ? 'Saving...' : 'Save Purchase' }}
            </button>
        </div>

    </form>
</AuthenticatedLayout>
</template>