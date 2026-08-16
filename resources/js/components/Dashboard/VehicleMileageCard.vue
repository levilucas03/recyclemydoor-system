<script setup lang="ts">

defineProps({
    vehicleMileageStats: {
        type: Array,
        default: () => []
    }
})

</script>

<template>
    <div class="bg-white rounded-xl p-6 shadow">

        <div class="flex justify-between items-center mb-5">
            <h3 class="text-lg font-semibold">
                Vehicle Mileage
            </h3>
        </div>

        <div
            v-if="vehicleMileageStats.length"
            class="space-y-6"
        >
            <div
                v-for="vehicle in vehicleMileageStats"
                :key="vehicle.vehicle_id"
            >
                <!-- VEHICLE -->
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <div class="font-semibold">
                            {{ vehicle.vehicle }}
                        </div>

                        <div
                            v-if="vehicle.registration"
                            class="text-xs text-gray-500"
                        >
                            {{ vehicle.registration }}
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-lg font-bold">
                            {{ vehicle.this_week.toLocaleString() }} mi
                        </div>

                        <div class="text-xs text-gray-500">
                            this week
                        </div>
                    </div>
                </div>

                <!-- STATS -->
                <div class="grid grid-cols-3 gap-3 text-sm">

                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500">
                            Last week
                        </div>

                        <div class="font-semibold mt-1">
                            {{ vehicle.last_week.toLocaleString() }} mi
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500">
                            Avg / week
                        </div>

                        <div class="font-semibold mt-1">
                            {{ vehicle.average_week.toLocaleString() }} mi
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-xs text-gray-500">
                            12 weeks
                        </div>

                        <div class="font-semibold mt-1">
                            {{ vehicle.last_12_weeks.toLocaleString() }} mi
                        </div>
                    </div>

                </div>

                <!-- WEEKLY BARS -->
                <div
                    v-if="vehicle.weekly.length"
                    class="flex items-end gap-1 h-20 mt-4"
                >
                    <div
                        v-for="week in vehicle.weekly"
                        :key="week.week"
                        class="flex-1 flex flex-col justify-end"
                    >
                        <div
                            class="bg-gray-300 rounded-t"
                            :style="{
                                height:
                                    Math.max(
                                        4,
                                        (
                                            week.miles /
                                            Math.max(
                                                ...vehicle.weekly.map(w => w.miles),
                                                1
                                            )
                                        ) * 64
                                    ) + 'px'
                            }"
                            :title="`${week.week}: ${week.miles} miles`"
                        ></div>
                    </div>
                </div>

                <div
                    v-if="vehicle !== vehicleMileageStats[vehicleMileageStats.length - 1]"
                    class="border-t mt-6"
                ></div>

            </div>
        </div>

        <div
            v-else
            class="text-sm text-gray-500"
        >
            Not enough mileage data yet.
        </div>

    </div>
</template>