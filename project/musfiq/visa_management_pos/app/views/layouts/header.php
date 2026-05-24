<?php
// যদি সেশন আগে থেকে শুরু না হয়ে থাকে, তবে শুরু করবে
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard - Visa Management POS'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                }
            }
        }
    </script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            background-color: #f8fafc;
        }

        /* Tailwind Slate 50 */

        /* DataTables Premium Customization */
        .dataTables_wrapper {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #cbd5e1;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            margin-left: 0.75rem;
            outline: none;
            font-size: 0.875rem;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            padding: 0.3rem 2rem 0.3rem 0.75rem;
            border-radius: 0.5rem;
            outline: none;
            font-size: 0.875rem;
            cursor: pointer;
        }

        /* Table Border & Header styling */
        table.dataTable.no-footer {
            border-bottom: 1px solid #e2e8f0;
        }

        table.dataTable thead th {
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }

        /* Custom Pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.3rem 0.8rem;
            margin: 0 0.2rem;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: 1px solid #bfdbfe !important;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
            background: #f1f5f9 !important;
            border: 1px solid #cbd5e1 !important;
            color: #0f172a !important;
        }
    </style>
</head>

<body class="font-sans flex flex-col min-h-screen antialiased text-slate-800">

    <nav class="bg-slate-900 border-b border-slate-800 text-white px-6 py-3 shadow-lg flex justify-between items-center sticky top-0 z-50">

        <div class="flex items-center gap-3">
            <div class="bg-blue-600 p-2 rounded-lg shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-100">Visa POS<span class="text-blue-400">Pro</span></h1>
        </div>

        <div class="flex items-center gap-5">

            <div class="flex items-center gap-2 bg-slate-800 px-3 py-1.5 rounded-full border border-slate-700 shadow-sm">
                <div id="network-dot" class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></div>
                <span id="network-status" class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Online</span>
            </div>

            <div class="flex items-center gap-3 border-l border-slate-700 pl-5">
                <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center border border-slate-600 shadow-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="flex flex-col hidden sm:flex">
                    <span class="text-[10px] text-slate-400 font-semibold uppercase leading-none mb-1">Welcome back,</span>
                    <span class="text-sm font-bold text-slate-200 leading-none"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></span>
                </div>
            </div>

            <a href="../app/views/auth/logout.php" class="ml-2 bg-slate-800 hover:bg-red-500 hover:text-white text-slate-300 border border-slate-700 hover:border-red-500 p-2 rounded-lg transition-all duration-300 group shadow-sm" title="Logout">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </nav>

    <main class="flex-grow p-6 max-w-[1600px] mx-auto w-full">