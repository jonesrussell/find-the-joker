<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BoardController extends Controller
{
    /**
     * Display a listing of boards.
     */
    public function index(): Response
    {
        $boards = Board::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Board $board) => [
                'id' => $board->id,
                'name' => $board->name,
                'is_active' => $board->is_active,
                'show_url' => route('dashboard.boards.show', $board),
            ]);

        return Inertia::render('dashboard/boards/Index', [
            'boards' => $boards,
            'urls' => [
                'index' => route('dashboard.boards.index'),
                'store' => route('dashboard.boards.store'),
            ],
        ]);
    }

    /**
     * Store a newly created board and 54 cards.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $board = DB::transaction(function () use ($validated) {
            $board = Board::create([
                'name' => $validated['name'],
                'is_active' => true,
            ]);

            for ($number = 1; $number <= 54; $number++) {
                Card::create([
                    'board_id' => $board->id,
                    'number' => $number,
                    'status' => 'available',
                    'revealed' => false,
                    'admin_sold' => false,
                ]);
            }

            return $board;
        });

        return redirect()->route('dashboard.boards.show', $board)
            ->with('success', 'Board created successfully.');
    }

    /**
     * Display the specified board with cards.
     */
    public function show(Board $board): Response
    {
        $board->load(['cards' => fn ($q) => $q->orderBy('number')]);

        return Inertia::render('dashboard/boards/Show', [
            'board' => $board,
            'cards' => $board->cards->values()->all(),
            'urls' => [
                'update' => route('dashboard.boards.update', $board),
                'destroy' => route('dashboard.boards.destroy', $board),
                'reset' => route('dashboard.boards.reset', $board),
                'export' => route('dashboard.boards.export', $board),
                'cardUpdate' => url('/dashboard/cards'),
            ],
        ]);
    }

    /**
     * Update the specified board (e.g. is_active).
     */
    public function update(Request $request, Board $board): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('is_active', $validated)) {
            $board->update(['is_active' => $validated['is_active']]);
        }

        return back()->with('success', 'Board updated.');
    }

    /**
     * Remove the specified board.
     */
    public function destroy(Board $board): RedirectResponse
    {
        $board->delete();

        return redirect()->route('dashboard.boards.index')
            ->with('success', 'Board deleted.');
    }

    /**
     * Reset all cards on the board to available.
     */
    public function reset(Board $board): RedirectResponse
    {
        $board->cards()->update([
            'status' => 'available',
            'revealed' => false,
            'buyer_name' => null,
            'buyer_email' => null,
            'admin_sold' => false,
        ]);

        return back()->with('success', 'Board reset.');
    }

    /**
     * Export buyers CSV for the board.
     */
    public function exportCsv(Board $board): StreamedResponse
    {
        $board->load(['cards' => fn ($q) => $q->orderBy('number')]);

        $filename = 'board-'.$board->id.'-buyers-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($board) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['number', 'buyer_name', 'buyer_email', 'status']);
            foreach ($board->cards as $card) {
                if ($card->buyer_name || $card->buyer_email) {
                    fputcsv($handle, [
                        $card->number,
                        $card->buyer_name ?? '',
                        $card->buyer_email ?? '',
                        $card->status,
                    ]);
                }
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
