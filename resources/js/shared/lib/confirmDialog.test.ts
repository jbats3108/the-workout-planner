import { confirmDialog, confirmState, settleConfirm } from '@/shared/lib/confirmDialog';
import { afterEach, describe, expect, it } from 'vitest';

describe('confirmDialog', () => {
    afterEach(() => {
        settleConfirm(false);
    });

    it('resolves true when confirmed', async () => {
        const pending = confirmDialog({
            title: 'Delete routine?',
            description: 'It will be archived.',
            confirmLabel: 'Delete',
            variant: 'destructive',
        });

        expect(confirmState.open).toBe(true);
        expect(confirmState.title).toBe('Delete routine?');
        expect(confirmState.variant).toBe('destructive');

        settleConfirm(true);
        await expect(pending).resolves.toBe(true);
        expect(confirmState.open).toBe(false);
    });

    it('resolves false when cancelled', async () => {
        const pending = confirmDialog({ title: 'Leave workout?' });
        settleConfirm(false);
        await expect(pending).resolves.toBe(false);
    });

    it('cancels a previous pending confirm when a new one opens', async () => {
        const first = confirmDialog({ title: 'First' });
        const second = confirmDialog({ title: 'Second' });

        await expect(first).resolves.toBe(false);
        expect(confirmState.title).toBe('Second');

        settleConfirm(true);
        await expect(second).resolves.toBe(true);
    });
});
