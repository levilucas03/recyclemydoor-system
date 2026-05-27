<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    product: Object,
})

const subtitle = ref('Professionally removed, complete set, ready to install again')

const intro = ref(
    `${props.product.title} available and ready to install again.`
)

const delivery = ref(
    'Delivery may be available depending on location. Please message first to confirm delivery pricing.'
)

const footer = ref(
    'Please message with any questions before purchasing.'
)

const features = ref([
    'Professionally removed',
    'Ready for reinstallation',
    'Frame included where shown',
    'Please check measurements before buying',
])

const escapeHtml = (text) => {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
}

const htmlOutput = computed(() => {
    return `
<div style="font-family:Arial,Helvetica,sans-serif;">
    <div style="background:#3e4349;color:#fff;padding:30px 25px;">
        <h1 style="margin:0;font-size:32px;">
            ${escapeHtml(props.product.title)}
        </h1>
        <p style="margin:10px 0 0;font-size:16px;color:#d9d9d9;">
            ${escapeHtml(subtitle.value)}
        </p>
    </div>

    <div style="padding:25px;background:#f7f7f7;">
        <p style="font-size:16px;line-height:1.7;">
            ${escapeHtml(intro.value)}
        </p>
    </div>

    <div style="padding:25px;">
        <h2>Frame Measurements</h2>
        <p><strong>Height:</strong> ${props.product.height ?? '-'} mm</p>
        <p><strong>Width:</strong> ${props.product.width ?? '-'} mm</p>
        <p><strong>Depth:</strong> ${props.product.depth ?? '-'} mm</p>
    </div>

    <div style="padding:25px;">
        <h2>Key Features</h2>
        ${features.value.map(item => `
            <div style="padding:12px 0;border-bottom:1px solid #ececec;">
                ✔ ${escapeHtml(item)}
            </div>
        `).join('')}
    </div>

    <div style="padding:25px;background:#f7f7f7;">
        <h2>Delivery</h2>
        <p>${escapeHtml(delivery.value)}</p>
    </div>

    <div style="padding:25px;">
        <p>${escapeHtml(footer.value)}</p>
    </div>
</div>
`
})

const copyHtml = async () => {
    await navigator.clipboard.writeText(htmlOutput.value)
    alert('eBay HTML copied')
}
</script>

<template>
    <div class="p-6 bg-white shadow rounded-xl mt-10">
        <h2 class="font-semibold mb-4">eBay HTML</h2>

        <input v-model="subtitle" class="w-full border p-2 mb-3" placeholder="Subtitle" />

        <textarea v-model="intro" class="w-full border p-2 mb-3" rows="4" />

        <textarea
            v-model="features"
            class="w-full border p-2 mb-3"
            rows="5"
        />

        <textarea v-model="delivery" class="w-full border p-2 mb-3" rows="3" />
        <textarea v-model="footer" class="w-full border p-2 mb-3" rows="3" />

        <button type="button" @click="copyHtml" class="bg-green-500 px-4 py-2 rounded font-bold">
            Copy eBay HTML
        </button>

        <textarea class="w-full border p-2 mt-4 h-64" :value="htmlOutput" readonly />

        <div class="mt-6 border p-4">
            <div v-html="htmlOutput"></div>
        </div>
    </div>
</template>