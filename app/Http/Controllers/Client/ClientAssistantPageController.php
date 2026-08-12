<?php

namespace App\Http\Controllers\Client;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientAssistantPageController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role !== UserRole::Client) {
            abort(403);
        }

        return view('client.assistant');
    }
}
