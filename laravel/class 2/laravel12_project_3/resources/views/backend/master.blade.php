<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="adminHMD professional admin dashboard template">
    <title>Dashboard | adminHMD</title>

    @stack('styles')
</head>

<body>
    <div class="admin-shell">
        <div class="sidebar-backdrop" data-sidebar-close></div>

        @include('backend.parts.leftbar')

        <div class="admin-main">

            @include('backend.parts.topnav')

            @yield('content')

            @include('backend.parts.footer')
        </div>
    </div>


    @stack('scripts')
</body>

</html>
