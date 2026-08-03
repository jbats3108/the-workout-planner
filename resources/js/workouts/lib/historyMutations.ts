import { confirmDialog } from '@/shared/lib/confirmDialog';
import { useForm } from '@inertiajs/vue3';

export function useHistoryDelete() {
    const deleteForm = useForm({});

    async function destroy(workoutId: string, routineName: string): Promise<void> {
        if (deleteForm.processing) {
            return;
        }

        const ok = await confirmDialog({
            title: `Remove “${routineName}” from history?`,
            description: 'This cannot be undone.',
            confirmLabel: 'Remove',
            variant: 'destructive',
        });

        if (!ok) {
            return;
        }

        deleteForm.delete(route('history.destroy', workoutId));
    }

    return { deleteForm, destroy };
}
