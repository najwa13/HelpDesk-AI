<?php

namespace App\Jobs;

use App\Models\SearchLog;
use App\Services\LanguageDetectionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DetectSearchLanguageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public SearchLog $searchLog
    ) {}

    public function handle(LanguageDetectionService $languageDetector): void
    {
        $language = $languageDetector->detect(
            $this->searchLog->requete_originale
        );

        if ($language === null) {
            return;
        }

        $this->searchLog->update([
            'langue_detectee' => $language,
        ]);
    }
}
