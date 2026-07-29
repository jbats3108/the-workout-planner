<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { useRoutineEditor } from '@/routines/composables/useRoutineEditor';
import { Link } from '@inertiajs/vue3';

const { form, errorList, save, deleteRoutine } = useRoutineEditor();
</script>

<template>
    <header class="border-b border-border px-4 py-4 md:px-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-xs tracking-[0.2em] text-muted-foreground uppercase">Routine</p>
                <input
                    v-model="form.name"
                    class="mt-1 w-full border-0 border-b border-border bg-transparent text-2xl font-bold outline-none focus:border-primary"
                    required
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="flex flex-wrap gap-3 font-mono text-sm">
                <Link
                    :href="route('dashboard')"
                    class="rounded-full border border-border px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    Cancel
                </Link>
                <button
                    type="button"
                    class="rounded-full border border-destructive/50 px-4 py-2 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10"
                    @click="deleteRoutine"
                >
                    Delete
                </button>
                <button
                    type="button"
                    class="rounded-full bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                    :disabled="form.processing"
                    @click="save"
                >
                    Save
                </button>
            </div>
        </div>
        <InputError class="mt-2" :message="form.errors.blocks" />
        <div v-if="errorList.length" class="mt-2 space-y-1 text-sm text-destructive">
            <p v-for="(message, index) in errorList" :key="index">{{ message }}</p>
        </div>
        <p v-if="form.recentlySuccessful" class="mt-2 text-sm text-primary">Saved.</p>
    </header>
</template>
