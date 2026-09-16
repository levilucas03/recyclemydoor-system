<script setup>

defineProps({
    value: {
        default: null,
    },

    inverse: {
        type: Boolean,
        default: false,
    },

    suffix: {
        type: String,
        default: '%',
    },
})

</script>

<template>

    <div class="mt-1 text-xs">

        <span
            v-if="value === null"
            class="font-medium text-gray-400"
        >
            No previous data
        </span>

        <span
            v-else-if="value === 0"
            class="font-medium text-gray-400"
        >
            No change
        </span>

        <span
            v-else
            class="font-semibold"
            :class="[
                inverse
                    ? (
                        value < 0
                            ? 'text-green-600'
                            : 'text-red-600'
                    )
                    : (
                        value > 0
                            ? 'text-green-600'
                            : 'text-red-600'
                    )
            ]"
        >

            {{ value > 0 ? '↑' : '↓' }}

            {{ Math.abs(value) }}{{ suffix }}

            <span class="font-normal text-gray-400">
                vs previous period
            </span>

        </span>

    </div>

</template>