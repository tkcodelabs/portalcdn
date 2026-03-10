/**
 * Service Worker — Correio do Norte PWA
 * Estratégia: Cache First para assets estáticos, Network First para páginas HTML
 */

const CACHE_NAME = 'cdn-v1';
const OFFLINE_URL = '/offline/';

// Assets para pré-cachear na instalação
const PRECACHE_ASSETS = [
    '/',
];

// =========================================================
// INSTALL: Pré-cachear recursos essenciais
// =========================================================
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => {
            return cache.addAll(PRECACHE_ASSETS);
        }).then(() => self.skipWaiting())
    );
});

// =========================================================
// ACTIVATE: Limpar caches antigos
// =========================================================
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames
                    .filter(name => name !== CACHE_NAME)
                    .map(name => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// =========================================================
// FETCH: Estratégia híbrida
// =========================================================
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Ignorar requisições admin e AJAX
    if (
        url.pathname.includes('/wp-admin') ||
        url.pathname.includes('/wp-json') ||
        url.pathname.includes('admin-ajax.php') ||
        request.method !== 'GET'
    ) {
        return;
    }

    // Assets estáticos (CSS, JS, imagens, fontes): Cache First
    if (
        url.pathname.match(/\.(css|js|woff2?|png|jpg|jpeg|gif|svg|ico|webp)$/)
    ) {
        event.respondWith(
            caches.match(request).then(cached => {
                if (cached) return cached;
                return fetch(request).then(response => {
                    if (!response || response.status !== 200) return response;
                    const toCache = response.clone();
                    caches.open(CACHE_NAME).then(cache => cache.put(request, toCache));
                    return response;
                });
            })
        );
        return;
    }

    // Páginas HTML: Network First com fallback de cache
    if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then(response => {
                    // Cachear a resposta para uso offline
                    if (response && response.status === 200) {
                        const toCache = response.clone();
                        caches.open(CACHE_NAME).then(cache => cache.put(request, toCache));
                    }
                    return response;
                })
                .catch(() => {
                    // Sem internet: tentar cache
                    return caches.match(request).then(cached => {
                        if (cached) return cached;
                        // Se não há cache, mostrar a home cacheada
                        return caches.match('/');
                    });
                })
        );
        return;
    }
});
