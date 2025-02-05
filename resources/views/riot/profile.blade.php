<x-app-layout>
    <div class="w-full p-8 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white shadow-xl rounded-lg">

        <!-- Section Profil + Statistiques -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-full transition transform">
            <div class="flex items-center">
                <!-- Icône du joueur -->
                <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/profileicon/{{ $summoner['profileIconId'] }}.png"
                     alt="Icône de {{ $summoner['gameName'] }}"
                     class="w-24 h-24 rounded-full border-4 border-yellow-500 shadow-lg">

                <!-- Infos du joueur -->
                <div class="ml-6">
                    <h2 class="text-4xl font-extrabold text-yellow-400">{{ $summoner['gameName'] }}</h2>
                    <p class="text-lg text-gray-300">
                        Riot ID: <strong>{{ $user->lolProfile->riot_pseudo }}#{{ $user->lolProfile->riot_tag }}</strong>
                    </p>
                    <p class="text-gray-400">Niveau : <strong>{{ $summoner['summonerLevel'] }}</strong></p>
                </div>
            </div>

            <!-- Statistiques Globales -->
            <div class="grid grid-cols-3 gap-6 mt-6 bg-gray-700 p-4 rounded-lg shadow-md">
                <div class="text-center">
                    <h3 class="text-xl font-semibold text-yellow-300">🎯 Taux de Victoire</h3>
                    <p class="text-3xl font-bold text-green-400 animate-pulse">
                        {{ round(($stats['wins'] / max(1, $stats['totalGames'])) * 100, 1) }}%
                    </p>
                    <p class="text-gray-300">{{ $stats['wins'] }} Victoires / {{ $stats['losses'] }} Défaites</p>
                </div>

                <div class="text-center">
                    <h3 class="text-xl font-semibold text-yellow-300">⚔️ KDA Moyen</h3>
                    <p class="text-3xl font-bold text-red-400">
                        {{ round(($stats['kills'] + $stats['assists']) / max(1, $stats['deaths']), 2) }}:1
                    </p>
                    <p class="text-gray-300">{{ round($stats['kills'] / max(1, $stats['totalGames']), 1) }} /
                        {{ round($stats['deaths'] / max(1, $stats['totalGames']), 1) }} /
                        {{ round($stats['assists'] / max(1, $stats['totalGames']), 1) }}
                    </p>
                </div>

                <div class="text-center">
                    <h3 class="text-xl font-semibold text-yellow-300">🔥 Champions les plus joués</h3>
                    @foreach (array_slice($stats['championsPlayed'], 0, 3) as $champion => $data)
                        <p class="text-gray-300">
                            {{ $champion }} - <span class="text-green-300">{{ round(($data['wins'] / max(1, $data['games'])) * 100, 1) }}% WR</span>
                        </p>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Historique des matchs -->
        <div class="mt-8 p-6">
            <h3 class="text-3xl font-semibold text-yellow-300">📜 Historique des matchs</h3>
            <div id="match-list" class="mt-6 space-y-6 w-full">
                @foreach ($matches as $match)
                    <div class="p-6 rounded-lg shadow-md flex items-center justify-between w-full
                                {{ $match['win'] ? 'bg-green-800' : 'bg-red-800' }}
                                transition transform hover:scale-105 hover:shadow-2xl">
                        <div class="flex items-center space-x-4">
                            <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/champion/{{ $match['champion'] }}.png"
                                 alt="{{ $match['champion'] }}" class="w-16 h-16 rounded-full border-4 border-gray-500 shadow-lg">
                            <div>
                                <p class="text-lg font-bold {{ $match['win'] ? 'text-green-300' : 'text-red-300' }}">
                                    {{ $match['win'] ? '✅ Victoire' : '❌ Défaite' }}
                                </p>
                                <p class="text-gray-300">Champion : <strong>{{ $match['champion'] }}</strong></p>
                            </div>
                        </div>

                        <div class="flex flex-col items-center">
                            <p class="text-gray-300 text-lg font-semibold">⏳ {{ $match['gameDuration'] }}</p>
                        </div>

                        <div class="flex flex-col items-center">
                            <p class="text-yellow-400 text-lg font-bold">{{ $match['cs'] }}</p>
                        </div>

                        <div>
                            <a href="javascript:void(0)"
                               class="px-4 py-2 bg-gray-900 hover:bg-gray-700 text-white rounded-lg shadow-md transition">
                                📊 Détails
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bouton Charger Plus de Parties -->
        <div class="mt-6 text-center">
            <button id="loadMoreMatches"
                    class="px-6 py-3 bg-gray-900 hover:bg-gray-700 text-white rounded-lg shadow-md transition duration-300 transform hover:scale-105">
                📜 Charger Plus de Parties
            </button>
        </div>

    </div>

    <script>
        document.getElementById('loadMoreMatches').addEventListener('click', function() {
            this.innerHTML = "⏳ Chargement...";
            fetch("{{ route('riot.loadMore') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert("Erreur: " + data.error);
                        return;
                    }

                    let matchList = document.getElementById('match-list');

                    data.matches.forEach(match => {
                        let matchHtml = `
                        <div class="p-6 rounded-lg shadow-md flex items-center justify-between w-full
                                    ${match.win ? 'bg-green-800' : 'bg-red-800'}
                                    transition transform hover:scale-105 hover:shadow-2xl">
                            <div class="flex items-center space-x-4">
                                <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/champion/${match.champion}.png"
                                    class="w-16 h-16 rounded-full border-4 border-gray-500 shadow-lg">
                                <div>
                                    <p class="text-lg font-bold ${match.win ? 'text-green-300' : 'text-red-300'}">
                                        ${match.win ? '✅ Victoire' : '❌ Défaite'}
                                    </p>
                                    <p class="text-gray-300">Champion : <strong>${match.champion}</strong></p>
                                </div>
                            </div>
                        </div>`;
                        matchList.innerHTML += matchHtml;
                    });

                    document.getElementById('loadMoreMatches').innerHTML = "📜 Charger Plus de Parties";
                })
                .catch(error => alert("Erreur lors du chargement des matchs."));
        });
    </script>
</x-app-layout>
