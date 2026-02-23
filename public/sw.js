const CACHE_NAME = 'medical-ecommerce-v2';
// لا نخزن الصفحة الرئيسية '/' حتى لا نقدم نسخة قديمة من البيانات (أقسام/منتجات/سلايدر)
const urlsToCache = [
  '/front/theme1/css/bootstrap.min.css',
  '/front/theme1/css/demo3.min.css',
  '/front/theme1/js/jquery.min.js',
  '/front/theme1/js/bootstrap.bundle.min.js',
  '/front/theme1/js/main.min.js',
];

// Install event - cache resources
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});

// Fetch event - للصفحات نفضل الشبكة دائماً (بيانات Inertia محدثة)، للملفات الثابتة من الكاش
self.addEventListener('fetch', (event) => {
  const isDocument = event.request.destination === 'document' || event.request.mode === 'navigate';

  event.respondWith(
    (async () => {
      try {
        // الصفحات (HTML/Inertia): دائماً من الشبكة أولاً حتى تكون البيانات محدثة
        if (isDocument) {
          const netRes = await fetch(event.request);
          return netRes;
        }

        const cached = await caches.match(event.request);
        if (cached) return cached;

        const netRes = await fetch(event.request);
        return netRes;
      } catch (err) {
        // يجب إرجاع Response صالح دائماً وإلا يظهر: Failed to convert value to 'Response'
        if (event.request.destination === 'image') {
          return new Response('', { status: 404, statusText: 'Not Found', headers: { 'Content-Type': 'image/gif' } });
        }
        if (isDocument) {
          const fallback = await caches.match('/');
          if (fallback) return fallback;
        }
        return new Response('', { status: 404, statusText: 'Not Found' });
      }
    })()
  );
});

