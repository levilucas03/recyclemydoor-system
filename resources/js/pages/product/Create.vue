<template>
    <Head title="Cretae Product" />

    <AuthenticatedLayout>

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Product</h2>
        </template>

        <div class="py-12">

            <div class="max-w-2xl mx-auto p-6 bg-white rounded shadow">
                <h1 class="text-xl font-bold mb-4">Create Product</h1>
                <form @submit.prevent="submit" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block mb-1">Sku</label>
                        <input v-model="form.sku" type="text" class="w-full border rounded p-2" />
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.sku">{{ form.errors.sku }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Width</label>
                        <input v-model="form.width" class="w-full border rounded p-2"></input>
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.width">{{ form.errors.width }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-1">Height</label>
                        <input v-model="form.height" class="w-full border rounded p-2"></input>
                        <div class="text-red-500 text-sm mt-1" v-if="form.errors.height">{{ form.errors.height }}</div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Brand</label>
                        <select v-model="form.brand_id" class="w-full border rounded p-2">
                            <option :value="null">-- Select Brand --</option>
                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                {{ brand.name }}
                            </option>
                        </select>
                    </div>


                    <input type="file" name="image" @change="uploadImage" />
                    <img v-if="form.image" :src="form.image" alt="Product Image" />

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Create
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

const props = defineProps({
    brands: Array
})

const form = useForm({
    sku: '',
    width: '',
    height: '',
    brand_id: '',
    image: ''
})

// const uploadImage = async (event) => {
//     const file = event.target.files[0]
//     const data = new FormData()
//     data.append('image', file)
//
//     const res = await axios.post('/api/upload', data, {
//         headers: { 'Content-Type': 'multipart/form-data' }
//     })
//
//     form.value.image = res.data.url
// }

function submit() {
    form.post(route('products.store'))
}
</script>
