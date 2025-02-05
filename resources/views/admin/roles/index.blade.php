<x-app-layout>
    <div class="w-full p-6 bg-white shadow-lg rounded-lg">
        <!-- En-tête -->
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Gestion des rôles</h2>
            <p class="text-gray-600 mt-1">Attribuez des rôles aux utilisateurs en toute simplicité.</p>
        </div>

        <!-- 🔍 Barre de recherche stylisée -->
        <div class="relative mb-6">
            <input type="text" id="search-user" placeholder="Rechercher un joueur..."
                   class="w-full p-3 pl-10 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-400">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500"
                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m2.35-7A7 7 0 1111 4a7 7 0 017 7z"/>
            </svg>
        </div>

        <!-- Tableau stylisé -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-6 py-3 text-left">Pseudo</th>
                    <th class="px-6 py-3 text-left">Email</th>
                    @foreach($roles as $role)
                        <th class="px-6 py-3 text-center">{{ ucfirst($role->name) }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody id="user-table">
                @foreach($users as $user)
                    <tr class="border-b hover:bg-gray-100 transition duration-200">
                        <td class="px-6 py-4 text-gray-800">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                        @foreach($roles as $role)
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('roles.assign', $user) }}" method="POST" class="inline-block">
                                    @csrf
                                    <label class="flex justify-center">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                               class="form-checkbox h-5 w-5 text-blue-600 transition duration-200"
                                               onchange="toggleRole(this, '{{ route('roles.assign', $user) }}')"
                                            {{ $user->hasRole($role->name) ? 'checked' : '' }}>
                                    </label>
                                </form>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- 🔍 Script de recherche dynamique -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const searchInput = document.getElementById("search-user");
            const tableRows = document.querySelectorAll("#user-table tr");

            searchInput.addEventListener("keyup", function () {
                const query = searchInput.value.toLowerCase();

                tableRows.forEach(row => {
                    const userName = row.cells[0].textContent.toLowerCase();
                    const userEmail = row.cells[1].textContent.toLowerCase();

                    if (userName.includes(query) || userEmail.includes(query)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });

        function toggleRole(checkbox, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ roles: [checkbox.value] })
            })
                .then(response => response.json())
                .then(data => console.log("Rôles mis à jour:", data.roles))
                .catch(error => console.error('Erreur:', error));
        }
    </script>
</x-app-layout>
