import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo;
    }
}

window.Pusher = Pusher;

export type EchoConfig = {
    key: string;
    cluster: string;
};

let echoInstance: Echo | null = null;

export function getEcho(config: EchoConfig | null): Echo | null {
    if (!config?.key) {
        return null;
    }
    if (echoInstance) {
        return echoInstance;
    }
    echoInstance = new Echo({
        broadcaster: 'pusher',
        key: config.key,
        cluster: config.cluster,
        forceTLS: true,
    });
    return echoInstance;
}
