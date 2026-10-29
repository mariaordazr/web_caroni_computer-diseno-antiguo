const CACHE_NAME = 'caroni-admin-v1';

self.addEventListener('install', (event) => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(clients.claim());
});

self.addEventListener('fetch', (event) => {
  if (event.request.method === 'POST' || event.request.url.includes('/admin/')) {
      return; // Retornar vacío hace que el navegador maneje la petición normalmente sin caché
  }
  // Ampliamos para que intercepte /admin/ y /auth/
  if (event.request.url.includes('/admin/') || event.request.url.includes('/auth/')) {
    event.respondWith(
      fetch(event.request).catch(function() {
        return caches.match(event.request);
      })
    );
  }
});