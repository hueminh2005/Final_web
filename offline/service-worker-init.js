// Service Worker Registration

if ('serviceWorker' in navigator) {
    // Register service worker for the application root with explicit scope
    navigator.serviceWorker.register('/note-management/service-worker.js', { scope: '/note-management/' })
        .then(registration => {
            console.log('Service Worker registered successfully:', registration.scope);
        })
        .catch(error => {
            console.log('Service Worker registration failed:', error);
        });
}

// Check online/offline status
window.addEventListener('online', () => {
    console.log('App is online');
    document.body.classList.remove('offline');
    // Trigger sync
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
        navigator.serviceWorker.ready.then(registration => {
            return registration.sync.register('sync-notes');
        }).catch(err => console.log(err));
    }
});

window.addEventListener('offline', () => {
    console.log('App is offline');
    document.body.classList.add('offline');
});

// Initialize offline database
if ('indexedDB' in window) {
    const request = indexedDB.open('NoteApp', 1);
    
    request.onerror = () => console.error('IndexedDB failed to open');
    request.onsuccess = (e) => {
        window.db = e.target.result;
    };
    
    request.onupgradeneeded = (e) => {
        const db = e.target.result;
        
        // Notes store
        if (!db.objectStoreNames.contains('notes')) {
            const noteStore = db.createObjectStore('notes', { keyPath: 'id' });
            noteStore.createIndex('user_id', 'user_id', { unique: false });
            noteStore.createIndex('updated_at', 'updated_at', { unique: false });
        }
        
        // Labels store
        if (!db.objectStoreNames.contains('labels')) {
            const labelStore = db.createObjectStore('labels', { keyPath: 'id' });
            labelStore.createIndex('user_id', 'user_id', { unique: false });
        }
        
        // Sync queue
        if (!db.objectStoreNames.contains('syncQueue')) {
            db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
        }
    };
}
