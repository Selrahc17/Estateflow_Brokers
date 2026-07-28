const CACHE_NAME = 'estateflow-v2';
const OFFLINE_URL = '/offline';
const PRECACHE = ['/', '/manifest.json', '/favicon.ico', OFFLINE_URL];

self.addEventListener('install', (e) => {
    e.waitUntil(caches.open(CACHE_NAME).then((c) => c.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', (e) => {
    if (e.request.method !== 'GET') return;

    const url = new URL(e.request.url);

    // Cache-first for static assets (CSS, JS, fonts, images)
    if (
        url.pathname.match(/\.(css|js|woff2?|ttf|png|jpg|jpeg|svg|ico|webp)$/) ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com'
    ) {
        e.respondWith(
            caches.match(e.request).then((cached) => {
                if (cached) return cached;
                return fetch(e.request).then((res) => {
                    if (res.ok) {
                        const copy = res.clone();
                        caches.open(CACHE_NAME).then((c) => c.put(e.request, copy));
                    }
                    return res;
                });
            })
        );
        return;
    }

    // Network-first for HTML pages
    e.respondWith(
        fetch(e.request)
            .then((res) => {
                if (res.ok) {
                    const copy = res.clone();
                    caches.open(CACHE_NAME).then((c) => c.put(e.request, copy));
                }
                return res;
            })
            .catch(() =>
                caches.match(e.request).then((cached) => cached || caches.match(OFFLINE_URL))
            )
    );
});
