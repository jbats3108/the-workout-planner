<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import type { HistoryWorkout } from '@/workouts/types';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps<{
    history: {
        workouts: HistoryWorkout[];
        routine_filter_options: { id: number; name: string }[];
        routine_id: number | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'History', href: '/history' }];

const filterByRoutine = (routineId: number | null) => {
    if (routineId === null) {
        router.get(route('history.index'));
        return;
    }
    router.get(route('history.index', { routine: routineId }));
};

const formatDate = (iso: string) => {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'medium' });
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
                        :value="history.routine_id ?? ''"
                        @change="
                            filterByRoutine(($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)
                        "
                    >
                        <option value="">All routines</option>
                        <option v-for="routine in history.routine_filter_options" :key="routine.id" :value="routine.id">
                            {{ routine.name }}
                        </option>
                    </select>
                </label>
            </div>

            <ul v-if="history.workouts.length" class="divide-y divide-border rounded-xl border border-border">
                <li v-for="workout in history.workouts" :key="workout.id">
                    <Link
                        :href="route('history.show', workout.id)"
                        class="flex items-center justify-between gap-3 px-4 py-3 transition-colors hover:bg-card"
                    >
                        <div>
                            <p class="font-medium">{{ workout.routine_name }}</p>
                            <p class="font-mono text-xs text-muted-foreground">{{ workout.mode }}</p>
                        </div>
                        <p class="text-sm text-muted-foreground">{{ formatDate(workout.finished_at) }}</p>
                    </Link>
                </li>
            </ul>
            <p v-else class="text-sm text-muted-foreground">No finished workouts yet.</p>
        </div>
    </AppLayout>
</template>
