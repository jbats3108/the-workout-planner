import { useSlugNamedForm } from '@/admin/composables/useSlugNamedForm';
import { describe, expect, it, vi } from 'vitest';
import { nextTick, reactive } from 'vue';

vi.mock('@inertiajs/vue3', () => ({
    useForm: <T extends object>(initial: T) => reactive({ ...initial }),
}));

describe('useSlugNamedForm', () => {
    it('slugifies name as it changes', async () => {
        const form = useSlugNamedForm({ name: '', slug: '' });

        form.name = 'Bench Press';
        await nextTick();

        expect(form.slug).toBe('bench-press');
    });
});
