import { vi } from 'vitest';
import { reactive } from 'vue';

const inertia = vi.hoisted(() => {
    const inertiaFormPut = vi.fn();
    const inertiaFormPost = vi.fn();
    const pageProps = {
        flash: { success: null as string | null, error: null as string | null },
        errors: {} as Record<string, string>,
    };
    const routerMocks = {
        delete: vi.fn(),
        post: vi.fn(),
        visit: vi.fn(),
        on: vi.fn(() => vi.fn()),
    };

    function createForm(initial: object) {
        return reactive({
            ...initial,
            processing: false,
            errors: {},
            recentlySuccessful: false,
            transform(fn: (data: object) => unknown) {
                const transformed = fn(initial);
                return {
                    put: (url: string, options?: unknown) => inertiaFormPut(url, transformed, options),
                    post: (url: string, options?: unknown) => inertiaFormPost(url, transformed, options),
                };
            },
            put: inertiaFormPut,
            post: inertiaFormPost,
            reset: vi.fn(),
        });
    }

    return {
        inertiaFormPut,
        inertiaFormPost,
        pageProps,
        routerMocks,
        createForm,
    };
});

vi.mock('@inertiajs/vue3', () => ({
    useForm: (keyOrInitial: string | object, maybeInitial?: object) =>
        inertia.createForm(typeof keyOrInitial === 'string' ? (maybeInitial ?? {}) : keyOrInitial),
    usePage: () => ({ props: inertia.pageProps }),
    router: inertia.routerMocks,
    Head: {
        name: 'Head',
        props: ['title'],
        setup: () => () => null,
    },
    Link: {
        name: 'Link',
        props: ['href'],
        template: '<a :href="href"><slot /></a>',
    },
}));

(globalThis as typeof globalThis & { __inertiaMocks: typeof inertia }).__inertiaMocks = inertia;

vi.stubGlobal(
    'route',
    vi.fn((name: string, _params?: unknown) => `/${String(name)}`),
);
