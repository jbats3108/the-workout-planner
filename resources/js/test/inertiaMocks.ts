type InertiaMocks = {
    inertiaFormPut: ReturnType<typeof import('vitest').vi.fn>;
    inertiaFormPost: ReturnType<typeof import('vitest').vi.fn>;
    pageProps: {
        flash: { success: string | null };
        errors: Record<string, string>;
    };
    routerMocks: {
        delete: ReturnType<typeof import('vitest').vi.fn>;
        post: ReturnType<typeof import('vitest').vi.fn>;
        visit: ReturnType<typeof import('vitest').vi.fn>;
        on: ReturnType<typeof import('vitest').vi.fn>;
    };
};

export function inertiaMocks(): InertiaMocks {
    return (globalThis as typeof globalThis & { __inertiaMocks: InertiaMocks }).__inertiaMocks;
}
