# ⌚ Watch Inventory — স্টাফ প্যানেল

## প্রজেক্ট সেটআপ (XAMPP / WAMP)

### ১. ফোল্ডার কপি করুন
```
C:\xampp\htdocs\watch-app\
```

### ২. ডাটাবেজ তৈরি করুন
phpMyAdmin খুলুন → SQL ট্যাবে `database.sql` ফাইলের কোড পেস্ট করে রান করুন।

### ৩. DB কনফিগ চেক করুন
`config/db.php` ফাইলে পাসওয়ার্ড ঠিক আছে কিনা দেখুন।

### ৪. প্রথম অ্যাডমিন তৈরি করুন
`admin/create_admin.php` ফাইলে:
- `SETUP_TOKEN` পরিবর্তন করুন
- `$new_username` এবং `$new_password` সেট করুন

তারপর ব্রাউজারে যান:
```
http://localhost/watch-app/admin/create_admin.php?token=আপনার_টোকেন
```
✅ অ্যাডমিন তৈরি হলে **এই ফাইলটি ডিলিট করুন**।

### ৫. সাইট চালু করুন
```
http://localhost/watch-app/           → স্টাফ প্যানেল
http://localhost/watch-app/admin/     → অ্যাডমিন প্যানেল
```

---

## ফাইল স্ট্রাকচার
```
watch-app/
├── index.php                  ← স্টাফ ফ্রন্টএন্ড
├── database.sql               ← ডাটাবেজ স্কিমা
├── README.md
├── config/
│   └── db.php                 ← DB কানেকশন
├── includes/
│   └── auth.php               ← Session / CSRF / Upload helper
├── assets/
│   ├── css/style.css
│   ├── js/script.js
│   └── uploads/               ← আপলোড করা ছবি (auto-created)
└── admin/
    ├── login.php
    ├── logout.php
    ├── dashboard.php
    ├── add_watch.php
    ├── edit_watch.php
    ├── process_watch.php      ← Add + Edit উভয় হ্যান্ডেল করে
    ├── delete_watch.php
    ├── delete_image.php
    └── create_admin.php       ← ✅ ব্যবহারের পর DELETE করুন
```

---

## v2.0 — পরিবর্তনের তালিকা

### 🔴 নিরাপত্তা সমস্যা সমাধান
- `process_watch.php` এ Auth চেক যোগ করা হয়েছে (আগে ছিল না!)
- `create_admin.php` এখন সিক্রেট টোকেন ছাড়া খোলা যাবে না
- সব DELETE অপারেশন GET → POST এ পরিবর্তন (CSRF প্রতিরোধ)
- সব POST ফর্মে CSRF টোকেন যাচাই
- ফাইল আপলোডে MIME টাইপ যাচাই (extension নয়) — PHP shell আপলোড ব্লক
- `session_regenerate_id()` লগইনের সময় যোগ করা হয়েছে
- DB এরর মেসেজ লগে লেখা হয়, ব্যবহারকারীকে দেখানো হয় না

### 🟠 বাগ সমাধান
- N+1 কোয়েরি সমস্যা দূর — সব ছবি একটি কোয়েরিতে আনা হয়
- `style.css` ও `script.js` আলাদা ফাইলে বিভক্ত (আগে একসাথে ছিল!)
- Error/success এর জন্য `alert()` বাদ দিয়ে flash message ব্যবহার
- `htmlspecialchars()` সব আউটপুটে যোগ করা হয়েছে
- Image path: শুধু ফাইলনেম স্টোর হয়, পুরো পাথ নয়

### 🟢 নতুন ফিচার
- **স্টক / কোয়ান্টিটি ট্র্যাকিং** — আছে / কম / নেই ব্যাজ
- **ব্র্যান্ড ফিল্টার বাটন** — ফ্রন্টএন্ডে ব্র্যান্ড অনুযায়ী ফিল্টার
- **Dashboard Summary** — মোট স্টক, বিনিয়োগ, সম্ভাব্য মুনাফা
- **ইমেজ স্লাইডার ডট** — একাধিক ছবিতে পেজিনেশন ডট দেখায়
- `brand` ও `quantity` ফিল্ড ফর্মে যোগ করা হয়েছে
- `created_at` / `updated_at` টাইমস্ট্যাম্প ডাটাবেজে যোগ
- `sort_order` কলাম ছবির ক্রম নির্ধারণের জন্য
