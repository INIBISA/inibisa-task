import './bootstrap';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { createIcons, Archive, ArrowRight, AtSign, CalendarDays, CircleCheck, Download, Funnel, House, Inbox, Layers3, Lightbulb, ListTodo, Menu, Package, Pencil, Plus, Search, Settings2, Trash2, UserRound, UsersRound, UserX } from 'lucide';

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

createIcons({ icons: { Archive, ArrowRight, AtSign, CalendarDays, CircleCheck, Download, Funnel, House, Inbox, Layers3, Lightbulb, ListTodo, Menu, Package, Pencil, Plus, Search, Settings2, Trash2, UserRound, UsersRound, UserX }, attrs: { 'stroke-width': 1.8 } });

if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).catch(() => {});
    });
}

const installButtons = [...document.querySelectorAll('[data-install-app]')];
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
let installPrompt = null;
const updateInstallButtons = () => installButtons.forEach((button) => { button.hidden = isStandalone(); });

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    updateInstallButtons();
});
window.addEventListener('appinstalled', () => {
    installPrompt = null;
    installButtons.forEach((button) => { button.hidden = true; });
});
updateInstallButtons();

installButtons.forEach((button) => button.addEventListener('click', async () => {
    if (installPrompt) {
        const prompt = installPrompt;
        installPrompt = null;
        try {
            await prompt.prompt();
            await prompt.userChoice;
            return;
        } catch {
            // If the browser no longer accepts this prompt, show installation guidance.
        }
    }

    const ios = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const message = !window.isSecureContext
        ? 'Buka IniBisa melalui alamat HTTPS di HP. Alamat HTTP dengan IP lokal belum memenuhi syarat instalasi PWA.'
        : ios
            ? 'Di Safari, ketuk tombol Bagikan lalu pilih Tambahkan ke Layar Utama.'
            : 'Buka menu browser lalu pilih Instal aplikasi atau Tambahkan ke layar utama. Jika pilihan belum muncul, buka ulang halaman setelah beberapa saat.';

    Swal.fire({ icon: 'info', title: 'Pasang IniBisa', text: message, confirmButtonText: 'Mengerti', confirmButtonColor: '#2563eb' });
}));

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

    const board = document.querySelector('.task-board');
    const tabs = [...document.querySelectorAll('[data-status-tab]')];
    const columns = [...document.querySelectorAll('[data-mobile-status]')];
    const selectStatus = (status) => {
        tabs.forEach((tab) => {
            const active = tab.dataset.statusTab === status;
            tab.classList.toggle('mobile-status-tab-active', active);
            tab.setAttribute('aria-selected', String(active));
            if (active && window.matchMedia('(max-width: 1023px)').matches) {
                tab.scrollIntoView({ block: 'nearest', inline: 'center' });
            }
        });
        columns.forEach((column) => column.classList.toggle('mobile-status-active', column.dataset.mobileStatus === status));
    };
    if (board && tabs.length) {
        selectStatus(board.dataset.initialStatus);
        board.classList.add('task-board-enhanced');
        tabs.forEach((tab) => tab.addEventListener('click', () => selectStatus(tab.dataset.statusTab)));
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    let isSorting = false;

    document.querySelectorAll('[data-dialog-target]').forEach((card) => {
        card.addEventListener('click', (event) => {
            if (isSorting || event.target.closest('.drag-handle')) return;
            document.getElementById(card.dataset.dialogTarget)?.showModal();
        });
        card.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            document.getElementById(card.dataset.dialogTarget)?.showModal();
        });
    });

    const desktop = window.matchMedia('(min-width: 1024px)');
    const sortables = [];
    zones.forEach((zone) => {
        sortables.push(Sortable.create(zone, {
            group: 'tasks',
            draggable: '.task-card',
            handle: '.drag-handle',
            disabled: !desktop.matches,
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

                try {
                    const response = await fetch(card.dataset.updateUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ status }),
                    });
                    if (response.ok) {
                        location.reload();
                        return;
                    }
                } catch {
                    // Restore the card and show the same actionable error below.
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
        }));
    });
    desktop.addEventListener('change', (event) => sortables.forEach((sortable) => sortable.option('disabled', !event.matches)));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTaskBoard);
} else {
    initTaskBoard();
}
