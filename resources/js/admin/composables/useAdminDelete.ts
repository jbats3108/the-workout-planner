import { confirmDialog } from '@/shared/lib/confirmDialog';
import { useForm } from '@inertiajs/vue3';

export function useAdminDelete() {
    const deleteForm = useForm({});

    async function destroy(url: string, title: string, description?: string): Promise<void> {
        if (deleteForm.processing) {
            return;
        }

        const ok = await confirmDialog({
            title,
            description,
            confirmLabel: 'Delete',
            variant: 'destructive',
        });

        if (!ok) {
            return;
        }

        deleteForm.delete(url);
    }

    return { deleteForm, destroy };
}
