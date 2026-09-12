import { defineConfig, loadEnv } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const devPort = Number.parseInt(env.VITE_DEV_PORT || '5173', 10);
    const hmrPort = Number.parseInt(env.VITE_HMR_PORT || String(devPort), 10);
    const hmrHost = env.VITE_HMR_HOST || undefined;
    const configuredHost = env.VITE_DEV_HOST;
    const devHost = configuredHost === 'true' ? true : configuredHost || true;

    return {
        plugins: [
            react(),
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/js/react/main.jsx',
                ],
                refresh: true,
            }),
        ],
        server: {
            host: devHost,
            port: devPort,
            ...(env.VITE_DEV_ORIGIN ? { origin: env.VITE_DEV_ORIGIN } : {}),
            ...(hmrHost ? { hmr: { host: hmrHost, port: hmrPort } } : {}),
        },
    };
});
