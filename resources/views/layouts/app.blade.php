<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Styles -->
    @livewireStyles
    <!-- Ajout du style pour la transition -->
    <style>
        .sidebar {
            transition: width 0.3s ease;
        }
        .sidebar.collapsed {
            width: 60px;
        }
        .main-content {
            transition: margin-left 0.3s ease;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 flex">
<!-- Sidebar -->
<x-sidebar />
<!-- Contenu principal -->
<div class="flex-1 flex flex-col sm:ml-64 w-full min-h-screen main-content">
    <!-- Bouton pour replier/déplier la sidebar -->
    <!-- En-tête de la page -->
    @if (isset($header))
        <header class="bg-white shadow mb-4 w-full px-6 py-4">
            {{ $header }}
        </header>
    @endif
    <!-- Contenu principal -->
    <main class="flex-1 w-full">
        {{ $slot }}
    </main>
</div>
<!-- Scripts -->
@livewireScripts
<!-- Ajout du script pour la gestion de la sidebar -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById("separator-sidebar");
        const mainContent = document.querySelector(".main-content");
        const toggleButton = document.getElementById("toggle-sidebar");
        const sidebarLogo = document.getElementById("sidebar-logo");
        const sidebarTextElements = document.querySelectorAll(".sidebar-text");

        toggleButton.addEventListener("click", function() {
            sidebar.classList.toggle("collapsed");

            if (sidebar.classList.contains("collapsed")) {
                sidebar.style.width = "60px";
                mainContent.style.marginLeft = "60px";
                sidebarLogo.style.width = "40px"; // Réduction du logo
                sidebarTextElements.forEach(el => el.style.display = "none"); // Masquer les textes
            } else {
                sidebar.style.width = "250px";
                mainContent.style.marginLeft = "250px";
                sidebarLogo.style.width = "128px"; // Rétablir le logo
                sidebarTextElements.forEach(el => el.style.display = "inline"); // Afficher les textes
            }
        });
    });
</script>
</body>
</html>
