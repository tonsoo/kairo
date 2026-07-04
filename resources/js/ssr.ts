import { createInertiaApp } from '@inertiajs/vue3';
import {
    configureInertiaApp,
    inertiaProgress,
    resolveInertiaLayout,
} from '@/inertia';

createInertiaApp({
    layout: resolveInertiaLayout,
    progress: inertiaProgress,
    withApp: configureInertiaApp,
});
