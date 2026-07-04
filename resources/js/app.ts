import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import {
    configureInertiaApp,
    inertiaProgress,
    resolveInertiaLayout,
} from '@/inertia';
import { initializeFlashToast } from '@/lib/flashToast';

createInertiaApp({
    layout: resolveInertiaLayout,
    progress: inertiaProgress,
    withApp: configureInertiaApp,
});

initializeTheme();

initializeFlashToast();
