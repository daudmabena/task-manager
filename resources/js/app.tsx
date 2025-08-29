import '../css/app.css';
import 'react-toastify/dist/ReactToastify.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';
import { progress } from '@inertiajs/progress';
import { ToastContainer } from 'react-toastify';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Initialize Inertia progress bar
progress.init({
    color: '#4B5563',
    showSpinner: true,
});

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);

        root.render(
            <>
                <App {...props} />
                <ToastContainer
                    position="top-right"
                    autoClose={5000}
                    hideProgressBar={false}
                    newestOnTop={false}
                    closeOnClick
                    rtl={false}
                    pauseOnFocusLoss
                    draggable
                    pauseOnHover
                    theme="light"
                />
            </>
        );
    },
});

// This will set light / dark mode on load...
initializeTheme();
