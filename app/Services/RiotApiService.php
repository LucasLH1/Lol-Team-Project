<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RiotApiService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.riot.api_key'); // Récupération de la clé API depuis .env
    }

    /**
     * Récupérer les infos de base d'un joueur via son Riot ID (Pseudo + Tag)
     */
    public function getSummonerByRiotId($riotPseudo, $riotTag)
    {
        $url = "https://europe.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{$riotPseudo}/{$riotTag}";

        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->get($url);

        return $response->json();
    }


    public function getSummonerProfile($puuid, $region = "euw1")
    {
        $url = "https://{$region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{$puuid}";

        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->get($url);

        return $response->json();
    }

    /**
     * Récupérer l'historique des matchs d'un joueur via son PUUID
     */
    public function getMatchHistory($puuid, $region = "europe", $start = 0, $count = 10)
    {
        $url = "https://{$region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{$puuid}/ids?start={$start}&count={$count}";

        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->get($url);

        if ($response->failed()) {
            \Log::error("Erreur API Riot - getMatchHistory: " . $response->body());
            return [];
        }

        return $response->json();
    }



    /**
     * Récupérer les détails d'un match
     */
    public function getMatchDetails($matchId, $region = "europe")
    {
        $url = "https://{$region}.api.riotgames.com/lol/match/v5/matches/{$matchId}";

        $response = Http::withHeaders([
            'X-Riot-Token' => $this->apiKey,
        ])->get($url);

        return $response->json();
    }

    /**
     * Récupérer les matchs détaillés et calculer les statistiques du joueur
     */
    public function getDetailedStats($puuid, $region = "europe")
    {
        $matchIds = $this->getMatchHistory($puuid);
        $stats = [
            'totalGames' => 0,
            'wins' => 0,
            'losses' => 0,
            'kills' => 0,
            'deaths' => 0,
            'assists' => 0,
            'championsPlayed' => [],
            'roleCount' => [],
        ];

        foreach ($matchIds as $matchId) {
            $matchData = $this->getMatchDetails($matchId);
            $playerData = collect($matchData['info']['participants'])
                ->where('puuid', $puuid)
                ->first();

            if (!$playerData) {
                continue;
            }

            // Calculer les stats
            $stats['totalGames']++;
            $stats['wins'] += $playerData['win'] ? 1 : 0;
            $stats['losses'] += !$playerData['win'] ? 1 : 0;
            $stats['kills'] += $playerData['kills'];
            $stats['deaths'] += $playerData['deaths'];
            $stats['assists'] += $playerData['assists'];

            // Compter les champions les plus joués
            if (!isset($stats['championsPlayed'][$playerData['championName']])) {
                $stats['championsPlayed'][$playerData['championName']] = [
                    'games' => 0,
                    'wins' => 0,
                    'kills' => 0,
                    'deaths' => 0,
                    'assists' => 0
                ];
            }
            $stats['championsPlayed'][$playerData['championName']]['games']++;
            $stats['championsPlayed'][$playerData['championName']]['wins'] += $playerData['win'] ? 1 : 0;
            $stats['championsPlayed'][$playerData['championName']]['kills'] += $playerData['kills'];
            $stats['championsPlayed'][$playerData['championName']]['deaths'] += $playerData['deaths'];
            $stats['championsPlayed'][$playerData['championName']]['assists'] += $playerData['assists'];

            // Compter les rôles joués
            $role = $playerData['teamPosition'] ?? 'UNKNOWN';
            if (!isset($stats['roleCount'][$role])) {
                $stats['roleCount'][$role] = 0;
            }
            $stats['roleCount'][$role]++;
        }

        return $stats;
    }

}

