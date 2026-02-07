<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    /**
     * List active boards for public selector.
     */
    public function index(): Response
    {
        $boards = Board::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('boards/Index', [
            'boards' => $boards,
            'urls' => [
                'showPath' => '/boards',
            ],
        ]);
    }

    /**
     * Show a single board (54 cards) for public view.
     */
    public function show(Request $request, Board $board): Response
    {
        if (! $board->is_active) {
            abort(404);
        }

        $board->load(['cards' => fn ($q) => $q->orderBy('number')]);

        return Inertia::render('boards/Show', [
            'board' => $board,
            'cards' => $board->cards->values()->all(),
            'emtEmail' => config('findthejoker.emt_email'),
            'pricePerCard' => config('findthejoker.price_per_card'),
            'urls' => [
                'claimPath' => '/boards/'.$board->id.'/cards',
            ],
        ]);
    }
}
