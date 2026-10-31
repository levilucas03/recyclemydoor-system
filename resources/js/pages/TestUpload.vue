
<script setup>
import { ref } from 'vue'
import axios from 'axios'

const file = ref(null)
const loading = ref(false)
const message = ref('')
const imageUrl = ref(null)

const selectFile = (event) => {
    file.value = event.target.files[0] ?? null
}

const upload = async (method) => {
    if (!file.value) return

    loading.value = true
    message.value = ''

    try {
        let response

        if (method === 'multipart') {
            const data = new FormData()
            data.append('image', file.value)

            response = await axios.post(
                '/test-upload/multipart',
                data
            )
        } else {
            response = await axios.post(
                '/test-upload/raw',
                file.value,
                {
                    headers: {
                        'Content-Type': 'application/octet-stream',
                        'X-File-Name': encodeURIComponent(file.value.name),
                    }
                }
            )
        }

        message.value = 'Upload successful!'
        imageUrl.value = response.data.url

    } catch (error) {
        message.value =
            'Upload failed: ' +
            (error.response?.status ?? error.message)
        console.error(error)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="max-w-xl mx-auto mt-10 bg-white p-6 rounded-xl shadow">
        <h2 class="text-xl font-bold mb-4">
            Image Upload Test
        </h2>

        <input
            type="file"
            accept="image/*"
            @change="selectFile"
            class="mb-4"
        />

        <div class="flex gap-3">
            <button
                @click="upload('multipart')"
                :disabled="loading || !file"
                class="bg-blue-600 text-white px-4 py-2 rounded"
            >
                Test Multipart
            </button>

            <button
                @click="upload('raw')"
                :disabled="loading || !file"
                class="bg-green-600 text-white px-4 py-2 rounded"
            >
                Test Raw Upload
            </button>
        </div>

        <p class="mt-4">{{ message }}</p>

        <img
            v-if="imageUrl"
            :src="imageUrl"
            class="mt-4 max-h-64 rounded"
        />
    </div>
</template>
