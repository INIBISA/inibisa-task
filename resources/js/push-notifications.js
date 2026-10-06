const toUint8Array = (base64url) => {
    const padded = `${base64url}${'='.repeat((4 - base64url.length % 4) % 4)}`.replace(/-/g, '+').replace(/_/g, '/');
    return Uint8Array.from(atob(padded), (character) => character.charCodeAt(0));
};

export const initPushNotifications = (Swal) => {
    const buttons = [...document.querySelectorAll('[data-push-toggle]')];
    const testButtons = [...document.querySelectorAll('[data-push-test]')];
    if (!buttons.length) return;

    const status = document.querySelector('[data-push-status]');
    const publicKey = document.querySelector('meta[name="push-public-key"]')?.content;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const supported = Boolean(window.isSecureContext && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window);
    const isIos = /iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    let currentSubscription = null;
    let busy = false;

    const setStatus = (message, active = false) => {
        if (status) status.textContent = message;
        buttons.forEach((button) => {
            const label = button.querySelector('span') || button;
            if (label === button) button.textContent = active ? 'Matikan notifikasi' : 'Aktifkan notifikasi';
            else label.firstChild.textContent = active ? 'Matikan notifikasi' : 'Aktifkan notifikasi';
            button.disabled = busy;
            button.classList.toggle('is-loading', busy);
            button.setAttribute('aria-busy', String(busy));
        });
    };

    const request = async (method, endpoint) => {
        const response = await fetch('/push-subscriptions', {
            method,
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(method === 'POST' ? endpoint.toJSON() : { endpoint: endpoint.endpoint }),
        });
        if (!response.ok) throw new Error(`Server menolak langganan notifikasi (${response.status}).`);
    };

    const refresh = async () => {
        if (!publicKey) { setStatus('Notifikasi belum diaktifkan di server.'); return; }
        if (!supported) {
            setStatus(isIos ? 'Di iPhone/iPad, pasang IniBisa ke Layar Utama lalu buka aplikasinya untuk mengaktifkan notifikasi.' : 'Browser ini belum mendukung push notification atau halaman belum memakai HTTPS.');
            return;
        }
        if (Notification.permission === 'denied') {
            setStatus('Izin notifikasi diblokir. Izinkan notifikasi IniBisa melalui pengaturan browser.');
            return;
        }
        const registration = await navigator.serviceWorker.ready;
        currentSubscription = await registration.pushManager.getSubscription();
        if (currentSubscription && Notification.permission === 'granted') {
            await request('POST', currentSubscription);
            setStatus('Notifikasi aktif di perangkat ini.', true);
        } else {
            setStatus('Notifikasi belum aktif di perangkat ini.');
        }
    };

    buttons.forEach((button) => button.addEventListener('click', async () => {
        if (busy) return;
        if (!publicKey || !supported || Notification.permission === 'denied') {
            const message = !publicKey ? 'Admin perlu mengatur kunci VAPID di server.' : isIos && !supported ? 'Pasang IniBisa ke Layar Utama melalui Safari, lalu buka aplikasi dari ikon tersebut.' : Notification.permission === 'denied' ? 'Buka pengaturan situs di browser dan izinkan notifikasi untuk IniBisa.' : 'Gunakan browser yang mendukung push notification melalui HTTPS.';
            await Swal.fire({ icon: 'info', title: 'Notifikasi belum tersedia', text: message, confirmButtonColor: '#2563eb' });
            return;
        }

        busy = true;
        buttons.forEach((item) => { item.disabled = true; item.classList.add('is-loading'); item.setAttribute('aria-busy', 'true'); });
        try {
            // iOS requires the permission request to begin in the click gesture.
            if (!currentSubscription && Notification.permission !== 'granted') {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    setStatus('Izin notifikasi belum diberikan.');
                    return;
                }
            }
            const registration = await navigator.serviceWorker.ready;
            currentSubscription = await registration.pushManager.getSubscription();
            if (currentSubscription) {
                await request('DELETE', currentSubscription);
                await currentSubscription.unsubscribe();
                currentSubscription = null;
                setStatus('Notifikasi dimatikan di perangkat ini.');
            } else {
                currentSubscription = await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: toUint8Array(publicKey) });
                await request('POST', currentSubscription);
                setStatus('Notifikasi aktif di perangkat ini.', true);
                await Swal.fire({ icon: 'success', title: 'Notifikasi aktif', text: 'Anda akan menerima info tugas baru dan perubahan tugas di perangkat ini.', confirmButtonColor: '#2563eb' });
            }
        } catch (error) {
            setStatus('Gagal mengatur notifikasi. Coba lagi.');
            await Swal.fire({ icon: 'error', title: 'Notifikasi gagal', text: error.message, confirmButtonColor: '#2563eb' });
        } finally {
            busy = false;
            buttons.forEach((item) => { item.disabled = false; item.classList.remove('is-loading'); item.setAttribute('aria-busy', 'false'); });
        }
    }));

    if (supported && publicKey) refresh().catch(() => setStatus('Status notifikasi belum dapat diperiksa.'));
    else refresh();

    testButtons.forEach((button) => button.addEventListener('click', async () => {
        if (busy) return;
        busy = true;
        testButtons.forEach((item) => { item.disabled = true; item.classList.add('is-loading'); item.setAttribute('aria-busy', 'true'); });
        try {
            const response = await fetch('/push-subscriptions/test', { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Notifikasi tes gagal dikirim.');
            await Swal.fire({ icon: 'success', title: 'Notifikasi tes dikirim', text: 'Cek notifikasi di perangkat ini.', confirmButtonColor: '#2563eb' });
        } catch (error) {
            await Swal.fire({ icon: 'error', title: 'Notifikasi tes gagal', text: error.message, confirmButtonColor: '#2563eb' });
        } finally {
            busy = false;
            testButtons.forEach((item) => { item.disabled = false; item.classList.remove('is-loading'); item.setAttribute('aria-busy', 'false'); });
        }
    }));

    document.querySelectorAll('form[action$="/logout"]').forEach((form) => form.addEventListener('submit', async (event) => {
        if (!currentSubscription) return;
        event.preventDefault();
        try {
            await request('DELETE', currentSubscription);
            await currentSubscription.unsubscribe();
        } catch { /* Logout must still work if push is unavailable. */ }
        form.submit();
    }));
};
