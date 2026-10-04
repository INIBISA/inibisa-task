import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { createIcons, Archive, ArrowRight, CalendarDays, CircleCheck, Funnel, Inbox, Layers3, Lightbulb, ListTodo, Package, Pencil, Plus, Search, Trash2, UserRound, UsersRound, UserX } from 'lucide';

window.Alpine = Alpine;

Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
    },
});

Alpine.start();

createIcons({ icons: { Archive, ArrowRight, CalendarDays, CircleCheck, Funnel, Inbox, Layers3, Lightbulb, ListTodo, Package, Pencil, Plus, Search, Trash2, UserRound, UsersRound, UserX }, attrs: { 'stroke-width': 1.8 } });

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirmTitle || form.dataset.confirmed) return;

    event.preventDefault();
    const destructive = form.dataset.confirmKind === 'danger';
    const result = await Swal.fire({
        target: form.closest('dialog') || document.body,
        title: form.dataset.confirmTitle,
        text: form.dataset.confirmText || '',
        icon: destructive ? 'warning' : 'question',
        showCancelButton: true,
        confirmButtonText: destructive ? 'Ya, lanjutkan' : 'Ya, simpan',
        cancelButtonText: 'Batal',
        confirmButtonColor: destructive ? '#be123c' : '#2563eb',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        background: document.documentElement.classList.contains('dark') ? '#17232d' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
    });
    if (result.isConfirmed) {
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    }
});

const flash = document.getElementById('workspace-flash');
if (flash?.dataset.message) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: flash.dataset.type,
        title: flash.dataset.message,
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        background: document.documentElement.classList.contains('dark') ? '#17232d' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
    });
}

const initTaskBoard = () => {
    const zones = document.querySelectorAll('[data-dropzone]');
    if (!zones.length) return;

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let isSorting = false;

    document.querySelectorAll('[data-dialog-target]').forEach((card) => {
        card.addEventListener('click', (event) => {
            if (isSorting || event.target.closest('.drag-handle')) return;
            document.getElementById(card.dataset.dialogTarget)?.showModal();
        });
    });

    zones.forEach((zone) => {
        Sortable.create(zone, {
            group: 'tasks',
            draggable: '.task-card',
            animation: 180,
            ghostClass: 'task-card-ghost',
            chosenClass: 'task-card-chosen',
            dragClass: 'task-card-dragging',
            forceFallback: true,
            fallbackOnBody: true,
            swapThreshold: 0.65,
            emptyInsertThreshold: 32,
            onStart() {
                isSorting = true;
            },
            onMove(event) {
                zones.forEach((dropzone) => dropzone.classList.remove('task-dropzone-active'));
                event.to?.classList.add('task-dropzone-active');
                return true;
            },
            async onEnd(event) {
                zones.forEach((dropzone) => dropzone.classList.remove('task-dropzone-active'));
                window.setTimeout(() => { isSorting = false; }, 0);

                const card = event.item;
                const status = event.to?.dataset.dropzone;
                if (!card?.dataset.updateUrl || !token || !status) return;

                const response = await fetch(card.dataset.updateUrl, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ status }),
                });

                if (response.ok) {
                    location.reload();
                    return;
                }

                event.from.insertBefore(card, event.from.children[event.oldIndex] || null);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal memindahkan tugas',
                    text: 'Coba lagi atau cek hak akses tugas ini.',
                    confirmButtonColor: '#2563eb',
                    background: document.documentElement.classList.contains('dark') ? '#17232d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#e2e8f0' : '#1e293b',
                });
            },
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTaskBoard);
} else {
    initTaskBoard();
}
