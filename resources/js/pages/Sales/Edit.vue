<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ProductSearchModal from '@/Components/ProductSearchModal.vue'
import axios from 'axios'

const props = defineProps({
    sale: Object,
    statusOptions: Array,
    sources: Array,
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

    contact_id: props.sale?.contact_id,

    address_1: props.sale?.deliver_address_1 || '',
    address_2: props.sale?.deliver_address_2 || '',
    town_city: props.sale?.deliver_town_city || '',
    postcode: props.sale?.deliver_postcode || '',

    source_id: props.sale?.source_id ?? null,

    invoice_date: props.sale?.invoice_date,
    notes: props.sale?.notes,

    status: props.sale?.status || '',

    items: props.sale?.items.map(item => ({
        id: item.id,

        type: item.type,

        product_id: item.product_id,
        product: item.product,

        title: item.title,
        sku: item.product?.sku || '',

        image: item.image,
        size: item.size,

        price: Number(item.price),
        qty: item.qty,

        discount: Number(item.discount),
        vat_amount: Number(item.vat_amount)
    })) || []
})

// --------------------
// CONTACT SEARCH
// --------------------
const search = ref('')
const results = ref([])
const selectedContact = ref<any | null>(props.sale?.contact || null)

watch(search, async (value) => {

    if (selectedContact.value) return

    if (!value || value.length < 2) {
        results.value = []
        return
    }

    const res = await axios.get(route('contacts.search'), {
        params: { q: value }
    })

    results.value = res.data

})

function selectContact(contact: any) {

    selectedContact.value = contact

    form.contact_id = contact.id

    search.value = contact.name

    form.address_1 = contact.address_1 || ''
    form.address_2 = contact.address_2 || ''
    form.town_city = contact.town_city || ''
    form.postcode = contact.postcode || ''

    results.value = []
}

function clearContact() {

    selectedContact.value = null
    form.contact_id = null
    search.value = ''

}

// --------------------
// PRODUCT MODAL
// --------------------
const showProductModal = ref(false)

function addProduct(product: any) {

    form.items.push({

        type: 'product',

        product_id: product.id,

        title: product.sku ? `${product.sku} - ${product.title}` : product.title,
        sku: product.sku,

        image: product.image,
        size: product.size,

        price: product.price || 0,

        qty: 1,
        discount: 0,
        vat_amount: 0

    })
}

// --------------------
// ADD ITEMS
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

function removeItem(index: number) {
    form.items.splice(index, 1)
}

// --------------------
// TOTAL
// --------------------
const total = computed(() => {

    return form.items.reduce((sum, item) => {

        return sum + (
            (item.price * item.qty)
            - (item.discount || 0)
        )

    }, 0)

})

// --------------------
// SUBMIT
// --------------------
function submit() {

    if (form.contact_id) {
        form.contact = {} as any
    }

    form.put(`/sales/${props.sale.id}`)
}
</script>

<template>

<AuthenticatedLayout>

    <template #header>
        <h2 class="text-xl font-semibold">
            Edit Sale
        </h2>
    </template>


    <div class="max-w-6xl mx-auto py-8 space-y-6">

        <!-- DATE / STATUS -->
        <div class="bg-white p-6 rounded shadow space-y-4">

            <input
                v-model="form.invoice_date"
                type="date"
                class="border p-2 rounded w-full"
            />

            <select
                v-model="form.status"
                class="border rounded p-2 w-full"
            >
                <option
                    v-for="option in statusOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>

            <select
                v-model="form.source_id"
                class="border rounded p-2 w-full"
            >
                <option :value="null">
                    Select source
                </option>

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

            <input
                v-model="search"
                placeholder="Search contact..."
                class="border p-2 w-full rounded"
            />

            <div
                v-if="results.length"
                class="border rounded mt-2"
            >
                <div
                    v-for="c in results"
                    :key="c.id"
                    @click="selectContact(c)"
                    class="p-2 hover:bg-gray-100 cursor-pointer"
                >
                    {{ c.name }}
                </div>
            </div>

            <div
                v-if="selectedContact"
                class="mt-3 bg-green-100 p-3 rounded flex justify-between items-center"
            >
                <span>
                    {{ selectedContact.name }}
                </span>

                <button @click="clearContact">
                    ✕
                </button>
            </div>

        </div>

        <!-- ADDRESS -->
        <div class="bg-white p-6 rounded shadow grid grid-cols-2 gap-4">

            <input
                v-model="form.address_1"
                placeholder="Address 1"
                class="border p-2 rounded col-span-2"
            />

            <input
                v-model="form.address_2"
                placeholder="Address 2"
                class="border p-2 rounded col-span-2"
            />

            <input
                v-model="form.town_city"
                placeholder="Town"
                class="border p-2 rounded"
            />

            <input
                v-model="form.postcode"
                placeholder="Postcode"
                class="border p-2 rounded"
            />

        </div>

        <!-- ITEMS -->
        <div class="bg-white p-6 rounded shadow">

            <!-- ACTIONS -->
            <div class="flex justify-end gap-2 mb-4">

                <button
                    @click="showProductModal = true"
                    class="bg-green-600 text-white px-3 py-1 rounded"
                >
                    + Product
                </button>

                <button
                    @click="addDelivery"
                    class="bg-blue-600 text-white px-3 py-1 rounded"
                >
                    + Delivery
                </button>

                <button
                    @click="addCustom"
                    class="bg-gray-500 text-white px-3 py-1 rounded"
                >
                    + Custom
                </button>

            </div>

            <!-- TABLE -->
            <table class="w-full border text-sm">

                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2">Price</th>
                        <th class="p-2">Qty</th>
                        <th class="p-2">Discount</th>
                        <th class="p-2">Total</th>
                        <th></th>
                    </tr>
                </thead>

                <tbody>

                    <tr
                        v-for="(item, i) in form.items"
                        :key="i"
                        class="border-t"
                    >

                        <!-- PRODUCT -->
                        <td class="p-2">

                            <div class="flex items-center gap-3">

                                <!-- IMAGE -->
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    class="w-16 h-16 rounded border object-cover bg-gray-100"
                                />

                                <!-- INFO -->
                                <div class="flex-1 min-w-0">

                                     

                                    <!-- SKU -->
                                   <div
                                        v-if="item.sku"
                                        class="text-xs font-bold text-blue-600 flex items-center gap-2"
                                    >
                                        <span>SKU: {{ item.sku }}</span>

                                        <a
                                            v-if="item.product_id"
                                            :href="route('products.edit', item.product_id)"
                                            target="_blank"
                                            class="text-gray-500 hover:text-blue-600 underline font-normal"
                                            @click.stop
                                        >
                                            View product
                                        </a>
                                    </div>

                                    <!-- TITLE -->
                                    <input
                                        v-model="item.title"
                                        class="border p-1 rounded w-full mt-1"
                                    />

                                    <!-- SIZE -->
                                    <div
                                        v-if="item.size"
                                        class="text-xs text-gray-500 mt-1"
                                    >
                                        {{ item.size }}
                                    </div>

                                    <!-- TYPE -->
                                    <div class="mt-1">

                                        <span
                                            class="text-[10px] uppercase bg-gray-100 px-2 py-1 rounded"
                                        >
                                            {{ item.type }}
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </td>

                        <!-- PRICE -->
                        <td class="p-2">
                            <input
                                v-model.number="item.price"
                                type="number"
                                step="0.01"
                                class="border p-1 w-24 rounded"
                            />
                        </td>

                        <!-- QTY -->
                        <td class="p-2">
                            <input
                                v-model.number="item.qty"
                                type="number"
                                class="border p-1 w-16 rounded"
                            />
                        </td>

                        <!-- DISCOUNT -->
                        <td class="p-2">
                            <input
                                v-model.number="item.discount"
                                type="number"
                                step="0.01"
                                class="border p-1 w-24 rounded"
                            />
                        </td>

                        <!-- TOTAL -->
                        <td class="p-2 font-semibold">
                            £{{
                                (
                                    (item.price * item.qty)
                                    - (item.discount || 0)
                                ).toFixed(2)
                            }}
                        </td>

                        <!-- REMOVE -->
                        <td class="p-2 text-center">

                            <button
                                @click="removeItem(i)"
                                class="text-red-500"
                            >
                                ❌
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- TOTAL -->
        <div class="text-right text-2xl font-bold">
            Total: £{{ total.toFixed(2) }}
        </div>

        <!-- SAVE -->
        <button
            @click="submit"
            class="bg-black text-white px-6 py-3 rounded"
        >
            Update Sale
        </button>

        <!-- MODAL -->
        <ProductSearchModal
            v-if="showProductModal"
            @close="showProductModal = false"
            @select="addProduct"
        />

    </div>

</AuthenticatedLayout>

</template>