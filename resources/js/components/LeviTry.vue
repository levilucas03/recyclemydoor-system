<script setup lang="ts">

interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();

</script>

<template>
    <div class="mb-8 space-y-0.5 pr-6 pl-6">
        <h2 class="text-xl font-semibold tracking-tight">{{ title }}</h2>
        <p v-if="description" class="text-sm text-muted-foreground">
            {{ description }}
        </p>
    </div>

</template>

<style scoped>

</style>
