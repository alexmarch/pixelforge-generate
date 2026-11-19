import { createInertiaApp } from '@inertiajs/vue3';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';


declare global {
    interface Window {
        Echo: any;
    }
}

if (typeof window !== 'undefined') {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        cluster: 'mt1',
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: false,
        enabledTransports: ['ws', 'wss'],
        Pusher: Pusher,
    });
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        delay: 250,
        color: '#4B5563',
        includeCSS: true,
        showSpinner: false,
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
});

