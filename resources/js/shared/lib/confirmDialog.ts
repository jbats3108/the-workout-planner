import { reactive } from 'vue';

export type ConfirmVariant = 'default' | 'destructive';

export type ConfirmOptions = {
    title: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: ConfirmVariant;
};

type ConfirmState = {
    open: boolean;
    title: string;
    description: string;
    confirmLabel: string;
    cancelLabel: string;
    variant: ConfirmVariant;
};

export const confirmState: ConfirmState = reactive({
    open: false,
    title: '',
    description: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    variant: 'default',
});

let pending: ((value: boolean) => void) | null = null;

export function confirmDialog(options: ConfirmOptions): Promise<boolean> {
    if (pending) {
        const previous = pending;
        pending = null;
        previous(false);
    }

    confirmState.title = options.title;
    confirmState.description = options.description ?? '';
    confirmState.confirmLabel = options.confirmLabel ?? 'Confirm';
    confirmState.cancelLabel = options.cancelLabel ?? 'Cancel';
    confirmState.variant = options.variant ?? 'default';
    confirmState.open = true;

    return new Promise((resolve) => {
        pending = resolve;
    });
}

export function settleConfirm(value: boolean): void {
    if (!confirmState.open && !pending) {
        return;
    }

    confirmState.open = false;
    const resolve = pending;
    pending = null;
    resolve?.(value);
}
