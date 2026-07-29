<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { Routine } from '@/routines/types';
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    routine: Routine;
    canStart: boolean;
    startBlockedReason: string | null;
}>();

const emit = defineEmits<{
    start: [mode: 'normal' | 'deload'];
    delete: [];
}>();

const startTitle = (mode: 'normal' | 'deload') => {
    if (props.startBlockedReason) {
        return props.startBlockedReason;
    }
    return mode === 'deload' ? 'Start deload' : 'Start workout';
};
</script>

<template>
    <div class="rounded-xl border border-border bg-card p-4">
        <div>
            <h3 class="text-lg font-semibold">{{ routine.name }}</h3>
            <p class="mt-1 font-mono text-xs text-muted-foreground">Deload {{ routine.deload_weight_factor }}w / {{ routine.deload_reps_factor }}r</p>
        </div>
        <p v-if="!routine.can_start" class="mt-3 text-xs text-muted-foreground">Add exercises in the editor before starting.</p>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap items-center gap-2">
                <Button type="button" size="pill" :disabled="!canStart" :title="startTitle('normal')" @click="emit('start', 'normal')">
                    Start
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    size="pill"
                    class="text-foreground/80"
                    :disabled="!canStart"
                    :title="startTitle('deload')"
                    @click="emit('start', 'deload')"
                >
                    Deload
                </Button>
            </div>
            <div class="flex items-center gap-1">
                <Button variant="ghost" size="icon-sm" as-child>
                    <Link
                        :href="route('routines.edit', routine.slug)"
                        class="text-muted-foreground hover:text-primary"
                        title="Edit routine"
                        aria-label="Edit routine"
                    >
                        <Pencil class="size-5" />
                    </Link>
                </Button>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon-sm"
                    class="text-destructive hover:text-destructive"
                    title="Delete routine"
                    aria-label="Delete routine"
                    @click="emit('delete')"
                >
                    <Trash2 class="size-5" />
                </Button>
            </div>
        </div>
    </div>
</template>
