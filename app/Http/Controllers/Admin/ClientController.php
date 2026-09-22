<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display the lab clients (borrowers) directory, scoped to the user's laboratory role.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $clients = User::query()
            ->whereIn('role', ['klien', 'student'])
            ->when($user->isLaboran(), fn (Builder $query) => $query
                ->whereHas('loanRequests', fn (Builder $inner) => $inner->where('laboratory_id', $user->laboratory_id))
                ->withCount(['loanRequests' => fn (Builder $inner) => $inner->where('laboratory_id', $user->laboratory_id)]))
            ->when($user->isKepalaLab() && $request->filled('laboratory_id'), fn (Builder $query) => $query
                ->whereHas('loanRequests', fn (Builder $inner) => $inner->where('laboratory_id', $request->integer('laboratory_id'))))
            ->when($user->isKepalaLab(), fn (Builder $query) => $query->withCount([
                'loanRequests' => fn (Builder $inner) => $inner->when(
                    $request->filled('laboratory_id'),
                    fn (Builder $scoped) => $scoped->where('laboratory_id', $request->integer('laboratory_id')),
                ),
            ]))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.clients.index', [
            'clients' => $clients,
            'laboratories' => Laboratory::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
