<?php

namespace App\Http\Controllers;

use App\Services\RiotApiService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RiotController extends Controller
{
    protected $riotApi;

    public function __construct(RiotApiService $riotApi)
    {
        $this->riotApi = $riotApi;
    }

    /**
     * Récupérer et afficher les infos d'un joueur connecté
     */
    public function showProfile()
    {
        $user = auth()->user();

        if (!$user->lolProfile) {
            return redirect()->route('dashboard')->with('error', 'Aucun profil League of Legends trouvé.');
        }

        // Définir les clés de cache pour chaque élément
        $cacheProfileKey = 'profile_' . $user->id;
        $cacheStatsKey = 'stats_' . $user->id;
        $cacheMatchesKey = 'matches_' . $user->id;

        // Vérifier si le profil est en cache
        $summoner = Cache::get($cacheProfileKey);

        if (!$summoner) {
            // Récupérer les infos du joueur via Riot API
            $summoner = $this->riotApi->getSummonerByRiotId($user->lolProfile->riot_pseudo, $user->lolProfile->riot_tag);

            if (!isset($summoner['puuid'])) {
                return redirect()->route('dashboard')->with('error', 'Impossible de récupérer les infos du joueur.');
            }


            // Récupérer les vraies infos en jeu (icône + niveau)
            $profileData = $this->riotApi->getSummonerProfile($summoner['puuid']);

            // Ajouter les nouvelles infos au tableau
            $summoner['profileIconId'] = $profileData['profileIconId'] ?? 1;
            $summoner['summonerLevel'] = $profileData['summonerLevel'] ?? 'N/A';

            // Stocker le profil en cache pendant 12 heures
            Cache::put($cacheProfileKey, $summoner, now()->addHours(12));
        }

        // Vérifier si les statistiques sont en cache
        $stats = Cache::get($cacheStatsKey);

        if (!$stats) {
            // Récupérer les statistiques avancées du joueur
            $stats = $this->riotApi->getDetailedStats($summoner['puuid']);

            // Stocker les statistiques en cache pendant 1 heure
            Cache::put($cacheStatsKey, $stats, now()->addHours(12));
        }

        // Vérifier si les matchs sont en cache
        $matches = Cache::get($cacheMatchesKey);

        if (!$matches) {
            // Récupérer l'historique des matchs
            $matchIds = $this->riotApi->getMatchHistory($summoner['puuid']);

            // Récupérer les détails des matchs
            $matches = [];
            foreach ($matchIds as $matchId) {
                $matchData = $this->riotApi->getMatchDetails($matchId);
                $playerData = collect($matchData['info']['participants'])
                    ->where('puuid', $summoner['puuid'])
                    ->first();

                $matches[] = [
                    'matchId' => $matchId,
                    'win' => $playerData['win'],
                    'champion' => $playerData['championName'],
                    'cs' => $playerData['totalMinionsKilled'],
                    'gameDuration' => gmdate("i:s", $matchData['info']['gameDuration']),
                ];
            }

            // Stocker les matchs en cache pendant 30 minutes
            Cache::put($cacheMatchesKey, $matches, now()->addHours(12));
        }

        return view('riot.profile', compact('user', 'summoner', 'stats', 'matches'));
    }

    public function loadMoreMatches()
    {
        $user = auth()->user();
        $cacheMatchesKey = 'matches_' . $user->id;
        $cacheStartKey = 'matches_start_' . $user->id;

        // Récupérer le profil et vérifier si le joueur existe
        $summoner = Cache::get('profile_' . $user->id);
        if (!$summoner) {
            return response()->json(['error' => 'Impossible de charger plus de matchs, profil introuvable.'], 400);
        }

        // Récupérer les matchs existants en cache et la position actuelle
        $matches = Cache::get($cacheMatchesKey, []);
        $existingMatchIds = array_column($matches, 'matchId'); // Liste des matchs déjà chargés
        $start = Cache::get($cacheStartKey, count($existingMatchIds)); // Utiliser la longueur actuelle

        // Récupérer 10 nouveaux matchs en utilisant la pagination
        $matchIds = $this->riotApi->getMatchHistory($summoner['puuid'], "europe", $start, 10);

        if (empty($matchIds)) {
            return response()->json(['error' => 'Aucun match supplémentaire trouvé.'], 400);
        }

        $newMatches = [];
        foreach ($matchIds as $matchId) {
            // Vérifier si le match est déjà dans la liste
            if (in_array($matchId, $existingMatchIds)) {
                continue;
            }

            $matchData = $this->riotApi->getMatchDetails($matchId);
            $playerData = collect($matchData['info']['participants'])
                ->where('puuid', $summoner['puuid'])
                ->first();

            if (!$playerData) {
                continue;
            }

            $newMatches[] = [
                'matchId' => $matchId,
                'win' => $playerData['win'],
                'champion' => $playerData['championName'],
                'cs' => $playerData['totalMinionsKilled'],
                'gameDuration' => gmdate("i:s", $matchData['info']['gameDuration']),
            ];
        }

        // Ajouter uniquement les nouveaux matchs
        if (empty($newMatches)) {
            return response()->json(['error' => 'Aucun nouveau match disponible.'], 400);
        }

        $updatedMatches = array_merge($matches, $newMatches);

        // Mettre à jour le cache avec les nouveaux matchs et la nouvelle position `start`
        Cache::put($cacheMatchesKey, $updatedMatches, now()->addMinutes(30));
        Cache::put($cacheStartKey, count($updatedMatches), now()->addMinutes(30)); // Mettre à jour `start`

        return response()->json(['matches' => $newMatches]);
    }

    public function getMatchDetailsAjax($matchId)
    {
        $cacheKey = "match_details_{$matchId}";

        // Vérifier si les détails sont déjà en cache
        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $matchData = $this->riotApi->getMatchDetails($matchId);
        $latestVersion = $this->riotApi->getLatestVersion();
        $runeIcons = $this->riotApi->getRunes();

        $teams = [
            'blue' => ['name' => 'Équipe Bleue', 'win' => false, 'players' => []],
            'red' => ['name' => 'Équipe Rouge', 'win' => false, 'players' => []]
        ];

        foreach ($matchData['info']['participants'] as $player) {
            $team = $player['teamId'] == 100 ? 'blue' : 'red';
            $teams[$team]['win'] = $player['win'];

            $primaryRuneId = $player['perks']['styles'][0]['selections'][0]['perk'] ?? null;
            $secondaryRuneId = $player['perks']['styles'][1]['style'] ?? null;

            $teams[$team]['players'][] = [
                'name' => $player['summonerName'],
                'champion' => $player['championName'],
                'kda' => "{$player['kills']}/{$player['deaths']}/{$player['assists']}",
                'cs' => $player['totalMinionsKilled'],
                'dmg' => $player['totalDamageDealtToChampions'],
                'primaryRune' => isset($runeIcons[$primaryRuneId]) ? "https://ddragon.leagueoflegends.com/cdn/img/{$runeIcons[$primaryRuneId]}" : null,
                'secondaryRune' => isset($runeIcons[$secondaryRuneId]) ? "https://ddragon.leagueoflegends.com/cdn/img/{$runeIcons[$secondaryRuneId]}" : null,
                'items' => [
                    $player['item0'], $player['item1'], $player['item2'],
                    $player['item3'], $player['item4'], $player['item5'], $player['item6']
                ],
                'latestVersion' => $latestVersion
            ];
        }

        $responseData = ['teams' => array_values($teams)];

        // Stocker en cache pour 1 heure
        Cache::put($cacheKey, $responseData, now()->addHour());

        return response()->json($responseData);
    }










}

