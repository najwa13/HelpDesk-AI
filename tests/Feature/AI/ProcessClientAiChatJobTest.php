<?php

use App\Ai\Agents\ClientSupportAssistant;
use App\Enums\UserRole;
use App\Jobs\DetectSearchLanguageJob;
use App\Jobs\ProcessClientAiChatJob;
use App\Models\Article;
use App\Models\Category;
use App\Models\SearchLog;
use App\Models\User;
use App\Services\KbSearchService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->category = Category::create([
        'nom' => 'Technique',
        'description' => 'Problèmes techniques',
    ]);

    $this->client = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    $this->otherClient = User::factory()->create([
        'role' => UserRole::Client,
    ]);

    Article::create([
        'titre' => 'Configurer les notifications',
        'contenu' => 'Procédure pour gérer les notifications du compte.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    Article::create([
        'titre' => 'Modifier son profil',
        'contenu' => 'Procédure permettant de modifier son profil utilisateur.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);

    $this->article = Article::create([
        'titre' => 'Réinitialiser votre mot de passe',
        'contenu' => 'Depuis la page de connexion, cliquez sur Mot de passe oublié puis suivez les instructions reçues par email.',
        'categorie_id' => $this->category->id,
        'published_at' => now(),
    ]);
});

test('le job utilise la base de connaissances avant de répondre au client', function () {
    Bus::fake([
        DetectSearchLanguageJob::class,
    ]);

    ClientSupportAssistant::fake([
        'Pour réinitialiser votre mot de passe, cliquez sur Mot de passe oublié.',
    ])->preventStrayPrompts();

    $job = new ProcessClientAiChatJob(
        client: $this->client,
        message: 'Comment réinitialiser mon mot de passe ?',
        conversationId: null,
    );

    $job->handle(
        app(KbSearchService::class)
    );

    $searchLog = SearchLog::latest()->firstOrFail();

    expect($searchLog->client_id)
        ->toBe($this->client->id)
        ->and($searchLog->article_id)
        ->toBe($this->article->id)
        ->and($searchLog->resultat)
        ->toBe('trouve');

    expect(
        DB::table('agent_conversations')
            ->where('participant_type', User::class)
            ->where('participant_id', $this->client->id)
            ->exists()
    )->toBeTrue();
});

test('le job peut répondre quand aucun article publié ne correspond', function () {
    Bus::fake([
        DetectSearchLanguageJob::class,
    ]);

    ClientSupportAssistant::fake([
        'Je ne dispose pas d’une réponse suffisamment fiable. Je vous invite à créer un ticket.',
    ])->preventStrayPrompts();

    $job = new ProcessClientAiChatJob(
        client: $this->client,
        message: 'zxqvplmn totalement inconnu',
        conversationId: null,
    );

    $job->handle(
        app(KbSearchService::class)
    );

    $searchLog = SearchLog::latest()->firstOrFail();

    expect($searchLog->client_id)
        ->toBe($this->client->id)
        ->and($searchLog->article_id)
        ->toBeNull()
        ->and($searchLog->resultat)
        ->toBe('non_trouve');

    expect(
        DB::table('agent_conversation_messages')
            ->where('participant_type', User::class)
            ->where('participant_id', $this->client->id)
            ->where('role', 'assistant')
            ->exists()
    )->toBeTrue();
});

test('le job peut continuer une conversation appartenant au client', function () {
    Bus::fake([
        DetectSearchLanguageJob::class,
    ]);

    ClientSupportAssistant::fake([
        'Voici une précision supplémentaire.',
    ])->preventStrayPrompts();

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => User::class,
        'participant_id' => $this->client->id,
        'title' => 'Support client',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $job = new ProcessClientAiChatJob(
        client: $this->client,
        message: 'Peux-tu préciser ?',
        conversationId: $conversationId,
    );

    $job->handle(
        app(KbSearchService::class)
    );

    $messages = DB::table('agent_conversation_messages')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->get();

    expect($messages)->not->toBeEmpty();

    expect(
        $messages->pluck('role')->all()
    )->toContain('user', 'assistant');
});

test('le job refuse une conversation appartenant à un autre client', function () {
    Bus::fake([
        DetectSearchLanguageJob::class,
    ]);

    ClientSupportAssistant::fake()
        ->preventStrayPrompts();

    $conversationId = (string) Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $conversationId,
        'participant_type' => User::class,
        'participant_id' => $this->otherClient->id,
        'title' => 'Conversation privée',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $job = new ProcessClientAiChatJob(
        client: $this->client,
        message: 'Continue cette conversation.',
        conversationId: $conversationId,
    );

    expect(
        fn () => $job->handle(
            app(KbSearchService::class)
        )
    )->toThrow(
        RuntimeException::class,
        'La conversation IA n’appartient pas à ce client.'
    );
});

test('le job refuse une conversation inexistante', function () {
    Bus::fake([
        DetectSearchLanguageJob::class,
    ]);

    ClientSupportAssistant::fake()
        ->preventStrayPrompts();

    $job = new ProcessClientAiChatJob(
        client: $this->client,
        message: 'Continue cette conversation.',
        conversationId: '00000000-0000-0000-0000-000000000000',
    );

    expect(
        fn () => $job->handle(
            app(KbSearchService::class)
        )
    )->toThrow(
        RuntimeException::class,
        'La conversation IA n’appartient pas à ce client.'
    );
});
