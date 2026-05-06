<!-- Footer START -->
    <footer class="bg-amberDark text-gray-300 pt-16 pb-8">
        <div class="container mx-auto max-w-6xl px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 border-b border-gray-700 pb-12 mb-8">
                
                <!-- Column 1: Contact -->
                <div>
                    <h3 class="text-xl font-bold text-white mb-6 uppercase tracking-wider">Contact Us</h3>
                    <ul class="space-y-3 text-sm">
                        <li><i class="fa fa-map-marker-alt text-amberRed w-5"></i> Navana Tower (7th Floor), 45 Gulshan South C/A, Dhaka</li>
                        <li><i class="fa fa-phone text-amberRed w-5"></i> 09611123123</li>
                        <li><i class="fa fa-envelope text-amberRed w-5"></i> info@amarit.com.bd</li>
                    </ul>
                </div>

                <!-- Column 2: Quick Links -->
                <div>
                    <h3 class="text-xl font-bold text-white mb-6 uppercase tracking-wider">Services</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-amberRed transition">Home Internet</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">Corporate Internet</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">Coverage Area</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">IPTSP</a></li>
                    </ul>
                </div>

                <!-- Column 3: Quick Links -->
                <div>
                    <h3 class="text-xl font-bold text-white mb-6 uppercase tracking-wider">Company</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-amberRed transition">About Us</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">Bill Pay</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">Special Offers</a></li>
                        <li><a href="#" class="hover:text-amberRed transition">Contact</a></li>
                    </ul>
                </div>

                <!-- Column 4: Brand -->
                <div>
                    <h3 class="text-xl font-bold text-white mb-6 uppercase tracking-wider">Amar IT</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        The #1 Broadband Internet Provider in Bangladesh offering reliable connectivity and advanced networking solutions.
                    </p>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="flex flex-col md:flex-row justify-between items-center text-sm text-gray-500">
                <p>&copy; <?php echo date("Y"); ?> Amar IT. All Rights Reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="hover:text-white transition"><i class="fa-brands fa-facebook text-xl"></i></a>
                    <a href="#" class="hover:text-white transition"><i class="fa-brands fa-linkedin text-xl"></i></a>
                    <a href="#" class="hover:text-white transition"><i class="fa-brands fa-youtube text-xl"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer END -->

    <!-- Mobile Menu Script (Simple Toggle) -->
    <script>
        // If you want to make the mobile hamburger menu work
        const btn = document.querySelector('button.md\\:hidden');
        const menu = document.querySelector('.hidden.md\\:flex');
        
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
            menu.classList.toggle('flex');
            menu.classList.toggle('flex-col');
            menu.classList.toggle('absolute');
            menu.classList.toggle('top-16');
            menu.classList.toggle('left-0');
            menu.classList.toggle('w-full');
            menu.classList.toggle('bg-white');
            menu.classList.toggle('shadow-lg');
            menu.classList.toggle('p-4');
        });
    </script>
</body>
</html>