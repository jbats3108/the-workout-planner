import { usePage } from '@inertiajs/vue3';
import { route as ziggyRoute } from 'ziggy-js';

export function useZiggyRoute() {
    const page = usePage();

    return (name: string, params?: Record<string, unknown>, absolute?: boolean) => ziggyRoute(name, params, absolute, page.props.ziggy);
}
