<x-app-layout>
    <div class="w-full min-h-screen p-20 bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white shadow-xl rounded-lg">

        <!-- Profil et Statistiques -->
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg w-full">
            <div class="flex items-center">
                <!-- Icône du joueur -->
                <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/profileicon/{{ $summoner['profileIconId'] }}.png"
                     alt="Icône de {{ $summoner['gameName'] }}"
                     class="w-20 h-20 rounded-full border-4 border-yellow-500 shadow-lg">

                <!-- Infos du joueur -->
                <div class="ml-6">
                    <h2 class="text-4xl font-bold text-yellow-400">{{ $summoner['gameName'] }}</h2>
                    <p class="text-lg text-gray-300">
                        <strong>{{ $user->lolProfile->riot_pseudo }}#{{ $user->lolProfile->riot_tag }}</strong>
                    </p>
                    <p class="text-gray-400">Niveau : <strong>{{ $summoner['summonerLevel'] }}</strong></p>
                </div>
            </div>

            <!-- Statistiques du joueur (basées sur les 10 derniers matchs) -->
            <div class="grid grid-cols-3 gap-4 mt-6 bg-gray-700 p-4 rounded-lg shadow-md">
                <!-- Taux de victoire -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-yellow-300">🎯 Taux de Victoire</h3>
                    <p class="text-2xl font-bold text-green-400">
                        {{ round(($stats['wins'] / max(1, $stats['totalGames'])) * 100, 1) }}%
                    </p>
                    <p class="text-gray-300">{{ $stats['wins'] }} Victoires / {{ $stats['losses'] }} Défaites</p>
                </div>

                <!-- KDA moyen -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-yellow-300">⚔️ KDA Moyen</h3>
                    <p class="text-2xl font-bold text-red-400">
                        {{ round(($stats['kills'] + $stats['assists']) / max(1, $stats['deaths']), 2) }}:1
                    </p>
                    <p class="text-gray-300">
                        {{ round($stats['kills'] / max(1, $stats['totalGames']), 1) }} /
                        {{ round($stats['deaths'] / max(1, $stats['totalGames']), 1) }} /
                        {{ round($stats['assists'] / max(1, $stats['totalGames']), 1) }}
                    </p>
                </div>

                <!-- Champions les plus joués -->
                <div class="text-center">
                    <h3 class="text-lg font-semibold text-yellow-300">🔥 Champions les plus joués</h3>
                    @foreach (array_slice($stats['championsPlayed'], 0, 3) as $champion => $data)
                        <p class="text-gray-300">
                            {{ $champion }} - <span class="text-green-300">{{ round(($data['wins'] / max(1, $data['games'])) * 100, 1) }}% WR</span>
                        </p>
                    @endforeach
                </div>
            </div>
        </div>



        <!-- Historique des matchs -->
        <div class="mt-8">
            <h3 class="text-2xl font-semibold text-yellow-300">📜 Historique des matchs</h3>
            <div id="match-list" class="mt-4 space-y-4">
                @foreach ($matches as $match)
                    <div class="p-4 rounded-lg shadow-md w-full flex items-center justify-between
                                {{ $match['win'] ? 'bg-green-800' : 'bg-red-800' }}
                                transition-all duration-300 hover:scale-105">

                        <!-- Section gauche : Icône du champion + Infos principales -->
                        <div class="flex items-center space-x-3">
                            <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/champion/{{ $match['champion'] }}.png"
                                 alt="{{ $match['champion'] }}" class="w-14 h-14 rounded-full border-2 border-gray-500 shadow-lg">
                            <div>
                                <p class="text-md font-bold {{ $match['win'] ? 'text-green-300' : 'text-red-300' }}">
                                    {{ $match['win'] ? '✅ Victoire' : '❌ Défaite' }}
                                </p>
                                <p class="text-gray-300 text-sm">Champion : <strong>{{ $match['champion'] }}</strong></p>
                            </div>
                        </div>

                        <!-- Section centrale : Détails de la partie -->
                        <div class="flex flex-col items-center">
                            <p class="text-gray-300 text-sm"><span class="font-semibold">⏳ Durée:</span> {{ $match['gameDuration'] }}</p>
                        </div>

                        <div class="flex flex-col items-center">
                            <p class="text-gray-300 text-sm"><span class="font-semibold">🧹 CS:</span> {{ $match['cs'] }}</p>
                        </div>

                        <!-- Bouton Détails -->
                        <button class="px-3 py-1 bg-gray-900 hover:bg-gray-700 text-white rounded-lg shadow-md transition-all duration-300 toggle-details"
                                data-match-id="{{ $match['matchId'] }}">
                            📊 Détails
                        </button>

                    </div>

                    <!-- Détails du match -->
                    <div class="match-details hidden overflow-hidden transition-all duration-300 mt-2 p-3 bg-gray-700 rounded-lg"></div>
                @endforeach
            </div>
        </div>

        <!-- Bouton Charger Plus de Parties -->
        <div class="mt-6 text-center">
            <button id="loadMoreMatches"
                    class="px-6 py-3 bg-gray-900 hover:bg-gray-700 text-white rounded-lg shadow-md transition-all duration-300">
                📜 Charger Plus de Parties
            </button>
        </div>

    </div>

    <script>
        document.getElementById('loadMoreMatches').addEventListener('click', function() {
            let button = this;
            button.innerHTML = "⏳ Chargement...";
            button.disabled = true;

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
                        <div class="p-4 rounded-lg shadow-md w-full flex items-center justify-between
                                    ${match.win ? 'bg-green-800' : 'bg-red-800'}
                                    transition-all duration-300 hover:scale-105">

                            <div class="flex items-center space-x-3">
                                <img src="https://ddragon.leagueoflegends.com/cdn/13.24.1/img/champion/${match.champion}.png"
                                    class="w-14 h-14 rounded-full border-2 border-gray-500 shadow-lg">
                                <div>
                                    <p class="text-md font-bold ${match.win ? 'text-green-300' : 'text-red-300'}">
                                        ${match.win ? '✅ Victoire' : '❌ Défaite'}
                                    </p>
                                    <p class="text-gray-300 text-sm">Champion : <strong>${match.champion}</strong></p>
                                </div>
                            </div>

                            <div class="flex flex-col items-center">
                                <p class="text-gray-300 text-sm"><span class="font-semibold">⏳ Durée:</span> ${match.gameDuration}</p>
                            </div>

                            <div class="flex flex-col items-center">
                                <p class="text-gray-300 text-sm"><span class="font-semibold">🧹 CS:</span> ${match.cs}</p>
                            </div>

                            <button class="px-3 py-1 bg-gray-900 hover:bg-gray-700 text-white rounded-lg shadow-md transition-all duration-300 toggle-details"
                                    data-match-id="${match.matchId}">
                                📊 Détails
                            </button>
                        </div>

                        <div class="match-details hidden overflow-hidden transition-all duration-300 mt-2 p-3 bg-gray-700 rounded-lg"></div>`;

                        matchList.innerHTML += matchHtml;
                    });

                    button.innerHTML = "📜 Charger Plus de Parties";
                    button.disabled = false;

                    attachDetailEventListeners();
                })
                .catch(error => {
                    alert("Erreur lors du chargement des matchs.");
                    button.innerHTML = "📜 Charger Plus de Parties";
                    button.disabled = false;
                });
        });

        function attachDetailEventListeners() {
            document.querySelectorAll('.toggle-details').forEach(button => {
                button.addEventListener('click', function() {
                    let matchId = this.dataset.matchId;
                    let detailsContainer = this.parentElement.nextElementSibling; // Trouver le bon conteneur sous le bouton

                    // Vérifier si le conteneur des détails existe
                    if (!detailsContainer) {
                        console.error("Impossible de trouver le conteneur des détails pour le match :", matchId);
                        return;
                    }

                    // Sauvegarder le texte original du bouton
                    let originalText = this.innerHTML;

                    // Modifier le bouton pour afficher "⏳ Chargement..."
                    this.innerHTML = "⏳ Chargement...";
                    this.disabled = true; // Désactiver temporairement le bouton

                    // Vérifier si les détails ont déjà été chargés
                    if (!detailsContainer.dataset.loaded) {
                        // Ajouter un indicateur de chargement dans le cadre des détails
                        detailsContainer.innerHTML = `
                    <div class="loading-spinner w-full flex justify-center items-center py-4">
                        <svg class="animate-spin h-8 w-8 text-white" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0H4z"></path>
                        </svg>
                    </div>
                `;

                        // Faire la requête pour récupérer les détails
                        fetch(`/match/details/${matchId}`)
                            .then(response => response.json())
                            .then(data => {
                                detailsContainer.innerHTML = formatMatchDetails(data);
                                detailsContainer.classList.remove('hidden');
                                detailsContainer.dataset.loaded = "true"; // Marquer comme chargé
                            })
                            .catch(error => {
                                console.error("Erreur chargement détails:", error);
                                detailsContainer.innerHTML = "<p class='text-red-500'>Erreur lors du chargement des détails.</p>";
                            })
                            .finally(() => {
                                // Restaurer le texte du bouton
                                this.innerHTML = originalText;
                                this.disabled = false; // Réactiver le bouton
                            });
                    } else {
                        // Toggle pour cacher/afficher
                        detailsContainer.classList.toggle('hidden');

                        // Restaurer le texte du bouton immédiatement
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }
                });
            });
        }

        // Appeler la fonction pour l'attacher aux boutons
        attachDetailEventListeners();



        // Dictionnaire des runes principales (déjà correct)
        const primaryRunes = {
            8005: "Precision/PressTheAttack/PressTheAttack",
            8008: "Precision/LethalTempo/LethalTempoTemp",
            8021: "Precision/FleetFootwork/FleetFootwork",
            8010: "Precision/Conqueror/Conqueror",
            8112: "Domination/Electrocute/Electrocute",
            8124: "Domination/Predator/Predator",
            8128: "Domination/DarkHarvest/DarkHarvest",
            9923: "Domination/HailOfBlades/HailOfBlades",
            8214: "Sorcery/SummonAery/SummonAery",
            8229: "Sorcery/ArcaneComet/ArcaneComet",
            8230: "Sorcery/PhaseRush/PhaseRush"
        };

        // Dictionnaire des arbres secondaires avec les bons chemins
        // Dictionnaire des arbres secondaires avec les bons chemins
        const secondaryRunes = {
            8000: "Precision",
            8100: "Domination",
            8200: "Sorcery",
            8300: "Inspiration",
            8400: "Resolve"
        };

        function formatMatchDetails(data) {
            let latestVersion = data.teams[0]?.players[0]?.latestVersion || "latest";

            let html = `<div class="grid grid-cols-2 gap-4">`;

            data.teams.forEach(team => {
                html += `<div class="p-3 bg-gray-800 rounded-lg shadow-md">`;
                html += `<h4 class="text-lg font-bold text-white text-center">${team.name} ${team.win ? '🏆' : '❌'}</h4>`;

                team.players.forEach(player => {
                    let playerName = player.name ? player.name : "Inconnu";

                    let primaryRunePath = player.primaryRune ? player.primaryRune : "https://ddragon.leagueoflegends.com/cdn/img/perk-images/placeholder.png";
                    let secondaryRunePath = player.secondaryRune ? player.secondaryRune : "https://ddragon.leagueoflegends.com/cdn/img/perk-images/placeholder.png";

                    let items = Array.isArray(player.items) ? player.items.filter(item => item) : [];

                    html += `
            <div class="flex items-center space-x-3 p-2 border-b border-gray-700">
                <img src="https://ddragon.leagueoflegends.com/cdn/${latestVersion}/img/champion/${player.champion}.png"
                     class="w-10 h-10 rounded-full border-2 border-gray-500 shadow-md">
                <div class="text-white w-40">
                    <p class="text-md font-bold truncate">${playerName}</p>
                    <p class="text-xs text-gray-300">KDA: <span class="text-yellow-300">${player.kda}</span></p>
                    <p class="text-xs text-gray-300">CS: <span class="text-yellow-300">${player.cs}</span> | Dégâts: <span class="text-yellow-300">${player.dmg}</span></p>

                    <!-- Runes -->
                    <div class="flex items-center mt-1">
                        <img src="${primaryRunePath}"
                            onerror="this.onerror=null; this.src='https://ddragon.leagueoflegends.com/cdn/img/perk-images/placeholder.png';"
                            class="w-6 h-6 rounded-full border border-gray-500 shadow-md">
                        <img src="${secondaryRunePath}"
                            onerror="this.onerror=null; this.src='https://ddragon.leagueoflegends.com/cdn/img/perk-images/placeholder.png';"
                            class="w-6 h-6 rounded-full border border-gray-500 shadow-md ml-1">
                    </div>

                    <!-- Objets -->
                    <div class="flex items-center mt-2">
                        ${items.length > 0 ? items.map(item =>
                        `<img src="https://ddragon.leagueoflegends.com/cdn/${latestVersion}/img/item/${item}.png"
                              class="w-6 h-6 border border-gray-500 shadow-md ml-1">`
                    ).join('') : '<p class="text-xs text-gray-400">Aucun objet</p>'}
                    </div>
                </div>
            </div>`;
                });

                html += `</div>`;
            });

            html += `</div>`;
            return html;
        }

    </script>
</x-app-layout>
