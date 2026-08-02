import { useAdminDelete } from '@/admin/composables/useAdminDelete';
import { confirmDialog } from '@/shared/lib/confirmDialog';
import { beforeEach, describe, expect, it, vi } from 'vitest';

const deleteMock = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => ({
        processing: false,
        delete: deleteMock,
    }),
}));

vi.mock('@/shared/lib/confirmDialog', () => ({
    confirmDialog: vi.fn(),
}));

describe('useAdminDelete', () => {
    beforeEach(() => {
        deleteMock.mockReset();
        vi.mocked(confirmDialog).mockReset();
    });

    it('deletes after confirm', async () => {
        vi.mocked(confirmDialog).mockResolvedValue(true);
        const { destroy } = useAdminDelete();

        await destroy('/admin/items/1', 'Delete “Bench”?');

        expect(confirmDialog).toHaveBeenCalledWith({
            title: 'Delete “Bench”?',
            description: undefined,
            confirmLabel: 'Delete',
            variant: 'destructive',
        });
        expect(deleteMock).toHaveBeenCalledWith('/admin/items/1');
    });

    it('skips delete when cancelled', async () => {
        vi.mocked(confirmDialog).mockResolvedValue(false);
        const { destroy } = useAdminDelete();

        await destroy('/admin/items/1', 'Delete “Bench”?');

        expect(deleteMock).not.toHaveBeenCalled();
    });
});
