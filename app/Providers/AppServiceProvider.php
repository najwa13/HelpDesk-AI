<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Ticket;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Article::class, ArticlePolicy::class);
    }
}
