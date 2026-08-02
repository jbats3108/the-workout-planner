import { slugify } from '@/admin/lib/slugify';
import type { FormDataType } from '@inertiajs/core';
import { useForm, type InertiaForm } from '@inertiajs/vue3';
import { watch } from 'vue';

type NameSlugFields = {
    name: string;
    slug: string;
};

export function useSlugNamedForm<TForm extends FormDataType<TForm> & NameSlugFields>(initial: TForm): InertiaForm<TForm> {
    const form = useForm(initial);

    watch(
        () => form.name,
        (name) => {
            form.slug = slugify(name) as TForm['slug'];
        },
    );

    return form;
}
