<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import axios from 'axios'

import ProductSearchModal from '@/Components/ProductSearchModal.vue'

const showProductModal = ref(false)

const props = defineProps({
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
    contact_id: null,
    status: 'draft',
    address_1: '',
    address_2: '',
    town_city: '',
    postcode: '',
    source_id: null,

    invoice_date:  new Date().toISOString().split('T')[0],
    notes: '',
    items: []
})

// --------------------
// CONTACT SEARCH
// --------------------
const search = ref('')
const results = ref<any[]>([])
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

    // autofill address
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
// ITEMS
// --------------------
function addDelivery() {
    form.items.push({
        type: 'delivery',
        title: 'Delivery',
        price: 0,
        qty: 1,
        discount: 0,
        vat_amount: 0
    })
}

function addCustom() {
    form.items.push({
        type: 'custom',
        title: 'Custom Item',
        price: 0,
        qty: 1,
        discount: 0,
        vat_amount: 0
    })
}

function addProduct(product) {
    form.items.push({
        type: 'product',
        product_id: product.id,
        title: product.title,
        price: product.price || 0,
        qty: 1,
        discount: 0,
        vat_amount: 0
    })
}

function removeItem(index: number) {
    form.items.splice(index, 1)
}

// --------------------
// TOTAL
// --------------------
const total = computed(() => {
    return form.items.reduce((sum, item) => {
        return sum + ((item.price * item.qty) - (item.discount || 0))
    }, 0)
})

// --------------------
// SUBMIT
// --------------------
function submit() {
    if (form.contact_id) {
        form.contact = {} as any // remove new contact data
    }

    form.post(route('sales.store'), {
        onError: (errors) => {
            console.log(errors)
        }
    })
}
</script>

<template>
<AuthenticatedLayout>
    <template #header>
        <h2 class="text-xl font-semibold">Create Sale</h2>
    </template>

```
<div class="max-w-6xl mx-auto py-8 space-y-6">

    <pre>{{ form.errors }}</pre>

        <!-- DATE -->
    <div class="bg-white p-6 rounded shadow">
        <label class="block text-sm mb-1">Sale Date</label>
        <input v-model="form.invoice_date" type="date" class="rounded mb-2 border p-2" />

        <label class="block text-sm mb-1">Status</label>
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
            <div
                v-for="contact in results"
                :key="contact.id"
                @click="selectContact(contact)"
                class="p-2 hover:bg-gray-100 cursor-pointer"
            >
                {{ contact.name }}
            </div>
        </div>

        <div v-if="selectedContact" class="mt-4 p-3 bg-green-100 rounded flex justify-between">
            <span>{{ selectedContact.name }}</span>
            <button @click="clearContact" class="text-red-600">✕</button>
        </div>

        <div v-if="!selectedContact" class="grid grid-cols-2 gap-4 mt-4">
            <input v-model="form.contact.first_name" placeholder="First Name *" class="border p-2" />
            <input v-model="form.contact.last_name" placeholder="Last Name" class="border p-2" />
            <input v-model="form.contact.email" placeholder="Email" class="border p-2" />
            <input v-model="form.contact.mobile" placeholder="Phone" class="border p-2" />

            <select v-model="form.contact.type" class="border p-2 col-span-2">
                <option value="general_public">General Public</option>
                <option value="supplier">Supplier</option>
                <option value="company">Company</option>
            </select>
        </div>
    </div>

    <!-- ADDRESS -->
    <div class="bg-white p-6 rounded shadow">
        <h3 class="text-lg font-semibold mb-4">Delivery Address</h3>

        <div class="grid grid-cols-2 gap-4">
            <input v-model="form.address_1" placeholder="Address 1" class="border p-2 col-span-2" />
            <input v-model="form.address_2" placeholder="Address 2" class="border p-2 col-span-2" />
            <input v-model="form.town_city" placeholder="Town / City" class="border p-2" />
            <input v-model="form.postcode" placeholder="Postcode" class="border p-2" />
        </div>
    </div>



    <!-- ITEMS -->
    <div class="bg-white p-6 rounded shadow">

        <div class="flex justify-end gap-2 mb-4">
            <button
                @click="showProductModal = true"
                class="bg-green-600 text-white px-3 py-1 rounded"
            >
                + Product
            </button>

            <button @click="addDelivery" class="bg-blue-600 text-white px-3 py-1 rounded">
                + Delivery
            </button>

            <button @click="addCustom" class="bg-gray-500 text-white px-3 py-1 rounded">
                + Custom
            </button>
        </div>

        <table class="w-full border text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Type</th>
                    <th class="p-2">Title</th>
                    <th class="p-2">Price</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2">Discount</th>
                    <th class="p-2">Total</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="(item, i) in form.items" :key="i" class="border-t">
                    <td class="p-2">{{ item.type }}</td>

                    <td class="p-2">
                        <input v-model="item.title" class="border p-1 w-full" />
                    </td>

                    <td class="p-2">
                        <input v-model.number="item.price" type="number" class="border p-1 w-24" />
                    </td>

                    <td class="p-2">
                        <input v-model.number="item.qty" type="number" class="border p-1 w-16" />
                    </td>

                    <td class="p-2">
                        <input v-model.number="item.discount" type="number" class="border p-1 w-20" />
                    </td>

                    <td class="p-2 font-semibold">
                        £{{ ((item.price * item.qty) - (item.discount || 0)).toFixed(2) }}
                    </td>

                    <td class="p-2">
                        <button @click="removeItem(i)">❌</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- TOTAL -->
    <div class="text-right text-xl font-bold">
        Total: £{{ total.toFixed(2) }}
    </div>

    <!-- SUBMIT -->
    <button @click="submit" class="bg-black text-white px-4 py-2 rounded">
        Save Sale
    </button>

    <ProductSearchModal
        v-if="showProductModal"
        @close="showProductModal = false"
        @select="addProduct"
    />

</div>

</AuthenticatedLayout>
</template>
