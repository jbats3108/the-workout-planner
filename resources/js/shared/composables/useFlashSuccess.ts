import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useFlashSuccess() {
    const page = usePage();

    return computed(() => page.props.flash?.success ?? null);
}

export function useFlashError() {
    const page = usePage();

    return computed(() => page.props.flash?.error ?? null);
}
