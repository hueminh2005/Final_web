// Service Worker

const CACHE_VERSION = 'noteapp-v1';
const CACHE_NAME = `${CACHE_VERSION}`;

const urlsToCache = [
    '/',
    '/index.php',
    '/login.php',
    '/register.php',
    '/editor.php',
    '/assets/css/style.css',
    '/assets/css/auth.css',
    '/assets/css/editor.css',
    '/assets/css/responsive.css',
    '/assets/js/app.js',
    '/assets/js/editor.js',
    '/manifest.json'
];

// Install event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(urlsToCache).catch(err => {
                console.log('Cache addAll error:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Handle API requests differently
    if (url.pathname.startsWith('/ajax/') || url.pathname.startsWith('/controllers/')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cache successful API responses
                    const clonedResponse = response.clone();
                    if (response.status === 200) {
                        caches.open(CACHE_NAME).then(cache => {
                            cache.put(request, clonedResponse);
                        });
                    }
                    return response;
                })
                .catch(() => {
                    // Return from cache if offline
                    return caches.match(request).then(response => {
                        if (response) {
                            return response;
                        }
                        // Return offline page if available
                        return caches.match('/offline.html');
                    });
                })
        );
    } else {
        // For page requests and assets
        event.respondWith(
            caches.match(request).then(response => {
                // Return from cache if available
                if (response) {
                    return response;
                }
                
                // Otherwise try network
                return fetch(request).then(response => {
                    // Don't cache non-200 responses
                    if (!response || response.status !== 200) {
                        return response;
                    }
                    
                    // Cache successful responses
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => {
                        cache.put(request, responseToCache);
                    });
                    
                    return response;
                }).catch(() => {
                    // Return offline page as fallback
                    return caches.match('/offline.html');
                });
            })
        );
    }
});

// Sync event for background sync
self.addEventListener('sync', event => {
    if (event.tag === 'sync-notes') {
        event.waitUntil(syncNotes());
    }
});

async function syncNotes() {
    try {
        const db = await openDB();
        const tx = db.transaction('syncQueue', 'readonly');
        const store = tx.objectStore('syncQueue');
        const items = await getAllFromStore(store);
        
        for (const item of items) {
            try {
                const response = await fetch(item.url, {
                    method: item.method,
                    body: JSON.stringify(item.data)
                });
                
                if (response.ok) {
                    // Remove from sync queue
                    const delTx = db.transaction('syncQueue', 'readwrite');
                    delTx.objectStore('syncQueue').delete(item.id);
                }
            } catch (err) {
                console.error('Sync error:', err);
            }
        }
    } catch (err) {
        console.error('Sync failed:', err);
    }
}

function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('NoteApp', 1);
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}

function getAllFromStore(store) {
    return new Promise((resolve, reject) => {
        const request = store.getAll();
        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);
    });
}
