import { createInertiaApp } from '@inertiajs/vue3';
import {
    configureInertiaApp,
    inertiaProgress,
    inertiaTitle,
    resolveInertiaLayout,
} from '@/inertia';

createInertiaApp({
    title: inertiaTitle,
    layout: resolveInertiaLayout,
    progress: inertiaProgress,
    withApp: configureInertiaApp,
});
