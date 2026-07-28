<script setup lang="ts">
import LogSetSheet from '@/workouts/components/LogSetSheet.vue';
import PlateGuideCard from '@/workouts/components/PlateGuideCard.vue';
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';
import { plannedSetCount } from '@/workouts/lib/sets';

const {
    stageWeightKg,
    stageDropsetWeights,
    canPromoteToDropset,
    canAddWorkingSet,
    canRemoveWorkingSet,
    openLogSheet,
    cancelLogSheet,
    completeSet,
    addDropSegment,
    removeDropSegment,
    promoteToDropset,
    addWorkingSet,
    removeWorkingSet,
    finishWorkout,
    applyNearestLoad,
    applyStageNearestLoad,
    groupLabel,
    formatPlateStack,
    stageFormatPlateStack,
    plateLoad,
    stagePlateLoad,
    workout,
    current,
    setForm,
    draftSegments,
    logSheetOpen,
    supersetNext,
} = useWorkoutPlayer();

const unlockInput = (event: PointerEvent) => {
    const input = event.currentTarget;
    if (!(input instanceof HTMLInputElement) || !input.readOnly) {
        return;
    }

    input.readOnly = false;
    input.focus();
};
</script>

<template>
    <div v-if="current" class="flex flex-1 flex-col px-4 py-6 text-center">
        <div class="flex min-h-0 flex-1 flex-col items-center justify-center gap-8">
            <h2 class="text-3xl leading-tight font-semibold">{{ current.set.exercise_name }}</h2>

            <p class="font-mono text-3xl font-semibold tracking-tight text-foreground">
                Target
                <template v-if="current.set.is_dropset">
                    {{ stageDropsetWeights.join(' → ') }}{{ workout.weight_unit }}
                    <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                </template>
                <template v-else>
                    <span v-if="stageWeightKg != null">{{ stageWeightKg }}{{ workout.weight_unit }}</span>
                    <span v-if="current.set.target_reps != null"> × {{ current.set.target_reps }}</span>
                </template>
            </p>

            <PlateGuideCard
                v-if="stagePlateLoad && stageFormatPlateStack"
                class="w-full"
                :plate-load="stagePlateLoad"
                :format-plate-stack="stageFormatPlateStack"
                :weight-unit="workout.weight_unit"
                @apply-nearest="applyStageNearestLoad"
            />

            <div class="space-y-2">
                <p class="text-sm font-semibold tracking-wide text-foreground">
                    Block {{ current.block.position }} · {{ groupLabel(current.set.group_type) }} {{ current.set.set_index + 1 }} of
                    {{ plannedSetCount(current.block, current.set) }}
                    <span v-if="current.set.is_dropset"> · Dropset</span>
                    <span v-if="current.block.is_superset"> · Superset</span>
                </p>
                <p v-if="supersetNext" class="text-base text-muted-foreground">{{ supersetNext.label }}</p>
            </div>
        </div>

        <div class="mt-6 flex w-full flex-col gap-3 pb-4">
            <button
                type="button"
                class="rounded-full bg-primary px-6 py-4 text-base font-semibold text-primary-foreground disabled:opacity-50"
                :disabled="workout.status !== 'in_progress'"
                @click="openLogSheet"
            >
                Done
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

        <LogSetSheet v-model:open="logSheetOpen">
            <form class="flex min-h-0 flex-1 flex-col gap-4" @submit.prevent="completeSet">
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto">
                    <div>
                        <p class="text-xs tracking-widest text-muted-foreground uppercase">Log set</p>
                        <h3 class="mt-1 text-xl font-semibold">{{ current.set.exercise_name }}</h3>
                    </div>

                    <div class="space-y-4 pt-10">
                        <template v-if="current.set.is_dropset">
                            <label class="flex flex-col gap-1 text-sm text-muted-foreground">
                                Reps (shared)
                                <input
                                    v-model.number="setForm.reps"
                                    type="number"
                                    min="0"
                                    max="100"
                                    readonly
                                    class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                                    required
                                    @pointerdown="unlockInput"
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
                                        readonly
                                        class="flex-1 rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                                        required
                                        @pointerdown="unlockInput"
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
                            <PlateGuideCard
                                v-if="plateLoad && formatPlateStack"
                                :plate-load="plateLoad"
                                :format-plate-stack="formatPlateStack"
                                :weight-unit="workout.weight_unit"
                                @apply-nearest="applyNearestLoad"
                            />
                            <div class="flex gap-3">
                                <label class="flex min-w-0 flex-1 flex-col gap-1 text-sm text-muted-foreground">
                                    Weight ({{ workout.weight_unit }})
                                    <input
                                        v-model.number="setForm.weight_kg"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        inputmode="decimal"
                                        readonly
                                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                                        required
                                        @pointerdown="unlockInput"
                                    />
                                </label>
                                <label class="flex min-w-0 flex-1 flex-col gap-1 text-sm text-muted-foreground">
                                    Reps
                                    <input
                                        v-model.number="setForm.reps"
                                        type="number"
                                        min="0"
                                        max="100"
                                        readonly
                                        class="rounded-xl border border-border bg-card px-4 py-3 text-lg text-foreground"
                                        required
                                        @pointerdown="unlockInput"
                                    />
                                </label>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="flex shrink-0 flex-col gap-2">
                    <button
                        type="submit"
                        class="rounded-full bg-primary px-6 py-4 text-base font-semibold text-primary-foreground disabled:opacity-50"
                        :disabled="setForm.processing || workout.status !== 'in_progress'"
                    >
                        Log set
                    </button>
                    <button
                        type="button"
                        class="rounded-full border border-border px-6 py-3 text-sm"
                        :disabled="setForm.processing"
                        @click="cancelLogSheet"
                    >
                        Cancel
                    </button>
                </div>
            </form>
        </LogSetSheet>
    </div>
</template>
