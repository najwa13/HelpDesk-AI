<?php

namespace App\Services;

use App\Jobs\DetectSearchLanguageJob;
use App\Models\Article;
use App\Models\SearchLog;
use App\Models\User;
use Illuminate\Support\Str;

class KbSearchService
{
    public function search(string $requete, User $client): SearchLog
    {
        $requeteNormalisee = $this->normaliser($requete);

        $article = Article::publies()
            ->selectRaw(
                'articles.*, MATCH(titre, contenu) AGAINST(? IN NATURAL LANGUAGE MODE) AS score_correspondance',
                [$requeteNormalisee]
            )
            ->whereRaw(
                'MATCH(titre, contenu) AGAINST(? IN NATURAL LANGUAGE MODE)',
                [$requeteNormalisee]
            )
            ->orderByDesc('score_correspondance')
            ->first();

        $searchLog = SearchLog::create([
            'requete_originale' => $requete,
            'requete_normalisee' => $requeteNormalisee,
            'langue_detectee' => null,
            'resultat' => $article ? 'trouve' : 'non_trouve',
            'score_correspondance' => $article?->score_correspondance,
            'client_id' => $client->id,
            'article_id' => $article?->id,
            'ticket_id' => null,
        ]);

        DetectSearchLanguageJob::dispatch($searchLog);

        return $searchLog->load('article');
    }

    private function normaliser(string $requete): string
    {
        return Str::of($requete)
            ->lower()
            ->squish()
            ->toString();
    }
}
