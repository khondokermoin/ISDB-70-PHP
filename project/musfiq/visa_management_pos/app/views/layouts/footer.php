</main>
<footer class="bg-white border-t border-slate-200 mt-auto shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)]">
    <div class="max-w-[1600px] mx-auto w-full px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">

        <p class="text-sm text-slate-500 font-medium">
            &copy; <?php echo date('Y'); ?> <span class="font-bold text-slate-800">Visa POS<span class="text-blue-600">Pro</span></span>. All rights reserved.
        </p>

        <div class="flex items-center gap-6 text-xs font-semibold text-slate-400 uppercase tracking-wider">
            <div class="flex items-center gap-1.5 hover:text-slate-600 transition-colors cursor-default">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Offline Sync Active</span>
            </div>
            <span class="bg-slate-100 px-2 py-1 rounded text-slate-500 border border-slate-200">v1.0.0</span>
        </div>
    </div>
</footer>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<script>
    // ==========================================
    // 1. Network Status Indicator Logic
    // ==========================================
    window.addEventListener('online', updateIndicator);
    window.addEventListener('offline', updateIndicator);

    function updateIndicator() {
        const statusText = document.getElementById('network-status');
        const statusDot = document.getElementById('network-dot');

        if (navigator.onLine) {
            // Online State Styling (Green with glowing pulse)
            if (statusText) statusText.textContent = 'Online';
            if (statusText) statusText.className = 'text-xs font-bold text-slate-300 uppercase tracking-wider';
            if (statusDot) statusDot.className = 'w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.6)]';

            // Trigger Background Sync if function exists (To be added later)
            if (typeof syncOfflineData === "function") {
                syncOfflineData();
            }
        } else {
            // Offline State Styling (Red without pulse)
            if (statusText) statusText.textContent = 'Offline';
            if (statusText) statusText.className = 'text-xs font-bold text-red-400 uppercase tracking-wider';
            if (statusDot) statusDot.className = 'w-2.5 h-2.5 bg-red-500 rounded-full shadow-[0_0_8px_rgba(239,68,68,0.6)]';
        }
    }

    // Initial check on page load
    document.addEventListener("DOMContentLoaded", function() {
        updateIndicator();
    });

    // ==========================================
    // 2. Service Worker Registration (PWA)
    // ==========================================
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            // ব্রাউজারের URL (public/) এর ওপর ভিত্তি করে পাথ দেওয়া হয়েছে
            navigator.serviceWorker.register('./service-worker.js')
                .then((registration) => {
                    console.log('Service Worker Registered Successfully! Scope:', registration.scope);
                })
                .catch((error) => {
                    console.log('Service Worker Registration Failed:', error);
                });
        });
    }
</script>
</body>

</html>