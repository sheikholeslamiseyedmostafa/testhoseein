/*
 * Service Worker - نرم‌افزار حسابداری (PWA)
 *
 * سیاست کش:
 *  - فایل‌های استاتیک (CSS، JS، فونت، آیکون) : Cache-First برای سرعت و کار آفلاین
 *  - صفحات PHP (داده‌های مالی و نشست کاربر) : هرگز در کش ذخیره نمی‌شوند (Network-Only)
 *  - ناوبری بدون اینترنت : نمایش صفحه offline.html
 *
 * برای انتشار نسخه جدید، مقدار CACHE_VERSION را تغییر دهید.
 */
const CACHE_VERSION = 'v4';
const CACHE_NAME = `accounting-static-${CACHE_VERSION}`;

const PRECACHE_URLS = [
  './offline.html',
  './manifest.webmanifest',
  './assets/css/app.css',
  './assets/js/app.js',
  './assets/fonts/Vazirmatn-Variable.woff2',
  './assets/icons/icon-192.png',
  './assets/icons/icon-512.png',
  './assets/icons/icon-maskable-512.png',
  './assets/icons/apple-touch-icon.png'
];

const STATIC_PATH = /\/assets\/|\/manifest\.webmanifest$|\/offline\.html$/;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys.filter((key) => key.startsWith('accounting-static-') && key !== CACHE_NAME)
          .map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') {
    return;
  }

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) {
    return;
  }

  // ناوبری صفحات: همیشه از شبکه؛ در نبود اینترنت صفحه آفلاین
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request).catch(() =>
        caches.match(new URL('./offline.html', self.registration.scope).href)
          .then((response) => response || Response.error())
      )
    );
    return;
  }

  if (STATIC_PATH.test(url.pathname)) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) {
          return cached;
        }
        return fetch(request).then((response) => {
          if (response && response.ok) {
            const copy = response.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
          }
          return response;
        });
      })
    );
  }
});
