<?php
require_once 'config/db.php';

$watches = $pdo->query("SELECT * FROM watches ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$allImages = $pdo->query("SELECT * FROM watch_images ORDER BY watch_id, sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$imageMap = [];
foreach ($allImages as $img) {
    $imageMap[$img['watch_id']][] = $img;
}
$brands = $pdo->query("SELECT DISTINCT brand FROM watches WHERE brand IS NOT NULL AND brand != '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Inventory — Staff Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* ===== DRAG GHOST ===== */
        #drag-ghost {
            position: fixed;
            top: -9999px;
            left: -9999px;
            pointer-events: none;
            z-index: 99999;
            background: #1e293b;
            border: 2px solid #3b82f6;
            border-radius: 12px;
            padding: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            max-width: 220px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        #drag-ghost img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }

        #drag-ghost .ghost-count {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #3b82f6;
            color: white;
            font-size: 12px;
            font-weight: 700;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        /* ===== DRAG HINT BANNER ===== */
        #drag-hint {
            position: fixed;
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            background: #1e293b;
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: bottom 0.3s ease;
            z-index: 9999;
            white-space: nowrap;
        }

        #drag-hint.show {
            bottom: 24px;
        }

        #drag-hint svg {
            flex-shrink: 0;
        }

        /* ===== DRAG OVERLAY (drop target highlight) ===== */
        .dragging-active .selectable-img {
            cursor: grabbing !important;
        }

        /* Selected image ring */
        .is-selected {
            outline: 3px solid #3b82f6 !important;
            outline-offset: -3px;
        }

        .is-selected+div {
            opacity: 1 !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <!-- Drag Ghost Element (hidden, used as drag image) -->
    <div id="drag-ghost"></div>

    <!-- Drag Hint Banner -->
    <div id="drag-hint">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
        </svg>
        <span id="drag-hint-text">টেনে Messenger / WhatsApp এ ছেড়ে দিন</span>
    </div>

    <!-- Navbar -->
    <nav class="bg-white border-b border-gray-200 py-4 sticky top-0 z-20 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">Watch Inventory</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-semibold text-blue-700 bg-blue-50 border border-blue-100 px-3 py-1 rounded-md hidden sm:block">Staff Panel</span>
                <a href="admin/login.php" class="bg-gray-900 hover:bg-black text-white text-sm px-4 py-2 rounded-md font-medium transition flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Admin Login
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 pb-12 pt-8">

        <!-- Search + Brand Filter -->
        <div class="mb-8 flex flex-col md:flex-row gap-4 items-center">
            <div class="relative w-full md:max-w-md">
                <svg class="w-5 h-5 absolute left-4 top-3 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" oninput="filterWatches()" placeholder="Search by brand or model..."
                    class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-800 focus:border-gray-800 transition">
            </div>
            <?php if (!empty($brands)): ?>
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterByBrand('')" class="brand-btn active text-sm font-medium px-4 py-2 rounded-md border border-gray-900 bg-gray-900 text-white transition shadow-sm">All Brands</button>
                    <?php foreach ($brands as $brand): ?>
                        <button onclick="filterByBrand('<?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>')"
                            class="brand-btn text-sm font-medium px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-50 transition shadow-sm">
                            <?= htmlspecialchars($brand, ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Watch Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="watchesContainer">

            <?php foreach ($watches as $watch):
                $wid      = (int)$watch['id'];
                $imgs     = $imageMap[$wid] ?? [];
                $imgCount = count($imgs);
                $qty      = (int)($watch['quantity'] ?? 0);
                $urlArr   = array_column($imgs, 'image_url');
                $urlStr   = implode(',', $urlArr);

                if ($qty <= 0) {
                    $stockClass = 'bg-red-50 text-red-700 border-red-200';
                    $stockLabel = 'Out of Stock';
                } elseif ($qty <= 3) {
                    $stockClass = 'bg-amber-50 text-amber-700 border-amber-200';
                    $stockLabel = 'Low Stock (' . $qty . ')';
                } else {
                    $stockClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    $stockLabel = 'In Stock (' . $qty . ')';
                }

                $gridCols = 1;
                $gridRows = 1;
                if ($imgCount == 2) {
                    $gridCols = 2;
                    $gridRows = 1;
                } elseif ($imgCount <= 4) {
                    $gridCols = 2;
                    $gridRows = 2;
                } elseif ($imgCount <= 6) {
                    $gridCols = 3;
                    $gridRows = 2;
                } elseif ($imgCount <= 9) {
                    $gridCols = 3;
                    $gridRows = 3;
                } elseif ($imgCount > 9) {
                    $gridCols = ceil(sqrt($imgCount));
                    $gridRows = ceil($imgCount / $gridCols);
                }
            ?>
                <div class="watch-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-200 flex flex-col"
                    data-brand="<?= htmlspecialchars(strtolower($watch['brand'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

                    <!-- Image Grid -->
                    <div class="relative h-64 w-full bg-gray-100 border-b border-gray-100 overflow-hidden">
                        <?php if ($imgCount == 0): ?>
                            <div class="flex flex-col items-center justify-center w-full h-full text-gray-400">
                                <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-medium">No Image</span>
                            </div>
                        <?php else: ?>
                            <div class="grid gap-0.5 w-full h-full bg-gray-200 select-none"
                                style="grid-template-columns: repeat(<?= $gridCols ?>, minmax(0, 1fr)); grid-template-rows: repeat(<?= $gridRows ?>, minmax(0, 1fr));">
                                <?php foreach ($imgs as $i => $img): ?>
                                    <div class="relative w-full h-full overflow-hidden">
                                        <img
                                            src="<?= htmlspecialchars($img['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                            class="selectable-img w-full h-full object-cover cursor-pointer transition-all"
                                            draggable="true"
                                            title="Click=select | Ctrl+Click=multi | Shift+Click=range | ছবি drag করে WhatsApp/Messenger এ দিন"
                                            data-url="<?= htmlspecialchars($img['image_url'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-watch="<?= $wid ?>"
                                            data-index="<?= $i ?>"
                                            onmousedown="handleImgMouseDown(event, <?= $wid ?>, <?= $i ?>)"
                                            onclick="selectImage(event, <?= $wid ?>, <?= $i ?>)">

                                        <!-- Selection Overlay -->
                                        <div id="overlay-<?= $wid ?>-<?= $i ?>"
                                            class="absolute inset-0 bg-blue-500/20 border-4 border-blue-500 opacity-0 pointer-events-none transition-all flex items-start justify-end p-1.5">
                                            <svg class="w-6 h-6 text-white bg-blue-600 rounded-full shadow-md p-1" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Stock Badge -->
                        <span class="absolute top-3 left-3 z-10 border <?= $stockClass ?> text-xs font-semibold px-2.5 py-1 rounded-md bg-white/95 shadow-sm pointer-events-none">
                            <?= $stockLabel ?>
                        </span>

                        <!-- Action Panel (shown when images selected) -->
                        <div id="drag-handle-<?= $wid ?>"
                            class="hidden absolute bottom-3 right-2 z-10 flex items-center gap-1.5 select-none">

                            <!-- DRAG button: WhatsApp Web / Messenger Web -->
                            <div draggable="true" data-watch="<?= $wid ?>"
                                class="drag-btn bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-lg cursor-grab active:cursor-grabbing flex items-center gap-1"
                                title="Drag করে WhatsApp Web / Messenger Web এ drop করুন">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
                                </svg>
                                <span id="drag-handle-label-<?= $wid ?>">Drag</span>
                            </div>

                            <!-- COPY button: Desktop WhatsApp Ctrl+V -->
                            <button onclick="copySelectedImages(<?= $wid ?>)"
                                class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-2.5 py-1.5 rounded-lg shadow-lg flex items-center gap-1"
                                title="Clipboard এ copy — Desktop WhatsApp এ Ctrl+V paste করুন">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span id="copy-handle-label-<?= $wid ?>">Copy</span>
                            </button>
                        </div>
                    </div>

                    <!-- Info & Buttons -->
                    <div class="p-5 flex flex-col flex-grow">
                        <div class="flex justify-between items-start mb-2">
                            <h2 class="text-lg font-bold text-gray-900 leading-tight">
                                <span class="watch-brand"><?= htmlspecialchars($watch['brand'] ?? $watch['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="watch-model text-gray-600 font-medium ml-1"><?= htmlspecialchars($watch['model'], ENT_QUOTES, 'UTF-8') ?></span>
                            </h2>
                            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded border border-gray-200 shrink-0">
                                Buy: ৳<?= number_format((float)$watch['buying_price']) ?>
                            </span>
                        </div>

                        <textarea id="short_info_<?= $wid ?>" class="hidden">Brand: <?= htmlspecialchars($watch['brand'] ?? $watch['name'], ENT_QUOTES, 'UTF-8') ?>&#13;&#10;Model: <?= htmlspecialchars($watch['model'], ENT_QUOTES, 'UTF-8') ?>&#13;&#10;Price: <?= number_format((float)$watch['selling_price']) ?> Tk</textarea>
                        <textarea id="full_info_<?= $wid ?>" class="hidden">Brand: <?= htmlspecialchars($watch['brand'] ?? $watch['name'], ENT_QUOTES, 'UTF-8') ?>&#13;&#10;Model: <?= htmlspecialchars($watch['model'], ENT_QUOTES, 'UTF-8') ?>&#13;&#10;Price: <?= number_format((float)$watch['selling_price']) ?> Tk&#13;&#10;&#13;&#10;Details:&#13;&#10;<?= htmlspecialchars($watch['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>

                        <div class="mt-auto space-y-3 pt-5">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-3 rounded-md border border-gray-200">
                                <div>
                                    <span class="block text-xs text-gray-500 font-semibold uppercase tracking-wider">Selling Price</span>
                                    <span class="text-xl font-bold text-gray-900">৳<?= number_format((float)$watch['selling_price']) ?></span>
                                </div>
                                <button onclick="copyText('short_info_<?= $wid ?>', 'Price Info')"
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-md transition shadow-sm">
                                    Copy Price
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <?php if (!empty($urlArr)): ?>
                                    <button onclick="downloadSelectedOrAll(<?= $wid ?>, '<?= htmlspecialchars($watch['model'], ENT_QUOTES, 'UTF-8') ?>')"
                                        class="col-span-1 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium px-3 py-2.5 rounded-md transition flex justify-center items-center gap-1.5 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Save Pics
                                    </button>
                                    <button onclick="copyText('full_info_<?= $wid ?>', 'Full Details')"
                                        class="col-span-1 bg-gray-900 hover:bg-black text-white text-sm font-medium px-3 py-2.5 rounded-md transition flex justify-center items-center gap-1.5 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        Copy Details
                                    </button>
                                <?php else: ?>
                                    <button onclick="copyText('full_info_<?= $wid ?>', 'Full Details')"
                                        class="col-span-2 bg-gray-900 hover:bg-black text-white text-sm font-medium px-3 py-2.5 rounded-md transition flex justify-center items-center gap-1.5 shadow-sm">
                                        Copy Full Details
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($watches)): ?>
                <div class="col-span-full text-center py-16 bg-white rounded-xl border border-gray-200 shadow-sm">
                    <p class="text-gray-500 text-lg font-medium">No watches have been added yet.</p>
                    <a href="admin/login.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm mt-2 inline-block">Go to Admin Panel &rarr;</a>
                </div>
            <?php endif; ?>

            <div id="noResultMessage" class="hidden col-span-full text-center py-16 bg-white rounded-xl border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-lg font-medium">No matching watches found.</p>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        // ============================================================
        //  MULTI-SELECT
        // ============================================================
        let lastSelected = {};
        let selectedMap = {};

        function getSelected(watchId) {
            return selectedMap[watchId] || (selectedMap[watchId] = new Set());
        }

        function setOverlay(watchId, index, active) {
            const overlay = document.getElementById(`overlay-${watchId}-${index}`);
            const img = document.querySelector(`.selectable-img[data-watch="${watchId}"][data-index="${index}"]`);
            if (overlay) {
                overlay.classList.toggle('opacity-0', !active);
                overlay.classList.toggle('opacity-100', active);
            }
            if (img) {
                img.classList.toggle('is-selected', active);
            }
        }

        function refreshDragHandle(watchId) {
            const sel = getSelected(watchId);
            const panel = document.getElementById(`drag-handle-${watchId}`);
            const dragLabel = document.getElementById(`drag-handle-label-${watchId}`);
            const copyLabel = document.getElementById(`copy-handle-label-${watchId}`);
            if (!panel) return;
            if (sel.size > 0) {
                panel.classList.remove('hidden');
                panel.classList.add('flex');
                const txt = sel.size === 1 ? '1 pic' : `${sel.size} pics`;
                if (dragLabel) dragLabel.textContent = 'Drag ' + txt;
                if (copyLabel) copyLabel.textContent = 'Copy ' + txt;
            } else {
                panel.classList.add('hidden');
                panel.classList.remove('flex');
            }
        }

        // mousedown: record position for drag-vs-click distinction
        let mouseDownData = null;
        window.handleImgMouseDown = function(e, watchId, index) {
            mouseDownData = {
                watchId,
                index,
                x: e.clientX,
                y: e.clientY
            };
        };

        window.selectImage = function(event, watchId, index) {
            // If this looks like end of a drag (moved > 5px), don't change selection
            if (mouseDownData && (Math.abs(event.clientX - mouseDownData.x) > 5 || Math.abs(event.clientY - mouseDownData.y) > 5)) {
                event.preventDefault();
                mouseDownData = null;
                return;
            }
            mouseDownData = null;

            const sel = getSelected(watchId);
            const imgs = document.querySelectorAll(`.selectable-img[data-watch="${watchId}"]`);
            const total = imgs.length;

            if (event.shiftKey) {
                const start = lastSelected[watchId] ?? 0;
                const from = Math.min(start, index),
                    to = Math.max(start, index);
                if (!event.ctrlKey && !event.metaKey) {
                    sel.clear();
                    for (let i = 0; i < total; i++) setOverlay(watchId, i, false);
                }
                for (let i = from; i <= to; i++) {
                    sel.add(i);
                    setOverlay(watchId, i, true);
                }
            } else if (event.ctrlKey || event.metaKey) {
                if (sel.has(index)) {
                    sel.delete(index);
                    setOverlay(watchId, index, false);
                } else {
                    sel.add(index);
                    setOverlay(watchId, index, true);
                }
                lastSelected[watchId] = index;
            } else {
                const onlyThis = sel.has(index) && sel.size === 1;
                sel.clear();
                for (let i = 0; i < total; i++) setOverlay(watchId, i, false);
                if (!onlyThis) {
                    sel.add(index);
                    setOverlay(watchId, index, true);
                    lastSelected[watchId] = index;
                }
            }
            refreshDragHandle(watchId);
            // Selection হওয়ার সাথে সাথে সব selected ছবির blob fetch শুরু
            prefetchAsFiles(watchId);
        }

        // ============================================================
        //  DRAG  — Canvas collage approach for multiple images
        //
        //  1 image selected  → browser native drag (works everywhere)
        //  2+ images selected → Canvas এ সব ছবি grid করে 1টা PNG বানিয়ে
        //                       সেই PNG কে native drag করা → works everywhere
        // ============================================================
        const dragGhost = document.getElementById('drag-ghost');
        const dragHint = document.getElementById('drag-hint');
        const dragHintText = document.getElementById('drag-hint-text');

        // Blob/ObjectURL cache
        const blobCache = {};

        function fetchBlob(url) {
            if (blobCache[url]) return Promise.resolve(blobCache[url]);
            return fetch(url).then(r => r.blob()).then(b => {
                blobCache[url] = b;
                return b;
            }).catch(() => null);
        }

        // ── Drag-ready File cache: url → File (fetched & ready) ──
        const readyFiles = {};

        window.prefetchAsFiles = async function(watchId) {
            const sel = getSelected(watchId);
            const urls = [];
            sel.forEach(idx => {
                const el = document.querySelector(`.selectable-img[data-watch="${watchId}"][data-index="${idx}"]`);
                if (el) urls.push(el.dataset.url);
            });
            await Promise.all(urls.map(async (url, i) => {
                if (readyFiles[url]) return;
                const blob = await fetchBlob(url);
                if (!blob) return;
                const ext = blob.type.split('/')[1] || 'jpg';
                const name = url.split('/').pop() || `image_${i + 1}.${ext}`;
                readyFiles[url] = new File([blob], name, {
                    type: blob.type
                });
            }));
        };

        // Drag button এ pointerdown হলে সাথে সাথে fetch শুরু
        document.addEventListener('pointerdown', function(e) {
            const btn = e.target.closest('.drag-btn');
            if (!btn) return;
            const watchId = parseInt(btn.dataset.watch);
            if (watchId) prefetchAsFiles(watchId);
        });

        document.addEventListener('dragstart', function(e) {
            const imgEl = e.target.closest('.selectable-img');
            const dragBtn = e.target.closest('.drag-btn');
            const sourceEl = imgEl || dragBtn;
            if (!sourceEl) return;

            const watchId = parseInt(sourceEl.dataset.watch);
            const sel = getSelected(watchId);

            // Image এ drag করলে এবং selected না থাকলে auto-select
            if (imgEl && !sel.has(parseInt(imgEl.dataset.index))) {
                const index = parseInt(imgEl.dataset.index);
                sel.clear();
                document.querySelectorAll(`.selectable-img[data-watch="${watchId}"]`).forEach((im, i) => setOverlay(watchId, i, false));
                sel.add(index);
                setOverlay(watchId, index, true);
                refreshDragHandle(watchId);
            }

            const selImgEls = [];
            const urls = [];
            sel.forEach(idx => {
                const el = document.querySelector(`.selectable-img[data-watch="${watchId}"][data-index="${idx}"]`);
                if (el) {
                    selImgEls.push(el);
                    urls.push(el.dataset.url);
                }
            });

            e.dataTransfer.effectAllowed = 'copy';

            // Ghost setup
            dragGhost.innerHTML = '';
            if (urls.length === 1) {
                dragGhost.innerHTML = `<img src="${urls[0]}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">`;
                dragGhost.style.display = 'flex';
                e.dataTransfer.setDragImage(dragGhost, 45, 45);
                dragHintText.textContent = 'ছবিটি WhatsApp / Messenger এ ছেড়ে দিন';
            } else {
                selImgEls.slice(0, 6).forEach(el => {
                    const gi = document.createElement('img');
                    gi.src = el.src;
                    dragGhost.appendChild(gi);
                });
                const badge = document.createElement('div');
                badge.className = 'ghost-count';
                badge.textContent = urls.length;
                dragGhost.appendChild(badge);
                dragGhost.style.display = 'flex';
                e.dataTransfer.setDragImage(dragGhost, 110, 70);
            }
            dragHint.classList.add('show');

            if (urls.length === 1) {
                // ১টা ছবি — browser নিজেই handle করবে, কিছু করতে হবে না
                return;
            }

            // ── একাধিক ছবি: readyFiles থেকে synchronously add ──
            const allReady = urls.every(url => readyFiles[url]);

            if (allReady) {
                // সব blob ready — একটার পর একটা add
                urls.forEach(url => {
                    e.dataTransfer.items.add(readyFiles[url]);
                });
                dragHintText.textContent = `${urls.length} টি ছবি — WhatsApp / Messenger এ ছেড়ে দিন`;
            } else {
                // কিছু blob এখনো ready না — URI list দিয়ে drag চালু রাখো
                // এবং background এ fetch করো (পরের drag এ কাজ করবে)
                e.dataTransfer.setData('text/uri-list', urls.join('\r\n'));
                e.dataTransfer.setData('text/plain', urls.join('\r\n'));
                dragHintText.textContent = `ছবি লোড হয়নি — আবার drag করুন ⟳`;
                prefetchAsFiles(watchId); // background এ fetch
            }
        });

        document.addEventListener('dragend', function(e) {
            dragHint.classList.remove('show');
            dragGhost.innerHTML = '';
            dragGhost.style.display = 'none';
        });

        // Pre-fetch on hover (card বা drag panel)
        document.addEventListener('mouseover', function(e) {
            const panel = e.target.closest('[id^="drag-handle-"]');
            if (panel) {
                const wid = panel.querySelector('[data-watch]')?.dataset.watch;
                if (wid) prefetchAsFiles(parseInt(wid));
                return;
            }
            // ছবিতে hover করলেও fetch
            const img = e.target.closest('.selectable-img');
            if (img) fetchBlob(img.dataset.url);
        });

        // ============================================================
        //  COPY TO CLIPBOARD  (Desktop WhatsApp → Ctrl+V)
        // ============================================================
        window.copySelectedImages = async function(watchId) {
            const sel = getSelected(watchId);
            if (sel.size === 0) return;
            const copyLabel = document.getElementById(`copy-handle-label-${watchId}`);
            if (copyLabel) copyLabel.textContent = '...';

            const urls = [];
            sel.forEach(idx => {
                const el = document.querySelector(`.selectable-img[data-watch="${watchId}"][data-index="${idx}"]`);
                if (el) urls.push(el.dataset.url);
            });

            async function blobToFile(url) {
                const b = await fetchBlob(url);
                const ext = b.type.split('/')[1] || 'jpg';
                const nm = url.split('/').pop() || ('image.' + ext);
                return new File([b], nm, {
                    type: b.type
                });
            }

            try {
                const file = await blobToFile(urls[0]);
                await navigator.clipboard.write([new ClipboardItem({
                    [file.type]: file
                })]);
                if (urls.length === 1) {
                    showToast('ছবি copy হয়েছে — Desktop WhatsApp এ Ctrl+V দিন ✅');
                } else {
                    showToast(`১ম ছবি copy হয়েছে (Ctrl+V দিন) — বাকি ${urls.length-1} টি download হচ্ছে 📥`);
                    for (let i = 1; i < urls.length; i++) {
                        const f = await blobToFile(urls[i]);
                        const u = URL.createObjectURL(f);
                        const a = document.createElement('a');
                        a.href = u;
                        a.download = f.name;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        setTimeout(() => URL.revokeObjectURL(u), 1000);
                        await new Promise(r => setTimeout(r, 600));
                    }
                }
            } catch (err) {
                showToast('Clipboard কাজ করেনি — সব ছবি download হচ্ছে 📥');
                for (let i = 0; i < urls.length; i++) {
                    const f = await blobToFile(urls[i]);
                    const u = URL.createObjectURL(f);
                    const a = document.createElement('a');
                    a.href = u;
                    a.download = f.name;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    setTimeout(() => URL.revokeObjectURL(u), 1000);
                    await new Promise(r => setTimeout(r, 600));
                }
            } finally {
                if (copyLabel) {
                    const n = sel.size;
                    copyLabel.textContent = 'Copy ' + (n === 1 ? '1 pic' : n + ' pics');
                }
            }
        }

        // ============================================================
        //  DOWNLOAD SELECTED OR ALL
        // ============================================================
        window.downloadSelectedOrAll = function(watchId, modelName) {
            const sel = getSelected(watchId);
            const urls = [];
            if (sel.size > 0) {
                sel.forEach(idx => {
                    const el = document.querySelector(`.selectable-img[data-watch="${watchId}"][data-index="${idx}"]`);
                    if (el) urls.push(el.dataset.url);
                });
            } else {
                document.querySelectorAll(`.selectable-img[data-watch="${watchId}"]`).forEach(el => urls.push(el.dataset.url));
            }
            if (urls.length && typeof downloadAllImages === 'function') downloadAllImages(urls.join(','), modelName);
        }

        // ============================================================
        //  SEARCH & BRAND FILTER
        // ============================================================
        let activeBrand = '';

        function filterByBrand(brand) {
            activeBrand = brand.toLowerCase();
            document.querySelectorAll('.brand-btn').forEach(btn => {
                const isThis = btn.getAttribute('onclick').includes("'" + brand + "'") || (brand === '' && btn.getAttribute('onclick').includes("''"));
                btn.classList.toggle('bg-gray-900', isThis);
                btn.classList.toggle('text-white', isThis);
                btn.classList.toggle('border-gray-900', isThis);
                btn.classList.toggle('bg-white', !isThis);
                btn.classList.toggle('text-gray-600', !isThis);
                btn.classList.toggle('border-gray-300', !isThis);
            });
            filterWatches();
        }
        window.filterWatches = function() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.getElementsByClassName('watch-card');
            let hasResult = false;
            for (const card of cards) {
                const model = (card.querySelector('.watch-model')?.innerText || '').toLowerCase();
                const brand = (card.querySelector('.watch-brand')?.innerText || '').toLowerCase();
                const cBrand = card.dataset.brand || '';
                const show = (!input || model.includes(input) || brand.includes(input)) && (!activeBrand || cBrand === activeBrand);
                card.style.display = show ? '' : 'none';
                if (show) hasResult = true;
            }
            const nr = document.getElementById('noResultMessage');
            if (nr) nr.style.display = (hasResult || (!input && !activeBrand)) ? 'none' : 'block';
        }
        document.querySelectorAll('.brand-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.brand-btn').forEach(b => {
                    b.classList.remove('bg-gray-900', 'text-white', 'border-gray-900');
                    b.classList.add('bg-white', 'text-gray-600', 'border-gray-300');
                });
                this.classList.add('bg-gray-900', 'text-white', 'border-gray-900');
                this.classList.remove('bg-white', 'text-gray-600', 'border-gray-300');
            });
        });
    </script>
</body>

</html>