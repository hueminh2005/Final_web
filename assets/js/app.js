// Main Application JavaScript

const API_BASE = window.location.origin;
let saveTimeout;
const AUTOSAVE_DELAY = 300; // milliseconds

// Note Management
class NoteManager {
    constructor() {
        this.currentNoteId = null;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.loadLabels();
    }
    
    setupEventListeners() {
        // Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => this.search(e.target.value), 300));
        }
        
        // View toggle
        const gridViewBtn = document.getElementById('gridViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        
        if (gridViewBtn) gridViewBtn.addEventListener('click', () => this.switchView('grid'));
        if (listViewBtn) listViewBtn.addEventListener('click', () => this.switchView('list'));
        
        // Pin/Unpin
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-pin-toggle')) {
                const noteCard = e.target.closest('.note-card');
                this.togglePin(noteCard.dataset.noteId, e.target.dataset.pinned === '0');
            }
            
            // Delete
            if (e.target.classList.contains('btn-delete')) {
                const noteCard = e.target.closest('.note-card');
                this.deleteNote(noteCard.dataset.noteId);
            }
        });
        
        // Theme toggle
        const themeToggle = document.getElementById('themeToggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', () => this.toggleTheme());
        }
    }
    
    debounce(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }
    
    async search(query) {
        try {
            const response = await fetch(`/ajax/search-note.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success) {
                this.displayNotes(data.notes);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }
    
    displayNotes(notes) {
        const container = document.getElementById('notesContainer');
        if (!notes || notes.length === 0) {
            container.innerHTML = '<div class="empty-state"><p>No notes found</p></div>';
            return;
        }
        
        container.innerHTML = notes.map(note => this.createNoteCard(note)).join('');
    }
    
    createNoteCard(note) {
        const icons = [];
        if (note.is_pinned) icons.push('📌');
        if (note.is_password_protected) icons.push('🔒');
        
        return `
            <div class="note-card" data-note-id="${note.id}">
                <div class="note-header">
                    <h3>${this.escapeHtml(note.title)}</h3>
                    <div class="note-icons">${icons.join('')}</div>
                </div>
                <p class="note-preview">${this.escapeHtml(note.content.substring(0, 100))}...</p>
                <div class="note-labels">
                    ${note.labels?.map(label => `<span class="label" style="background-color: ${label.color}">${this.escapeHtml(label.name)}</span>`).join('') || ''}
                </div>
                <div class="note-meta">
                    <small>${new Date(note.updated_at).toLocaleDateString()}</small>
                </div>
                <div class="note-actions">
                    <a href="editor.php?id=${note.id}" class="btn-small">Edit</a>
                    <button class="btn-small btn-pin-toggle" data-pinned="${note.is_pinned ? 1 : 0}">
                        ${note.is_pinned ? 'Unpin' : 'Pin'}
                    </button>
                    <button class="btn-small btn-delete">Delete</button>
                </div>
            </div>
        `;
    }
    
    switchView(view) {
        const container = document.getElementById('notesContainer');
        const gridBtn = document.getElementById('gridViewBtn');
        const listBtn = document.getElementById('listViewBtn');
        
        container.className = `notes-${view}`;
        
        if (view === 'grid') {
            gridBtn?.classList.add('active');
            listBtn?.classList.remove('active');
        } else {
            gridBtn?.classList.remove('active');
            listBtn?.classList.add('active');
        }
        
        localStorage.setItem('noteViewPreference', view);
    }
    
    async togglePin(noteId, isPinned) {
        try {
            const formData = new FormData();
            formData.append('note_id', noteId);
            formData.append('is_pinned', isPinned ? 1 : 0);
            
            const response = await fetch('/ajax/pin-note.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                location.reload();
            }
        } catch (error) {
            console.error('Pin error:', error);
        }
    }
    
    async deleteNote(noteId) {
        if (!confirm('Are you sure you want to delete this note?')) return;
        
        try {
            const formData = new FormData();
            formData.append('note_id', noteId);
            
            const response = await fetch('/ajax/delete-note.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            if (data.success) {
                document.querySelector(`[data-note-id="${noteId}"]`).remove();
            }
        } catch (error) {
            console.error('Delete error:', error);
        }
    }
    
    loadLabels() {
        // Load labels from API or fetch
    }
    
    toggleTheme() {
        const currentTheme = document.body.classList.contains('theme-dark') ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        document.body.classList.remove(`theme-${currentTheme}`);
        document.body.classList.add(`theme-${newTheme}`);
        
        localStorage.setItem('theme', newTheme);
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const noteManager = new NoteManager();
    
    // Restore theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.classList.remove('theme-light', 'theme-dark');
    document.body.classList.add(`theme-${savedTheme}`);
});
