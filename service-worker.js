// Service Worker (clean rebuild)

const CACHE_VERSION = 'noteapp-v1';
const CACHE_NAME = `noteapp-cache-${CACHE_VERSION}`;

const urlsToCache = [
    './',
    './index.php',
    './login.php',
    './register.php',
    './editor.php',
    './assets/css/style.css',
    './assets/css/auth.css',
    './assets/css/editor.css',
    './assets/css/responsive.css',
    './assets/js/app.js',
    './assets/js/editor.js',
    './manifest.json',
    './offline.html'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => Promise.all(
            keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k))
        ))
    );
    self.clients.claim();
});

// Utility: determine if response is cacheable
function isCacheableResponse(response) {
    if (!response) return false;
    if (response.type === 'opaqueredirect') return false;
    if (response.redirected) return false;
    if (response.status >= 300 && response.status < 400) return false;
    return response.status === 200;
}

self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // Bypass SW for chrome-extension or browser-internal requests
    if (url.protocol.startsWith('chrome') || url.protocol === 'about:') return;

    // Handle navigation requests (pages) with network-first, fallback to cache/offline
    if (req.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const networkResp = await fetch(req);
                if (isCacheableResponse(networkResp)) {
                    const copy = networkResp.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(req, copy)).catch(()=>{});
                }
                return networkResp;
            } catch (err) {
                const cached = await caches.match(req);
                if (cached) return cached;
                return caches.match('./offline.html');
            }
        })());
        return;
    }

    // For API requests and other fetches: try network, but avoid caching redirects
    if (url.pathname.includes('/ajax/') || url.pathname.includes('/controllers/')) {
        event.respondWith((async () => {
            try {
                const resp = await fetch(req);
                if (isCacheableResponse(resp)) {
                    const copy = resp.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(req, copy)).catch(()=>{});
                }
                return resp;
            } catch (err) {
                const cached = await caches.match(req);
                if (cached) return cached;
                return new Response(JSON.stringify({ error: 'offline' }), { status: 503, headers: { 'Content-Type': 'application/json' } });
            }
        })());
        return;
    }

    // For assets: cache-first, then network
    event.respondWith((async () => {
        const cached = await caches.match(req);
        if (cached) return cached;
        try {
            const resp = await fetch(req);
            if (isCacheableResponse(resp)) {
                const copy = resp.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(req, copy)).catch(()=>{});
            }
            return resp;
        } catch (err) {
            return caches.match('./offline.html');
        }
    })());
});

// Background sync stub
self.addEventListener('sync', event => {
    if (event.tag === 'sync-notes') {
        event.waitUntil((async () => {
            // implement sync if needed
        })());
    }
});
