<script setup>
import { computed } from 'vue'

const props = defineProps({
    materialId: Number,
    colourId: Number,
    categoryIds: Array,
    materials: Array,
    colours: Array,
    categories: Array,
})

const emit = defineEmits(['generated'])

// ---------------------
// HELPERS
// ---------------------

const materialName = computed(() => {
    return props.materials.find(m => m.id === props.materialId)?.name || ''
})

const colourName = computed(() => {
    return props.colours.find(c => c.id === props.colourId)?.name || ''
})

// Find first selected category (child)
const selectedCategory = computed(() => {
    for (let parent of props.categories) {
        for (let child of parent.children || []) {
            if (props.categoryIds.includes(child.id)) {
                return {
                    child: child.name,
                    parent: parent.name
                }
            }
        }
    }
    return null
})

// ---------------------
// GENERATE TITLE
// ---------------------

function generateTitle() {
    if (!selectedCategory.value) return

    const parts = [
        materialName.value,
        selectedCategory.value.child,
        // selectedCategory.value.parent,
        colourName.value
    ].filter(Boolean)

    const title = parts.join(' ')

    emit('generated', title)
}
</script>

<template>
    <div class="mt-4">
        <button
            type="button"
            @click="generateTitle"
            class="bg-purple-600 text-white px-4 py-2 rounded"
        >
            Generate Title
        </button>
    </div>
</template>