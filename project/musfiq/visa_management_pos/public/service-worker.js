// Cache এর একটি নাম দেওয়া হলো
const CACHE_NAME = "visa-pos-cache-v1";

// যেসব ফাইল বা লিংক আমরা অফলাইনের জন্য সেভ (Cache) করে রাখতে চাই
const STATIC_ASSETS = [
  "./", // মূল ড্যাশবোর্ড (index.php)
  "../app/views/auth/login.php", // লগিন পেজ
  "https://cdn.tailwindcss.com", // Tailwind CSS
  "https://code.jquery.com/jquery-3.7.1.min.js", // jQuery
  "https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css", // DataTables CSS
  "https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js", // DataTables JS
];

// ১. Install Event: প্রথমবার সাইট লোড হলে ফাইলগুলো ক্যাশ করে রাখবে
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log("Service Worker: Caching Files");
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// ২. Activate Event: পুরানো ক্যাশ ডিলিট করে নতুন ক্যাশ আপডেট করবে
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log("Service Worker: Clearing Old Cache");
            return caches.delete(cache);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// ৩. Fetch Event: ইন্টারনেট থাকলে নেটওয়ার্ক থেকে আনবে, না থাকলে ক্যাশ থেকে আনবে (Network First Strategy)
self.addEventListener("fetch", (event) => {
  // শুধুমাত্র GET রিকোয়েস্ট (পেজ লোড) ক্যাশ করবে, POST (ডাটা সাবমিট) ক্যাশ করবে না
  if (event.request.method !== "GET") return;

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        // ইন্টারনেট থাকলে নতুন ডাটা ক্যাশে সেভ করে রাখবে
        return caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, networkResponse.clone());
          return networkResponse;
        });
      })
      .catch(() => {
        // ইন্টারনেট বা কারেন্ট না থাকলে ক্যাশ থেকে পেজ দেখাবে
        return caches.match(event.request);
      })
  );
});
