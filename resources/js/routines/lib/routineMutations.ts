import { confirmDialog } from '@/shared/lib/confirmDialog';
import { router } from '@inertiajs/vue3';
import type { Ref } from 'vue';

type MutatingRef = Ref<boolean>;

export type DuplicateRoutineOptions = {
    mutating: MutatingRef;
    confirm?: () => Promise<boolean>;
};

export async function duplicateRoutine(slug: string, options: DuplicateRoutineOptions): Promise<void> {
    if (options.mutating.value) {
        return;
    }

    if (options.confirm) {
        const ok = await options.confirm();
        if (!ok) {
            return;
        }
    }

    options.mutating.value = true;
    router.post(
        route('routines.duplicate', slug),
        {},
        {
            onFinish: () => {
                options.mutating.value = false;
            },
        },
    );
}

export async function deleteRoutine(slug: string, name: string, mutating: MutatingRef): Promise<void> {
    if (mutating.value) {
        return;
    }

    const ok = await confirmDialog({
        title: `Delete “${name}”?`,
        description: 'It will be archived and removed from your list.',
        confirmLabel: 'Delete',
        variant: 'destructive',
    });

    if (!ok) {
        return;
    }

    mutating.value = true;
    router.delete(route('routines.delete', slug), {
        onFinish: () => {
            mutating.value = false;
        },
    });
}
