// Toast নোটিফিকেশন
function showToast(message, isError = false) {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-notification' + (isError ? ' error' : '');
    toast.innerText = message;
    document.body.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 50);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 350);
    }, 3000);
}

// টেক্সট কপি
function copyText(elementId, copyType) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = (el.textContent || el.innerText).trim();
    navigator.clipboard.writeText(text)
        .then(() => showToast(copyType + ' কপি হয়েছে! ✅'))
        .catch(() => showToast('কপি ব্যর্থ হয়েছে ❌', true));
}

// সব ছবি ডাউনলোড
async function downloadAllImages(urlsString, watchName) {
    if (!urlsString) { showToast('ডাউনলোড করার মতো কোনো ছবি নেই! ❌', true); return; }
    const urls = urlsString.split(',').filter(Boolean);
    showToast('সবগুলো ছবি ডাউনলোড হচ্ছে... (' + urls.length + ' টি) 📥');

    for (let i = 0; i < urls.length; i++) {
        try {
            const res = await fetch(urls[i]);
            if (!res.ok) throw new Error('fetch failed');
            const blob = await res.blob();
            const ext  = blob.type.split('/')[1] || 'jpg';
            const name = watchName.replace(/\s+/g, '_') + '_Image_' + (i + 1) + '.' + ext;
            const url  = URL.createObjectURL(blob);
            const a    = document.createElement('a');
            a.href = url; a.download = name;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 1000);
            await new Promise(r => setTimeout(r, 800));
        } catch (e) { console.error('Download error image ' + (i+1), e); }
    }
    setTimeout(() => showToast('সবগুলো (' + urls.length + ' টি) ছবি ডাউনলোড সম্পন্ন! ✅'), 600);
}

// লাইভ সার্চ
function filterWatches() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.getElementsByClassName('watch-card');
    let hasResult = false;

    for (const card of cards) {
        const name  = (card.querySelector('.watch-name')?.innerText  || '').toLowerCase();
        const model = (card.querySelector('.watch-model')?.innerText || '').toLowerCase();
        const brand = (card.querySelector('.watch-brand')?.innerText || '').toLowerCase();
        const show  = name.includes(input) || model.includes(input) || brand.includes(input);
        card.style.display = show ? '' : 'none';
        if (show) hasResult = true;
    }

    const noResult = document.getElementById('noResultMessage');
    if (noResult) noResult.style.display = (hasResult || !input) ? 'none' : 'block';
}

// ইমেজ স্লাইডার ডট আপডেট
function initSlider(watchId) {
    const container = document.getElementById('slider-' + watchId);
    const dotsEl    = document.getElementById('dots-' + watchId);
    if (!container || !dotsEl) return;

    const slides = container.querySelectorAll('.slide-item');
    if (slides.length <= 1) return;

    container.addEventListener('scroll', () => {
        const idx = Math.round(container.scrollLeft / container.clientWidth);
        dotsEl.querySelectorAll('.img-dot').forEach((d, i) => d.classList.toggle('active', i === idx));
    });
}
