<aside id="separator-sidebar" class="fixed top-0 left-0 z-40 w-64 h-full transition-all bg-gray-50 dark:bg-gray-800 pt-16 flex flex-col">
    <!-- Toggle Sidebar Button -->
    <button id="toggle-sidebar" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
        </svg>
    </button>

    <!-- Logo -->
    <div class="flex justify-center mb-6">
        <img id="sidebar-logo" src="{{ asset('build/assets/League-of-Legends-Logo.png') }}" alt="League of Legends Logo" class="w-32 h-auto transition-all" />
    </div>

    <div class="h-full px-3 py-4 overflow-y-auto flex flex-col justify-between">
        <ul class="space-y-2 font-medium">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-all" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7m-9 14V10m-6 8h12"></path>
                    </svg>
                    <span class="ms-3 sidebar-text transition-all">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('roles.index') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-all" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12A4 4 0 108 12a4 4 0 008 0zm4-7h-4M6 5H2m14 14h4M6 19H2m2-7H2m20 0h-2"></path>
                    </svg>
                    <span class="ms-3 sidebar-text transition-all">Gestion des rôles</span>
                </a>
            </li>
        </ul>

        <!-- Footer section with Profile and Logout -->
        <ul class="space-y-2 font-medium mt-auto pb-4">
            <li>
                <a href="{{ route('profile.show') }}" class="flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-all" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.953 9.953 0 0112 15a9.953 9.953 0 016.879 2.804M12 15V9m0 0l-3 3m3-3l3 3"></path>
                    </svg>
                    <span class="ms-3 sidebar-text transition-all">Mon profil</span>
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-all" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1m0-11V7"></path>
                        </svg>
                        <span class="ms-3 sidebar-text transition-all">Déconnexion</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>

