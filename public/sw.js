const CACHE = 'inibisa-static-v3';
const APP_SHELL = ['/offline.html', '/icons/pwa-192.png', '/icons/pwa-512.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(APP_SHELL)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(Promise.all([
        caches.keys().then((keys) => Promise.all(keys.filter((key) => key.startsWith('inibisa-static-') && key !== CACHE).map((key) => caches.delete(key)))),
        self.clients.claim(),
    ]));
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match('/offline.html')));
        return;
    }

    // Only cache versioned frontend files and public icons. Authenticated HTML and API data stay on the network.
    if (!url.pathname.startsWith('/build/assets/') && !url.pathname.startsWith('/icons/')) return;

    event.respondWith(caches.match(request).then((cached) => cached || fetch(request).then((response) => {
        if (response.ok && response.type === 'basic') {
            const copy = response.clone();
            caches.open(CACHE).then((cache) => cache.put(request, copy));
        }
        return response;
    })));
});

self.addEventListener('push', (event) => {
    let payload = {};
    try { payload = event.data?.json() || {}; } catch { /* Show a generic message for malformed payloads. */ }
    const url = new URL(payload.url || '/tasks', self.location.origin);
    if (url.origin !== self.location.origin || url.pathname !== '/tasks') url.href = `${self.location.origin}/tasks`;

    event.waitUntil(Promise.all([
        self.registration.showNotification(payload.title || 'IniBisa', {
            body: payload.body || 'Ada pembaruan tugas.',
            icon: '/icons/pwa-192.png',
            badge: '/icons/favicon-32.png',
            tag: payload.tag || 'inibisa-task',
            data: { url: url.href },
        }),
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => clients.forEach((client) => client.postMessage({ type: 'push-received', sound: payload.sound }))),
    ]));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || `${self.location.origin}/tasks`;
    event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(async (clients) => {
        const existing = clients.find((client) => new URL(client.url).origin === self.location.origin);
        if (existing) {
            await existing.navigate(url);
            return existing.focus();
        }
        return self.clients.openWindow(url);
    }));
});
