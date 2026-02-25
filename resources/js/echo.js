import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const parseBoolean = (value, fallback = false) => {
    if (typeof value === 'boolean') return value;
    if (typeof value === 'string') {
        const normalized = value.trim().toLowerCase();
        if (['1', 'true', 'yes', 'on'].includes(normalized)) return true;
        if (['0', 'false', 'no', 'off'].includes(normalized)) return false;
    }

    return fallback;
};

const runtimeFeatures = window.ReimbursementFeatures ?? {};
const runtimeRealtime = runtimeFeatures.realtimeNotifications;
const runtimeEchoClient = runtimeFeatures.echoClient;
const envRealtime = parseBoolean(import.meta.env.VITE_FEATURE_REALTIME_NOTIFICATIONS, true);
const envEchoClient = parseBoolean(import.meta.env.VITE_FEATURE_ECHO_CLIENT, true);

const realtimeEnabled = typeof runtimeRealtime === 'boolean' ? runtimeRealtime : envRealtime;
const echoClientEnabled = typeof runtimeEchoClient === 'boolean' ? runtimeEchoClient : envEchoClient;

if (realtimeEnabled && echoClientEnabled) {
    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
    const tlsEnabled = scheme === 'https';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
        forceTLS: tlsEnabled,
        enabledTransports: tlsEnabled ? ['wss', 'ws'] : ['ws', 'wss'],
        disableStats: true,
    });
} else {
    window.Echo = null;
}
