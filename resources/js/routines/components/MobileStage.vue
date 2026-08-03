<script setup lang="ts">
import DeloadSettings from '@/routines/components/DeloadSettings.vue';
import DropsetEditor from '@/routines/components/DropsetEditor.vue';
import EditorDisclosure from '@/routines/components/EditorDisclosure.vue';
import ExercisePicker from '@/routines/components/ExercisePicker.vue';
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { canSetupAfterBlock } from '@/routines/lib/blocks';
import { optionalRepsPlaceholder, parseOptionalReps } from '@/routines/lib/optionalReps';
import { Link } from '@inertiajs/vue3';

const {
    form,
    active,
    activeBlock,
    activeExerciseIndex,
    warmUpExpanded,
    toggleWarmUpExpanded,
    dropsetsExpanded,
    toggleDropsetsExpanded,
    progressionExpanded,
    toggleProgressionExpanded,
    selectBlockExercise,
    exerciseName,
    removeBlock,
    addBlock,
    trimDropsetsToSetCount,
    formatRest,
    warmUpText,
    setWarmUpText,
    addWarmUpStep,
    removeWarmUpStep,
    clearWarmUp,
    toggleSuperset,
    dropsetSummary,
    achievementFloorDefault,
    save,
    deleteRoutine,
    mutating,
} = useRoutineEditor();
</script>

<template>
    <div class="flex flex-col md:hidden">
        <div class="flex gap-2 overflow-x-auto px-4 py-3">
            <button
                v-for="(b, i) in form.blocks"
                :key="i"
                type="button"
                class="shrink-0 rounded-lg border px-3 py-2 text-left text-sm"
                :class="i === active ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground'"
                @click="selectBlockExercise(i, 0)"
            >
                <div class="font-mono text-xs">{{ i + 1 }}{{ b.is_superset ? ' SS' : '' }}</div>
                <div class="max-w-28 truncate">{{ exerciseName(b.exercises[0]?.exercise_id) }}</div>
            </button>
        </div>

        <main v-if="activeBlock" class="mx-auto flex w-full max-w-lg flex-col gap-4 px-4 pb-28">
            <div class="rounded-2xl border border-border bg-card p-4">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-base font-semibold">
                        Exercise {{ active + 1 }}
                        <span v-if="activeBlock.is_superset" class="ml-2 text-sm font-normal text-primary">Superset</span>
                    </h2>
                    <button type="button" class="text-xs text-destructive" @click="removeBlock(active)">Remove</button>
                </div>

                <div v-for="(ex, ei) in activeBlock.exercises" :key="ei" class="mb-4 last:mb-0">
                    <p v-if="activeBlock.is_superset" class="mb-1 font-mono text-xs text-muted-foreground">
                        {{ ei === 0 ? 'A' : 'B' }}
                    </p>
                    <ExercisePicker
                        v-model="ex.exercise_id"
                        variant="mobile"
                        :active="ei === activeExerciseIndex"
                        @open="selectBlockExercise(active, ei)"
                    />
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <label class="block">
                            <span class="text-xs text-muted-foreground">Working kg</span>
                            <input
                                v-model.number="ex.working_weight_kg"
                                type="number"
                                step="0.01"
                                min="0"
                                inputmode="decimal"
                                class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-center text-2xl font-semibold tabular-nums outline-none focus:border-primary"
                            />
                        </label>
                        <label class="block">
                            <span class="text-xs text-muted-foreground">Target reps</span>
                            <input
                                v-model.number="ex.prescribed_reps"
                                type="number"
                                min="1"
                                class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 text-center text-2xl font-semibold tabular-nums outline-none focus:border-primary"
                            />
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 border-t border-border pt-3">
                    <label>
                        <span class="text-xs text-muted-foreground">Working sets</span>
                        <input
                            v-model.number="activeBlock.working.set_count"
                            type="number"
                            min="1"
                            class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                            @change="trimDropsetsToSetCount(activeBlock)"
                        />
                    </label>
                    <label>
                        <span class="text-xs text-muted-foreground">Rest ({{ formatRest(activeBlock.working.rest_seconds) }})</span>
                        <input
                            v-model.number="activeBlock.working.rest_seconds"
                            type="number"
                            min="0"
                            step="15"
                            class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                        />
                    </label>
                </div>

                <EditorDisclosure
                    v-if="!activeBlock.is_superset"
                    :expanded="dropsetsExpanded"
                    label="Dropsets"
                    :summary="dropsetSummary(activeBlock) || 'None'"
                    @toggle="toggleDropsetsExpanded"
                >
                    <DropsetEditor :block="activeBlock" variant="mobile" />
                </EditorDisclosure>

                <EditorDisclosure :expanded="progressionExpanded" label="Progression" summary="Floor override" @toggle="toggleProgressionExpanded">
                    <div class="space-y-4">
                        <div v-for="(ex, ei) in activeBlock.exercises" :key="ei" class="space-y-2">
                            <p v-if="activeBlock.is_superset" class="font-mono text-xs text-muted-foreground">
                                {{ ei === 0 ? 'A' : 'B' }}
                            </p>
                            <label class="block">
                                <span class="text-xs text-muted-foreground">Floor</span>
                                <input
                                    :value="ex.achievement_floor ?? ''"
                                    type="number"
                                    min="1"
                                    max="100"
                                    :placeholder="optionalRepsPlaceholder(achievementFloorDefault)"
                                    class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                                    @input="ex.achievement_floor = parseOptionalReps(($event.target as HTMLInputElement).value)"
                                />
                            </label>
                        </div>
                        <p class="text-xs text-muted-foreground">Empty inherits Settings → Training. Bump is always the exercise Target (reps).</p>
                    </div>
                </EditorDisclosure>

                <EditorDisclosure
                    :expanded="warmUpExpanded"
                    label="Warm-up"
                    :summary="activeBlock.warm_up.steps.length ? warmUpText(activeBlock) : 'None'"
                    @toggle="toggleWarmUpExpanded"
                >
                    <div class="space-y-2">
                        <label class="block">
                            <span class="text-xs text-muted-foreground">Compact (40x5, 60x3)</span>
                            <input
                                :value="warmUpText(activeBlock)"
                                class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-sm text-primary/90"
                                @change="setWarmUpText(activeBlock, ($event.target as HTMLInputElement).value)"
                            />
                        </label>
                        <label class="block">
                            <span class="text-xs text-muted-foreground">Warm-up rest ({{ formatRest(activeBlock.warm_up.rest_seconds) }})</span>
                            <input
                                v-model.number="activeBlock.warm_up.rest_seconds"
                                type="number"
                                min="0"
                                step="15"
                                class="mt-1 w-full rounded-xl border border-border bg-background px-3 py-2 font-mono text-lg"
                            />
                        </label>
                        <div v-for="(step, si) in activeBlock.warm_up.steps" :key="si" class="flex items-center gap-1.5">
                            <input
                                v-model.number="step.percent"
                                type="number"
                                min="1"
                                max="100"
                                class="w-16 rounded-lg border border-border bg-background px-2 py-1.5 font-mono text-sm"
                                aria-label="Warm-up percent"
                            />
                            <span class="text-xs text-muted-foreground">×</span>
                            <input
                                v-model.number="step.reps"
                                type="number"
                                min="1"
                                max="100"
                                class="w-14 rounded-lg border border-border bg-background px-2 py-1.5 font-mono text-sm"
                                aria-label="Warm-up reps"
                            />
                            <label
                                v-if="si < activeBlock.warm_up.steps.length - 1"
                                class="flex items-center gap-1 text-xs text-muted-foreground"
                                title="Setup after this warm-up"
                            >
                                <input v-model="step.has_setup_after" type="checkbox" />
                                Setup
                            </label>
                            <button
                                type="button"
                                class="ml-auto text-xs text-muted-foreground hover:text-destructive"
                                @click="removeWarmUpStep(activeBlock, si)"
                            >
                                −
                            </button>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" class="text-xs text-primary" @click="addWarmUpStep(activeBlock)">+ Step</button>
                            <button
                                v-if="activeBlock.warm_up.steps.length"
                                type="button"
                                class="text-xs text-muted-foreground hover:text-destructive"
                                @click="clearWarmUp(activeBlock)"
                            >
                                Clear warm-up
                            </button>
                        </div>
                    </div>
                </EditorDisclosure>

                <DeloadSettings variant="mobile" />

                <div class="mt-3 flex flex-wrap gap-4 border-t border-border pt-3 text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" :checked="activeBlock.is_superset" @change="toggleSuperset(activeBlock)" />
                        Superset
                    </label>
                    <label
                        class="flex items-center gap-2"
                        :class="activeBlock.warm_up.steps.length ? '' : 'opacity-40'"
                        :title="activeBlock.warm_up.steps.length ? undefined : 'Add warm-up steps first'"
                    >
                        <input v-model="activeBlock.has_setup_after_warm_up" type="checkbox" :disabled="!activeBlock.warm_up.steps.length" />
                        Setup before working
                    </label>
                    <label
                        class="flex items-center gap-2"
                        :class="canSetupAfterBlock(active, form.blocks.length) ? '' : 'opacity-40'"
                        :title="canSetupAfterBlock(active, form.blocks.length) ? undefined : 'Not on the final exercise'"
                    >
                        <input v-model="activeBlock.has_setup_after" type="checkbox" :disabled="!canSetupAfterBlock(active, form.blocks.length)" />
                        Setup after exercise
                    </label>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                    @click="addBlock(false)"
                >
                    Add exercise
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                    @click="addBlock(true)"
                >
                    Add superset
                </button>
            </div>
        </main>

        <div v-else class="px-4 pb-28">
            <p class="py-8 text-center text-muted-foreground">No exercises yet.</p>
            <div class="flex gap-2">
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                    @click="addBlock(false)"
                >
                    Add exercise
                </button>
                <button
                    type="button"
                    class="flex-1 rounded-xl border border-dashed border-border px-4 py-3 text-sm text-muted-foreground hover:border-primary hover:text-primary"
                    @click="addBlock(true)"
                >
                    Add superset
                </button>
            </div>
        </div>

        <div class="fixed right-0 bottom-0 left-0 flex justify-center gap-2 px-4 pt-2 pb-[max(1rem,env(safe-area-inset-bottom,0px))]">
            <Link :href="route('dashboard')" class="rounded-full border border-border bg-background px-4 py-3 text-sm text-muted-foreground">
                Cancel
            </Link>
            <button
                type="button"
                class="rounded-full border border-destructive/50 bg-background px-4 py-3 text-sm text-destructive disabled:opacity-50"
                :disabled="mutating || form.processing"
                @click="deleteRoutine"
            >
                Delete
            </button>
            <button
                type="button"
                class="rounded-full bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                :disabled="form.processing || mutating"
                @click="save"
            >
                Save
            </button>
        </div>
    </div>
</template>
