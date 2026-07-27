<script setup lang="ts">
/**
 * Routine editor — desktop: dense list (A), mobile: stage (B), styling: B (dark zinc + lime).
 */
import AppLayout from '@/layouts/AppLayout.vue';
import DesktopBlockList from '@/routines/components/DesktopBlockList.vue';
import ExerciseFinder from '@/routines/components/ExerciseFinder.vue';
import MobileStage from '@/routines/components/MobileStage.vue';
import RoutineEditorHeader from '@/routines/components/RoutineEditorHeader.vue';
import { createRoutineEditor, routineEditorKey, type EditRoutineProps } from '@/routines/composables/useRoutineEditor';
import { Head } from '@inertiajs/vue3';
import { provide } from 'vue';

const props = defineProps<EditRoutineProps>();

const editor = createRoutineEditor(props);
provide(routineEditorKey, editor);

const { form } = editor;
</script>

<template>
    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: form.name || 'Routine', href: '#' },
        ]"
    >
        <Head :title="`Edit · ${form.name}`" />

        <div class="flex flex-1 flex-col overscroll-y-none bg-background text-foreground">
            <RoutineEditorHeader />

            <div class="hidden border-b border-border px-4 py-3 md:block md:px-6">
                <ExerciseFinder deferred />
            </div>

            <DesktopBlockList />
            <MobileStage />
        </div>
    </AppLayout>
</template>
