import './bootstrap';

import Alpine from 'alpinejs';
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
