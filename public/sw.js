const CACHE_NAME = 'eskm-pwa-v3';
const APP_SHELL = ['/', '/skm', '/manifest.webmanifest', '/assets/logo_mahulu.png', '/assets/logo-bayan.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(async (cache) => {
            // Pre-cache softly: one missing URL should not break SW install.
            await Promise.allSettled(
                APP_SHELL.map((url) => cache.add(url)),
            );
        }),
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => key !== CACHE_NAME)
                .map((key) => caches.delete(key)),
        )),
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // Ignore non-HTTP(S) and cross-origin requests (e.g. chrome-extension://).
    if (
        event.request.method !== 'GET'
        || !['http:', 'https:'].includes(url.protocol)
        || url.origin !== self.location.origin
        // Sync API for the admin respondent page: always live, never from cache.
        || url.pathname.startsWith('/admin/responden')
    ) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Cache only successful basic responses.
                if (!response || !response.ok || response.type !== 'basic') {
                    return response;
                }

                const responseClone = response.clone();

                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, responseClone));

                return response;
            })
            .catch(() => caches.match(event.request).then((cached) => cached || caches.match('/'))),
    );
});