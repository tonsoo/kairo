import type { Auth } from '@/types/auth';

declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            locale: string;
            localeOptions: Array<{
                code: string;
                url: string;
            }>;
            routeDefaults: Record<string, string>;
            currentUrl: string;
            meta: {
                title: string;
                description?: string | null;
                robots: string;
                applicationName: string;
                canonical: string;
                alternates: Array<{
                    locale: string;
                    url: string;
                }>;
                openGraph: {
                    type: string;
                    siteName: string;
                    title: string;
                    description?: string | null;
                    url: string;
                    locale: string;
                    alternateLocales: string[];
                };
                twitter: {
                    card: string;
                    title: string;
                    description?: string | null;
                };
                structuredData?: string | null;
            };
            auth: Auth;
            sidebarOpen: boolean;
            [key: string]: unknown;
        };
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof Router;
        $page: Page;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}
