<script setup lang="ts">
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const {
    workout,
    current,
    setForm,
    draftSegments,
    groupLabel,
    plateLoad,
    formatPlateStack,
    gramsToKg,
    canPromoteToDropset,
    canAddWorkingSet,
    canRemoveWorkingSet,
    completeSet,
    addDropSegment,
    removeDropSegment,
    promoteToDropset,
    addWorkingSet,
    removeWorkingSet,
    applyNearestLoad,
    finishWorkout,
} = useWorkoutPlayer();
</script>

<template>
    <div v-if="current" class="flex flex-1 flex-col px-4 py-6">
        <p class="text-xs tracking-widest text-muted-foreground uppercase">
            Block {{ current.block.position }} · {{ groupLabel(current.set.group_type) }} · Set
            {{ current.set.set_index + 1 }}
            <span v-if="current.set.is_dropset"> · Dropset</span>
            <span v-if="current.block.is_superset"> · Superset</span>
        </p>
        <h2 class="mt-2 text-3xl leading-tight font-semibold">{{ current.set.exercise_name }}</h2>
        <p class="mt-2 font-mono text-muted-foreground">
            Target
            <template v-if="current.set.is_dropset">
                {{ draftSegments.map((s) => s.weight_kg).join(' → ') }}{{ workout.weight_unit }}
                <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
            </template>
            <template v-else>
                <span v-if="current.set.target_weight_kg != null"
                    >{{ current.set.target_weight_kg }}{{ workout.weight_unit }}</span
                >
                <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
            </template>
        </p>

        <form class="mt-8 flex flex-1 flex-col gap-4" @submit.prevent="completeSet">
            <template v-if="current.set.is_dropset">
                <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                    Reps (shared)
                    <input
                        v-model.number="setForm.reps"
                        type="number"
                        min="0"
                        max="100"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                        required
                    />
                </label>
                <div class="space-y-2">
                    <p class="text-xs tracking-wide text-muted-foreground uppercase">Segments</p>
                    <div v-for="(seg, si) in draftSegments" :key="si" class="flex items-center gap-2">
                        <span class="w-6 font-mono text-xs text-muted-foreground">{{ si + 1 }}</span>
                        <input
                            v-model.number="seg.weight_kg"
                            type="number"
                            step="0.01"
                            min="0"
                            inputmode="decimal"
                            class="flex-1 rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                            required
                        />
                        <span class="text-sm text-muted-foreground">{{ workout.weight_unit }}</span>
                        <button
                            type="button"
                            class="text-sm text-muted-foreground hover:text-destructive disabled:opacity-30"
                            :disabled="draftSegments.length <= 2"
                            @click="removeDropSegment(si)"
                        >
                            −
                        </button>
                    </div>
                    <button type="button" class="text-sm text-primary" @click="addDropSegment">+ Drop</button>
                </div>
            </template>
            <template v-else>
                <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                    Weight ({{ workout.weight_unit }})
                    <input
                        v-model.number="setForm.weight_kg"
                        type="number"
                        step="0.01"
                        min="0"
                        inputmode="decimal"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                        required
                    />
                </label>
                <div
                    v-if="plateLoad && formatPlateStack"
                    class="rounded-xl border border-border bg-card/60 px-4 py-3 text-sm"
                >
                    <p class="text-xs tracking-wide text-muted-foreground uppercase">Plates</p>
                    <p class="mt-1 font-mono text-foreground">{{ formatPlateStack }}</p>
                    <p v-if="!plateLoad.exact" class="mt-1 text-xs text-muted-foreground">
                        Nearest loadable:
                        {{ gramsToKg(plateLoad.total_g) }}{{ workout.weight_unit }}
                        <span v-if="plateLoad.delta_g > 0">(+{{ gramsToKg(plateLoad.delta_g) }})</span>
                        <span v-else-if="plateLoad.delta_g < 0">({{ gramsToKg(plateLoad.delta_g) }})</span>
                    </p>
                    <button
                        v-if="!plateLoad.exact"
                        type="button"
                        class="mt-2 text-xs font-medium text-primary hover:underline"
                        @click="applyNearestLoad"
                    >
                        Apply nearest
                    </button>
                </div>
                <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                    Reps
                    <input
                        v-model.number="setForm.reps"
                        type="number"
                        min="0"
                        max="100"
                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                        required
                    />
                </label>
            </template>
            <div class="mt-auto flex flex-col gap-3 pb-4">
                <button
                    type="submit"
                    class="rounded-full bg-primary px-6 py-4 text-base font-semibold text-primary-foreground disabled:opacity-50"
                    :disabled="setForm.processing || workout.status !== 'in_progress'"
                >
                    {{ current.set.is_dropset ? 'Complete dropset' : 'Complete set' }}
                </button>
                <button
                    v-if="canPromoteToDropset"
                    type="button"
                    class="rounded-md border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                    @click="promoteToDropset"
                >
                    Promote to dropset
                </button>
                <div v-if="canAddWorkingSet || canRemoveWorkingSet" class="flex gap-2">
                    <button
                        v-if="canAddWorkingSet"
                        type="button"
                        class="flex-1 rounded-md border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                        @click="addWorkingSet"
                    >
                        + Set
                    </button>
                    <button
                        v-if="canRemoveWorkingSet"
                        type="button"
                        class="flex-1 rounded-md border border-border px-4 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                        @click="removeWorkingSet"
                    >
                        − Set
                    </button>
                </div>
                <button
                    type="button"
                    class="rounded-full border border-border px-6 py-3 text-sm"
                    :disabled="workout.status !== 'in_progress'"
                    @click="finishWorkout"
                >
                    Finish workout
                </button>
            </div>
        </form>
    </div>
</template>
