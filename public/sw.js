const CACHE_NAME = 'estateflow-v5';
const OFFLINE_URL = '/offline';
// '/' is left out on purpose: for a signed-in user it redirects to a private dashboard.
const PRECACHE = [OFFLINE_URL, '/manifest.json', '/favicon.ico', '/icons/icon-192.png', '/icons/icon-512.png'];

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

    // Prefer current static assets so different devices do not keep stale UI bundles.
    if (
        url.pathname.match(/\.(css|js|woff2?|ttf|png|jpg|jpeg|svg|ico|webp)$/) ||
        url.hostname === 'fonts.googleapis.com' ||
        url.hostname === 'fonts.gstatic.com'
    ) {
        e.respondWith(
            fetch(e.request)
                .then((res) => {
                    if (res.ok) {
                        const copy = res.clone();
                        caches.open(CACHE_NAME).then((c) => c.put(e.request, copy));
                    }
                    return res;
                })
                .catch(() => caches.match(e.request))
        );
        return;
    }

    // Never cache HTML pages because authenticated responses may contain private data.
    e.respondWith(
        fetch(e.request)
            .then((res) => res)
            .catch(() => caches.match(OFFLINE_URL))
    );
});
