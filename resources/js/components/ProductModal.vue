<script setup lang="ts">
import { ref, computed, watch } from 'vue'

type Category = {
    id: number
    name: string
    children?: Category[]
}

const props = defineProps<{
    product?: any
    categories: Category[]
    materials: any[]
    colours: any[]
}>()

const emit = defineEmits(['save', 'close'])

// ---------------------
// STATE
// ---------------------
const localProduct = ref({
    title: '',
    width: '',
    height: '',
    depth: '70',
    category_ids: [] as number[],
    material_id: null as number | null,
    colour_id: null as number | null,
    type: '',
    price: ''
})

const selectedCategoryParent = ref<number | null>(null)

// ---------------------
// CATEGORY LOGIC
// ---------------------
const categoryChildren = computed(() => {
    const parent = props.categories?.find(
        p => p.id === selectedCategoryParent.value
    )
    return parent?.children ?? []
})

// ---------------------
// LOAD EDIT DATA
// ---------------------
watch(
    () => props.product,
    (val) => {
        if (!val) {
            // RESET when creating new
            localProduct.value = {
                title: '',
                width: '',
                height: '',
                depth: '70',
                category_ids: [],
                material_id: null,
                colour_id: null,
                type: '',
                price: ''
            }
            selectedCategoryParent.value = null
            return
        }

        // LOAD EXISTING PRODUCT
        localProduct.value = {
            title: val.title || '',
            width: val.width || '',
            height: val.height || '',
            category_ids: val.category_ids || [],
            material_id: val.material_id || null,
            colour_id: val.colour_id || null,
            type: val.type || '',
            price: val.price || ''
        }

        // 🔥 IMPORTANT: set parent automatically
        const parent = props.categories.find(p =>
            p.children?.some(child =>
                localProduct.value.category_ids.includes(child.id)
            )
        )

        if (parent) {
            selectedCategoryParent.value = parent.id
        }
    },
    { immediate: true }
)

// ---------------------
// SAVE
// ---------------------
function save() {
    emit('save', { ...localProduct.value })
    emit('close')
}

// Title Generation 

function getMaterialName(id: number | null) {
    return props.materials.find(m => m.id === id)?.name || ''
}

function getColourName(id: number | null) {
    return props.colours.find(c => c.id === id)?.name || ''
}

function getCategoryName() {
    if (!localProduct.value.category_ids.length) return ''

    const allChildren = props.categories.flatMap(p => p.children || [])

    const match = allChildren.find(c =>
        localProduct.value.category_ids.includes(c.id)
    )

    return match?.name || ''
}

const userEditedTitle = ref(false)
const isAutoUpdating = ref(false)

watch(() => localProduct.value.title, (val, old) => {
    if (isAutoUpdating.value) return

    if (old !== undefined && val !== old) {
        userEditedTitle.value = true
    }
})

watch(
    [
        () => localProduct.value.material_id,
        () => localProduct.value.category_ids,
        () => localProduct.value.colour_id
    ],
    () => {
        if (userEditedTitle.value) return

        const material = getMaterialName(localProduct.value.material_id)
        const category = getCategoryName()
        const colour = getColourName(localProduct.value.colour_id)

        isAutoUpdating.value = true

        localProduct.value.title = [
            material,
            category,
            colour
        ]
        .filter(Boolean)
        .join(' ')

        // allow next tick before resetting
        setTimeout(() => {
            isAutoUpdating.value = false
        }, 0)
    },
    { deep: true }
)

</script>

<template>
<div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded shadow w-full max-w-md">

        <h3 class="text-lg font-semibold mb-4">
            Add Product
        </h3>

        <!-- <pre>{{ localProduct }}</pre> -->
        

        <div class="space-y-3">

            <!-- TITLE -->
            <input
                v-model="localProduct.title"
                placeholder="Title"
                class="border p-2 w-full rounded"
            />

            <!-- CATEGORY -->
            <div>
                <select v-model.number="selectedCategoryParent" class="border p-2 w-full rounded mb-2">
                    <option :value="null">Select Category</option>

                    <option
                        v-for="p in categories"
                        :key="p.id"
                        :value="p.id"
                    >
                        {{ p.name }}
                    </option>
                </select>

                <div v-if="categoryChildren.length" class="flex flex-wrap gap-2">
                    <label
                        v-for="child in categoryChildren"
                        :key="child.id"
                        class="flex items-center gap-2 border px-3 py-1 rounded cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            :value="child.id"
                            v-model="localProduct.category_ids"
                        />
                        {{ child.name }}
                    </label>
                </div>
            </div>

            <!-- MATERIAL -->
            <select v-model.number="localProduct.material_id" class="border p-2 w-full rounded">
                <option :value="null">Select Material</option>
                <option v-for="m in materials" :key="m.id" :value="m.id">
                    {{ m.name }}
                </option>
            </select>

            <!-- COLOUR -->
            <select v-model.number="localProduct.colour_id" class="border p-2 w-full rounded">
                <option :value="null">Select Colour</option>
                <option v-for="c in colours" :key="c.id" :value="c.id">
                    {{ c.name }}
                </option>
            </select>

            

            <!-- SIZE -->
            <div class="grid grid-cols-3 gap-2">
                <input
                    v-model="localProduct.width"
                    placeholder="Width (mm)"
                    class="border p-2 rounded"
                />
                <input
                    v-model="localProduct.height"
                    placeholder="Height (mm)"
                    class="border p-2 rounded"
                />
                <input
                    v-model="localProduct.depth"
                    placeholder="Depth (mm)"
                    class="border p-2 rounded"
                />
            </div>

            <!-- PRICE -->
            <input
                v-model="localProduct.price"
                type="number"
                placeholder="Price"
                class="border p-2 w-full rounded"
            />

        </div>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-2 mt-4">
            <button @click="$emit('close')" class="px-4 py-2">
                Cancel
            </button>

            <button @click="save" class="bg-green-600 text-white px-4 py-2 rounded">
                Save
            </button>
        </div>

    </div>
</div>
</template>