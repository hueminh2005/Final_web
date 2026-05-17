// IndexedDB utilities for offline storage

class OfflineDB {
    constructor() {
        this.db = null;
        this.init();
    }
    
    init() {
        if ('indexedDB' in window) {
            const request = indexedDB.open('NoteApp', 1);
            
            request.onerror = () => console.error('IndexedDB failed');
            request.onsuccess = (e) => {
                this.db = e.target.result;
            };
        }
    }
    
    // Save note offline
    async saveNoteOffline(note) {
        if (!this.db) return false;
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('notes', 'readwrite');
            const store = tx.objectStore('notes');
            const request = store.put({
                ...note,
                synced: false,
                lastModified: new Date().toISOString()
            });
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(true);
        });
    }
    
    // Get note offline
    async getNoteOffline(noteId) {
        if (!this.db) return null;
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('notes', 'readonly');
            const store = tx.objectStore('notes');
            const request = store.get(noteId);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }
    
    // Get all notes offline
    async getAllNotesOffline(userId) {
        if (!this.db) return [];
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('notes', 'readonly');
            const store = tx.objectStore('notes');
            const index = store.index('user_id');
            const request = index.getAll(userId);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }
    
    // Delete note offline
    async deleteNoteOffline(noteId) {
        if (!this.db) return false;
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('notes', 'readwrite');
            const store = tx.objectStore('notes');
            const request = store.delete(noteId);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(true);
        });
    }
    
    // Queue sync item
    async queueSync(data) {
        if (!this.db) return false;
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('syncQueue', 'readwrite');
            const store = tx.objectStore('syncQueue');
            const request = store.add(data);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }
    
    // Get sync queue
    async getSyncQueue() {
        if (!this.db) return [];
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('syncQueue', 'readonly');
            const store = tx.objectStore('syncQueue');
            const request = store.getAll();
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);
        });
    }
    
    // Clear sync queue item
    async clearSyncItem(id) {
        if (!this.db) return false;
        
        return new Promise((resolve, reject) => {
            const tx = this.db.transaction('syncQueue', 'readwrite');
            const store = tx.objectStore('syncQueue');
            const request = store.delete(id);
            
            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(true);
        });
    }
}

// Global instance
window.offlineDB = new OfflineDB();
