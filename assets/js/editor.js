// Note Editor JavaScript - Fixed version

class NoteEditor {
    constructor() {
        this.noteId = document.getElementById('noteId')?.value || null;
        this.isDirty = false;
        this.basePath = this.getBasePath();
        this.init();
    }

    getBasePath() {
        const path = window.location.pathname;
        const parts = path.split('/');
        parts.pop();
        return parts.join('/');
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        const titleInput = document.getElementById('noteTitle');
        const editor = document.getElementById('editor');
        const saveBtn = document.getElementById('saveBtn');
        const deleteBtn = document.getElementById('deleteBtn');
        const passwordProtect = document.getElementById('passwordProtect');
        const noteColor = document.getElementById('noteColor');
        const noteColorPreview = document.getElementById('noteColorPreview');

        const imageInput = document.getElementById('imageInput');

        if (titleInput) titleInput.addEventListener('input', () => { this.isDirty = true; this.updateSaveStatus('unsaved'); this.autoSave(); });
        if (editor) editor.addEventListener('input', () => { this.isDirty = true; this.updateSaveStatus('unsaved'); this.autoSave(); });
        if (imageInput) imageInput.addEventListener('change', (event) => this.handleImageSelection(event));
        if (saveBtn) saveBtn.addEventListener('click', () => this.saveNote());
        if (deleteBtn) deleteBtn.addEventListener('click', () => this.deleteNote());
        if (passwordProtect) {
            passwordProtect.addEventListener('change', () => {
                const options = document.getElementById('passwordProtectOptions');
                if (options) options.style.display = passwordProtect.checked ? 'block' : 'none';
            });
        }
        if (noteColor) {
            noteColor.addEventListener('change', () => {
                if (noteColorPreview) {
                    noteColorPreview.style.backgroundColor = noteColor.value;
                }
                const editor = document.getElementById('editor');
                if (editor) {
                    editor.style.backgroundColor = noteColor.value;
                }
                this.isDirty = true;
                this.updateSaveStatus('unsaved');
                this.autoSave();
            });
        }

        document.querySelectorAll('.toolbar-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleToolbarAction(e));
        });
    }

    updateSaveStatus(status) {
        const statusEl = document.getElementById('saveStatus');
        if (!statusEl) return;
        statusEl.classList.remove('saving', 'saved', 'unsaved');
        if (status === 'saving') { statusEl.textContent = 'Đang lưu...'; statusEl.classList.add('saving'); }
        else if (status === 'unsaved') { statusEl.textContent = 'Chưa lưu'; statusEl.classList.add('unsaved'); }
        else if (status === 'saved') { statusEl.textContent = 'Đã lưu ✓'; statusEl.classList.add('saved'); }
    }

    autoSave() {
        clearTimeout(window.autoSaveTimeout);
        window.autoSaveTimeout = setTimeout(() => {
            if (this.isDirty && this.noteId) this.saveNote(true);
        }, 1500);
    }

    async saveNote(isAutoSave = false) {
        const title = document.getElementById('noteTitle')?.value?.trim() || '';
        const content = document.getElementById('editor')?.innerHTML || '';

        if (!title) { if (!isAutoSave) alert('Vui lòng nhập tiêu đề!'); return; }
        if (!content || content.trim() === '<br>' || content.trim() === '') { if (!isAutoSave) alert('Vui lòng nhập nội dung!'); return; }

        this.updateSaveStatus('saving');

        try {
            const formData = new FormData();
            formData.append('title', title);
            formData.append('content', content);
            
            const noteColor = document.getElementById('noteColor');
            if (noteColor) {
                formData.append('note_color', noteColor.value);
            }

            let url;
            if (this.noteId) {
                url = this.basePath + '/ajax/autosave-note.php';
                formData.append('note_id', this.noteId);
            } else {
                url = this.basePath + '/ajax/create-note.php';
            }

            const response = await fetch(url, { method: 'POST', body: formData });
            if (!response.ok) throw new Error('HTTP ' + response.status);

            const data = await response.json();

            if (data.success) {
                if (!this.noteId && data.note_id) {
                    this.noteId = data.note_id;
                    window.history.replaceState({}, '', 'editor.php?id=' + this.noteId);
                    const noteIdInput = document.getElementById('noteId');
                    if (noteIdInput) noteIdInput.value = this.noteId;
                    const deleteBtn = document.getElementById('deleteBtn');
                    if (deleteBtn) deleteBtn.style.display = 'inline-block';
                }
                this.isDirty = false;
                this.updateSaveStatus('saved');
                if (!isAutoSave) this.showToast('Lưu thành công!');
            } else {
                this.updateSaveStatus('unsaved');
                if (!isAutoSave) alert('Lưu thất bại: ' + (data.error || 'Lỗi không xác định'));
            }
        } catch (error) {
            console.error('Save error:', error);
            this.updateSaveStatus('unsaved');
            if (!isAutoSave) alert('Lỗi khi lưu: ' + error.message);
        }
    }

    async deleteNote() {
        if (!this.noteId || !confirm('Xoá ghi chú này?')) return;
        try {
            const formData = new FormData();
            formData.append('note_id', this.noteId);
            const response = await fetch(this.basePath + '/ajax/delete-note.php', { method: 'POST', body: formData });
            const data = await response.json();
            if (data.success) window.location.href = this.basePath + '/index.php';
            else alert('Xoá thất bại');
        } catch (error) { alert('Lỗi khi xoá'); }
    }

    async handleImageSelection(event) {
        const file = event.target.files?.[0];
        if (!file) return;
        await this.uploadImage(file);
        event.target.value = '';
    }

    async uploadImage(file) {
        const formData = new FormData();
        formData.append('image', file);
        if (this.noteId) {
            formData.append('note_id', this.noteId);
        }

        this.showToast('Đang tải ảnh lên...', 'success');

        try {
            const response = await fetch(this.basePath + '/ajax/upload-image.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Không thể upload ảnh');
            }

            this.insertImage(data.url);
            this.showToast('Ảnh đã được thêm vào ghi chú');
        } catch (error) {
            console.error('Image upload error:', error);
            this.showToast('Upload ảnh thất bại', 'error');
        }
    }

    insertImage(url) {
        const editor = document.getElementById('editor');
        if (!editor) return;
        const imageHtml = `<img src="${url}" class="editor-image" alt="Note Image">`;
        document.execCommand('insertHTML', false, imageHtml);
        editor.focus();
    }

    handleToolbarAction(e) {
        e.preventDefault();
        const action = e.currentTarget.dataset.action;
        if (action === 'insertImage') {
            document.getElementById('imageInput')?.click();
        } else {
            document.execCommand(action, false, null);
            document.getElementById('editor')?.focus();
        }
    }

    showToast(msg, type = 'success') {
        const toast = document.createElement('div');
        toast.textContent = msg;
        toast.style.cssText = 'position:fixed;bottom:2rem;left:50%;transform:translateX(-50%);background:' + (type === 'success' ? '#10b981' : '#ef4444') + ';color:white;padding:0.75rem 1.5rem;border-radius:8px;font-weight:600;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,0.2);';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => { new NoteEditor(); });
