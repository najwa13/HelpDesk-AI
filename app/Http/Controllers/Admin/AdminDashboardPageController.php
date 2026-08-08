<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AiSuggestion;
use App\Models\SearchLog;
use App\Models\Ticket;

class AdminDashboardPageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role !== UserRole::Admin) {
            abort(403);
        }

        $startOfMonth = now()->startOfMonth();
        $previousMonth = now()->subMonthNoOverflow();
        $startOfPreviousMonth = $previousMonth->copy()->startOfMonth();
        $endOfPreviousMonth = $previousMonth->copy()->endOfMonth();

        $ticketsThisMonth = Ticket::query()
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->count();

        $ticketsPreviousMonth = Ticket::query()
            ->whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth])
            ->count();

        $ticketsMonthTrend = $this->calculatePercentageTrend(
            current: $ticketsThisMonth,
            previous: $ticketsPreviousMonth
        );

        $totalSearches = SearchLog::query()->count();
        $successfulSearches = SearchLog::query()
            ->where('resultat', 'trouve')
            ->count();

        $kbResolutionRate = $totalSearches > 0
            ? round(($successfulSearches / $totalSearches) * 100, 2)
            : 0;

        $resolvedTickets = Ticket::query()
            ->where('statut', TicketStatus::Resolu)
            ->count();

        $aiAnalyses = AiSuggestion::query()->count();

        $stats = [
            'tickets_this_month' => [
                'value' => $ticketsThisMonth,
                'trend_percentage' => $ticketsMonthTrend,
            ],
            'kb_resolution_rate' => [
                'value' => $kbResolutionRate,
                'unit' => '%',
            ],
            'resolved_tickets' => [
                'value' => $resolvedTickets,
            ],
            'ai_analyses' => [
                'value' => $aiAnalyses,
            ],
        ];

        $ticketsByMonth = $this->ticketsByMonth();
        $categoryDistribution = $this->categoryDistribution();
        $ticketsByCategoryOverTime = $this->ticketsByCategoryOverTime();
        $recentTickets = $this->recentTickets();

        return view('admin.dashboard', compact(
            'stats',
            'ticketsByMonth',
            'categoryDistribution',
            'ticketsByCategoryOverTime',
            'recentTickets'
        ));
    }

    private function calculatePercentageTrend(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return $current === 0 ? 0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function ticketsByMonth(): array
    {
        $start = now()->subMonths(5)->startOfMonth();

        $rows = Ticket::query()
            ->selectRaw(
                'YEAR(created_at) as year,
                 MONTH(created_at) as month,
                 COUNT(*) as total'
            )
            ->where('created_at', '>=', $start)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->year, $row->month));

        $result = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $key = $date->format('Y-m');

            $result[] = [
                'month' => $key,
                'label' => $date->locale('fr')->translatedFormat('M'),
                'total' => (int) ($rows[$key]->total ?? 0),
            ];
        }

        return $result;
    }

    private function categoryDistribution(): array
    {
        $total = Ticket::query()->count();

        return Ticket::query()
            ->join('categories', 'categories.id', '=', 'tickets.categorie_id')
            ->selectRaw(
                'categories.id,
                 categories.nom,
                 COUNT(tickets.id) as total'
            )
            ->groupBy('categories.id', 'categories.nom')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) use ($total) {
                return [
                    'category_id' => $row->id,
                    'category' => $row->nom,
                    'total' => (int) $row->total,
                    'percentage' => $total > 0
                        ? round(($row->total / $total) * 100, 2)
                        : 0,
                ];
            })
            ->all();
    }

    private function ticketsByCategoryOverTime(): array
    {
        $start = now()->subMonths(5)->startOfMonth();

        return Ticket::query()
            ->join('categories', 'categories.id', '=', 'tickets.categorie_id')
            ->selectRaw(
                'categories.id as category_id,
                 categories.nom as category,
                 YEAR(tickets.created_at) as year,
                 MONTH(tickets.created_at) as month,
                 COUNT(tickets.id) as total'
            )
            ->where('tickets.created_at', '>=', $start)
            ->groupBy('categories.id', 'categories.nom')
            ->groupByRaw('YEAR(tickets.created_at), MONTH(tickets.created_at)')
            ->orderBy('categories.nom')
            ->orderByRaw('YEAR(tickets.created_at), MONTH(tickets.created_at)')
            ->get()
            ->map(function ($row) {
                return [
                    'category_id' => $row->category_id,
                    'category' => $row->category,
                    'month' => sprintf('%04d-%02d', $row->year, $row->month),
                    'total' => (int) $row->total,
                ];
            })
            ->all();
    }

    private function recentTickets(): array
    {
        return Ticket::query()
            ->with([
                'client:id,name',
                'categorie:id,nom',
                'agent:id,name',
            ])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function (Ticket $ticket) {
                return [
                    'id' => $ticket->id,
                    'titre' => $ticket->titre,
                    'statut' => $ticket->statut->value,
                    'priorite' => $ticket->priorite?->value,
                    'client' => [
                        'id' => $ticket->client?->id,
                        'name' => $ticket->client?->name,
                    ],
                    'agent' => $ticket->agent
                        ? [
                            'id' => $ticket->agent->id,
                            'name' => $ticket->agent->name,
                        ]
                        : null,
                    'categorie' => [
                        'id' => $ticket->categorie?->id,
                        'nom' => $ticket->categorie?->nom,
                    ],
                    'created_at' => $ticket->created_at?->toISOString(),
                ];
            })
            ->all();
    }
}
