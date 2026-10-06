import './bootstrap';
import * as Turbo from '@hotwired/turbo';

import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import { createIcons, Archive, ArrowRight, AtSign, Bell, BriefcaseBusiness, CalendarDays, Camera, CircleCheck, Download, Funnel, House, ImagePlus, Inbox, Layers3, Lightbulb, ListTodo, Menu, Music2, Package, Pencil, Plus, Reply, Search, Send, Settings2, ThumbsUp, Trash2, UserRound, UsersRound, UserX, Video } from 'lucide';
import { initPushNotifications } from './push-notifications';

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

const lucideIcons = { Archive, ArrowRight, AtSign, Bell, BriefcaseBusiness, CalendarDays, Camera, CircleCheck, Download, Funnel, House, ImagePlus, Inbox, Layers3, Lightbulb, ListTodo, Menu, Music2, Package, Pencil, Plus, Reply, Search, Send, Settings2, ThumbsUp, Trash2, UserRound, UsersRound, UserX, Video };
if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost' || location.hostname === '127.0.0.1')) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).catch(() => {});
    });
}

const commentToken = document.querySelector('meta[name="csrf-token"]')?.content;
const pending = (element, active) => {
    if (!element) return;
    element.classList.toggle('is-loading', active);
    element.setAttribute('aria-busy', String(active));
};
const pendingButton = (button, active) => {
    if (!button) return;
    button.disabled = active;
    button.classList.toggle('is-loading', active);
    button.setAttribute('aria-busy', String(active));
    let spinner = button.querySelector('.loading-spinner');
    if (active && !spinner) button.insertAdjacentHTML('afterbegin', '<span class="loading-spinner" aria-hidden="true"></span>');
    if (!active) spinner?.remove();
};
const pageLoading = (active) => document.querySelector('[data-page-loading]')?.classList.toggle('is-loading', active);
const replyForm = (parentId) => `<form class="task-comment-form mt-3" data-comment-form enctype="multipart/form-data"><input type="hidden" name="parent_id" value="${parentId}"><label class="sr-only">Tulis balasan</label><textarea class="field min-h-20 w-full resize-y" name="body" maxlength="5000" placeholder="Tulis balasan..." data-comment-body></textarea><div class="mt-2 flex items-center justify-between gap-3"><label class="action-button cursor-pointer"><i data-lucide="image-plus"></i> Gambar<input class="sr-only" type="file" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-comment-images></label><div class="flex gap-2"><button type="button" class="action-button" data-cancel-reply>Batal</button><button class="brand-button min-h-8 px-3" type="submit"><i data-lucide="send" class="h-3.5 w-3.5"></i> Balas</button></div></div><div class="task-comment-previews" data-comment-previews></div><p class="mt-2 text-xs text-rose-600" data-comment-error aria-live="polite"></p></form>`;

const previewCommentImages = (input) => {
    const previews = input.closest('[data-comment-form]')?.querySelector('[data-comment-previews]');
    if (!previews) return;
    previews.replaceChildren(...[...input.files].slice(0, 4).map((file) => {
        const image = document.createElement('img');
        image.src = URL.createObjectURL(file);
        image.alt = file.name;
        image.onload = () => URL.revokeObjectURL(image.src);
        return image;
    }));
};

document.addEventListener('click', (event) => {
    const reply = event.target.closest('[data-reply-to]');
    if (reply) {
        const article = reply.closest('[data-comment-id]');
        const composer = article?.querySelector('[data-reply-composer]');
        if (!composer || composer.children.length) return;
        composer.innerHTML = replyForm(reply.dataset.replyTo);
        createIcons({ icons: lucideIcons, attrs: { 'stroke-width': 1.8 } });
        composer.querySelector('[data-comment-body]')?.focus();
        return;
    }

    const cancel = event.target.closest('[data-cancel-reply]');
    if (cancel) cancel.closest('[data-comment-form]')?.remove();
});

document.addEventListener('change', (event) => {
    const input = event.target.closest('[data-comment-images]');
    if (input) previewCommentImages(input);
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-comment-form]');
    if (!form) return;
    event.preventDefault();

    const dialog = form.closest('dialog');
    const url = form.dataset.commentUrl || dialog?.querySelector('[data-comment-form][data-comment-url]')?.dataset.commentUrl;
    const body = form.querySelector('[data-comment-body]');
    const error = form.querySelector('[data-comment-error]');
    const submit = form.querySelector('[type="submit"]');
    if (!url || !body || !commentToken) return;

    error.textContent = '';
    pendingButton(submit, true);
    pending(form, true);
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': commentToken },
            body: new FormData(form),
        });
        const payload = await response.json();
        if (!response.ok) {
            error.textContent = payload.errors?.body?.[0] || 'Komentar gagal dikirim.';
            return;
        }

        const template = document.createElement('template');
        template.innerHTML = payload.html.trim();
        const comment = template.content.firstElementChild;
        const list = dialog?.querySelector('[data-comment-list]');
        if (!comment || !list) return;
        list.querySelector('[data-comments-empty]')?.remove();
        if (payload.parent_id) {
            const parent = list.querySelector(`[data-comment-id="${payload.parent_id}"]`);
            parent?.querySelector('[data-comment-children]')?.append(comment);
        } else {
            list.append(comment);
        }
        const count = dialog?.querySelector('[data-comment-count]');
        if (count) count.textContent = String(Number(count.textContent) + 1);
        form.closest('[data-reply-composer]') ? form.closest('[data-reply-composer]').innerHTML = '' : form.reset();
        createIcons({ icons: lucideIcons, attrs: { 'stroke-width': 1.8 } });
        comment.focus();
    } catch {
        error.textContent = 'Koneksi bermasalah. Coba lagi.';
    } finally {
        pendingButton(submit, false);
        pending(form, false);
    }
});

const installButtons = [...document.querySelectorAll('[data-install-app]')];
const installOffer = document.querySelector('[data-install-offer]');
const isStandalone = () => window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
let installPrompt = null;
let installOfferTimer = null;
const updateInstallButtons = () => installButtons.forEach((button) => { button.hidden = isStandalone(); });

const dismissInstallOffer = () => {
    if (installOffer) installOffer.hidden = true;
    sessionStorage.setItem('inibisa-install-offer-shown', '1');
};

const scheduleInstallOffer = () => {
    if (!installOffer || !installPrompt || isStandalone() || installOfferTimer || sessionStorage.getItem('inibisa-install-offer-shown')) return;
    installOfferTimer = window.setTimeout(() => {
        installOfferTimer = null;
        if (document.visibilityState !== 'visible' || !installPrompt || isStandalone()) return;
        installOffer.hidden = false;
        sessionStorage.setItem('inibisa-install-offer-shown', '1');
    }, 1500);
};

const showInstallGuide = () => {
    const ios = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    const message = !window.isSecureContext
        ? 'Buka IniBisa melalui alamat HTTPS di HP. Alamat HTTP dengan IP lokal belum memenuhi syarat instalasi PWA.'
        : ios
            ? 'Di Safari, ketuk tombol Bagikan lalu pilih Tambahkan ke Layar Utama.'
            : window.matchMedia('(min-width: 1024px)').matches
                ? 'Klik ikon instal (monitor dengan panah turun) di sisi kanan address bar Chrome. Jika belum terlihat, buka ulang halaman setelah beberapa saat.'
                : 'Buka menu browser lalu pilih Instal aplikasi atau Tambahkan ke layar utama. Jika pilihan belum muncul, buka ulang halaman setelah beberapa saat.';

    return Swal.fire({ icon: 'info', title: 'Pasang IniBisa', text: message, confirmButtonText: 'Mengerti', confirmButtonColor: '#2563eb' });
};

const requestInstall = async () => {
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
    await showInstallGuide();
};

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    installPrompt = event;
    updateInstallButtons();
    scheduleInstallOffer();
});
window.addEventListener('appinstalled', () => {
    installPrompt = null;
    installButtons.forEach((button) => { button.hidden = true; });
    dismissInstallOffer();
});
document.addEventListener('visibilitychange', scheduleInstallOffer);
updateInstallButtons();

installButtons.forEach((button) => button.addEventListener('click', async () => {
    if (installOffer && !installOffer.hidden) dismissInstallOffer();
    await requestInstall();
}));

installOffer?.querySelectorAll('[data-install-offer-close]').forEach((button) => button.addEventListener('click', dismissInstallOffer));
installOffer?.querySelector('[data-install-offer-action]')?.addEventListener('click', async () => {
    dismissInstallOffer();
    await requestInstall();
});

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

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.dataset.submitting || (form.dataset.confirmTitle && !form.dataset.confirmed) || form.matches('[data-comment-form]')) return;
    form.dataset.submitting = 'true';
    pending(form, true);
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => {
        button.disabled = true;
        button.insertAdjacentHTML('afterbegin', '<span class="loading-spinner" aria-hidden="true"></span>');
    });
    pageLoading(true);
});

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
    const url = new URL(link.href, location.href);
    if (url.origin === location.origin && url.href !== location.href) pageLoading(true);
});
window.addEventListener('pageshow', () => pageLoading(false));

const showFlash = () => {
    const flash = document.getElementById('workspace-flash');
    if (!flash?.dataset.message) return;
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
};

const initTaskBoard = () => {
    const zones = document.querySelectorAll('[data-dropzone]');
    if (!zones.length || document.body.dataset.taskBoardInitialized) return;
    document.body.dataset.taskBoardInitialized = 'true';

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

    const target = new URLSearchParams(location.search);
    const taskId = target.get('task');
    const commentId = target.get('comment');
    const dialog = taskId && document.getElementById(`task-${taskId}`);
    if (dialog) {
        dialog.showModal();
        const comment = commentId && dialog.querySelector(`[data-comment-id="${CSS.escape(commentId)}"]`);
        if (comment) {
            comment.classList.add('task-comment-target');
            comment.scrollIntoView({ block: 'center' });
            comment.focus({ preventScroll: true });
        }
        target.delete('task');
        target.delete('comment');
        history.replaceState({}, '', `${location.pathname}${target.size ? `?${target}` : ''}`);
    }

    const desktop = window.matchMedia('(min-width: 1024px)');
    const sortables = [];
    let isSavingTask = false;
    zones.forEach((zone) => {
        sortables.push(Sortable.create(zone, {
            group: 'tasks',
            draggable: '.task-card',
            handle: '.drag-handle',
            disabled: !desktop.matches,
            animation: 140,
            ghostClass: 'task-card-ghost',
            chosenClass: 'task-card-chosen',
            dragClass: 'task-card-dragging',
            swapThreshold: 0.65,
            emptyInsertThreshold: 48,
            delayOnTouchOnly: true,
            fallbackTolerance: 4,
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
                if (event.from === event.to && event.oldIndex === event.newIndex) return;
                if (isSavingTask) {
                    event.from.insertBefore(card, event.from.children[event.oldIndex] || null);
                    return;
                }
                isSavingTask = true;
                pending(card, true);
                sortables.forEach((sortable) => sortable.option('disabled', true));

                let response;
                try {
                    response = await fetch(card.dataset.updateUrl, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ status }),
                    });
                } catch {
                    // Restore the card and show the same actionable error below.
                }
                if (response?.ok) {
                    Turbo.visit(location.href, { action: 'replace' });
                    return;
                }

                event.from.insertBefore(card, event.from.children[event.oldIndex] || null);
                isSavingTask = false;
                pending(card, false);
                sortables.forEach((sortable) => sortable.option('disabled', !desktop.matches));
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

const initPage = () => {
    pageLoading(false);
    createIcons({ icons: lucideIcons, attrs: { 'stroke-width': 1.8 } });
    initPushNotifications(Swal);
    initTaskBoard();
    showFlash();
};

document.addEventListener('turbo:load', initPage);
