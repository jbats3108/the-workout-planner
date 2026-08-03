<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { confirmDialog } from '@/shared/lib/confirmDialog';
import { formatDate } from '@/shared/lib/formatDate';
import { type BreadcrumbItem } from '@/types';
import type { HistoryWorkout } from '@/workouts/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Trash2 } from 'lucide-vue-next';

defineProps<{
    history: {
        workouts: HistoryWorkout[];
        routine_filter_options: { slug: string; name: string }[];
        routine_slug: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'History', href: '/history' }];
const deleteForm = useForm({});

const filterByRoutine = (routineSlug: string | null) => {
    if (routineSlug === null) {
        router.get(route('history.index'));
        return;
    }
    router.get(route('history.index', { routine: routineSlug }));
};

const deleteWorkout = async (workout: HistoryWorkout) => {
    if (deleteForm.processing) {
        return;
    }
    const ok = await confirmDialog({
        title: `Remove “${workout.routine_name}” from history?`,
        description: 'This cannot be undone.',
        confirmLabel: 'Remove',
        variant: 'destructive',
    });
    if (!ok) {
        return;
    }
    deleteForm.delete(route('history.destroy', workout.id));
};
</script>

<template>
    <Head title="History" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-4 p-4 text-foreground">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">History</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Finished workouts</p>
                </div>
                <label class="flex flex-col gap-1 text-xs text-muted-foreground">
                    Routine
                    <select
                        class="rounded border border-border bg-background px-3 py-2 text-sm text-foreground"
                        :value="history.routine_slug ?? ''"
                        @change="filterByRoutine(($event.target as HTMLSelectElement).value || null)"
                    >
                        <option value="">All routines</option>
                        <option v-for="routine in history.routine_filter_options" :key="routine.slug" :value="routine.slug">
                            {{ routine.name }}
                        </option>
                    </select>
                </label>
            </div>

            <ul v-if="history.workouts.length" class="divide-y divide-border rounded-xl border border-border">
                <li v-for="workout in history.workouts" :key="workout.id" class="flex items-center">
                    <Link
                        :href="route('history.show', workout.id)"
                        class="flex min-w-0 flex-1 items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-card"
                    >
                        <div>
                            <p class="font-medium">{{ workout.routine_name }}</p>
                            <p class="font-mono text-xs text-muted-foreground">{{ workout.mode }}</p>
                        </div>
                        <p class="text-sm text-muted-foreground">{{ formatDate(workout.finished_at) }}</p>
                    </Link>
                    <button
                        type="button"
                        class="mr-3 shrink-0 rounded-md p-2 text-destructive transition-opacity hover:opacity-80 disabled:opacity-50"
                        :aria-label="`Delete ${workout.routine_name}`"
                        :disabled="deleteForm.processing"
                        @click="deleteWorkout(workout)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </li>
            </ul>
            <p v-else class="text-sm text-muted-foreground">No finished workouts yet.</p>
        </div>
    </AppLayout>
</template>
