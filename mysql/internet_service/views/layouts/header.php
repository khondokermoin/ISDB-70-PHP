<!DOCTYPE html>
<html lang="en">
<head>
    <title>Amar IT</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Optional: Tailwind Custom Configuration for Brand Colors -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amberRed: '#dc2626', // Custom red matching Amber IT
                        amberDark: '#111827', // Dark color for footer/topbar
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

    <!-- Top-Bar START -->
    <div class="bg-amberDark text-gray-300 text-xs md:text-sm hidden md:block py-2">
        <div class="container mx-auto px-4 flex justify-between items-center max-w-6xl">
            <div class="flex space-x-6 items-center">
                <span class="font-semibold text-white">Welcome to Amar IT</span>
                <span><i class="fa fa-phone text-amberRed mr-1"></i> 09611123123</span>
                <span><i class="fa fa-envelope text-amberRed mr-1"></i> support@amarit.com.bd</span>
            </div>
            <div class="flex space-x-4 items-center">
                <a href="#" class="hover:text-white transition">BTRC Approved Tariff</a>
                <a href="#" class="hover:text-white transition">Blog</a>
                <div class="flex space-x-3 text-lg">
                    <a href="#" class="hover:text-blue-500 transition"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="hover:text-red-500 transition"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Top-Bar END -->

    <!-- Navbar START -->
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center max-w-6xl">
            <!-- Logo -->
            <a href="index.php" class="text-3xl font-extrabold text-amberRed tracking-tight">
                AMAR <span class="text-gray-800">IT</span>
            </a>
            
            <!-- Desktop Menu -->
            <nav class="hidden md:flex space-x-6 font-semibold text-gray-600">
                <a href="#" class="hover:text-amberRed transition">Home Internet</a>
                <a href="corporate.php" class="hover:text-amberRed transition">Corporate</a>
                <a href="#" class="hover:text-amberRed transition">IPTSP</a>
                <a href="#" class="hover:text-amberRed transition">Hosting</a>
                <a href="#" class="hover:text-amberRed transition">Support</a>
            </nav>

            <!-- Mobile Menu Button -->
            <button class="md:hidden text-gray-600 hover:text-amberRed focus:outline-none">
                <i class="fa fa-bars text-2xl"></i>
            </button>
        </div>
    </header>
    <!-- Navbar END -->