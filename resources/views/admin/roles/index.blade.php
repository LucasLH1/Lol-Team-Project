<x-app-layout>
    <div class="w-full p-6 bg-white shadow-lg rounded-lg">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Gestion des rôles</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="px-4 py-2 border">Nom</th>
                    <th class="px-4 py-2 border">Email</th>
                    @foreach($roles as $role)
                        <th class="px-4 py-2 border">{{ $role->name }}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr class="text-center hover:bg-gray-100">
                        <td class="px-4 py-2 border">{{ $user->name }}</td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        @foreach($roles as $role)
                            <td class="px-4 py-2 border">
                                <form action="{{ route('roles.assign', $user) }}" method="POST" class="inline-block">
                                    @csrf
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-checkbox h-5 w-5 text-blue-600" onchange="toggleRole(this, '{{ route('roles.assign', $user) }}')" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
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

    <script>
        function toggleRole(checkbox, url) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ roles: checkbox.checked ? [checkbox.value] : [] })
            }).then(response => response.json())
                .then(data => console.log(data))
                .catch(error => console.error('Erreur:', error));
        }
    </script>
</x-app-layout>
