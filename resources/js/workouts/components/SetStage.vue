<script setup lang="ts">
import LogSetSheet from '@/workouts/components/LogSetSheet.vue';
import PlateGuideCard from '@/workouts/components/PlateGuideCard.vue';
import { useWorkoutPlayer } from '@/workouts/composables/useWorkoutPlayer';

const {
    workout,
    current,
    setForm,
    draftSegments,
    logSheetOpen,
    groupLabel,
    plateLoad,
    stagePlateLoad,
    formatPlateStack,
    stageFormatPlateStack,
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
            class="mt-6"
            :plate-load="stagePlateLoad"
            :format-plate-stack="stageFormatPlateStack"
            :weight-unit="workout.weight_unit"
            @apply-nearest="applyStageNearestLoad"
        />

        <div class="mt-auto flex flex-col gap-3 pb-4">
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
                        <label class="flex flex-col gap-1 text-sm text-muted-foreground">
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
                        <PlateGuideCard
                            v-if="plateLoad && formatPlateStack"
                            :plate-load="plateLoad"
                            :format-plate-stack="formatPlateStack"
                            :weight-unit="workout.weight_unit"
                            @apply-nearest="applyNearestLoad"
                        />
                        <label class="flex flex-col gap-1 text-sm text-muted-foreground">
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
                    </template>
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
