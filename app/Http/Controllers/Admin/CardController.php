<?php

namespace App\Http\Controllers\Admin;

use App\Events\JokerRevealed;
use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    /**
     * Update card: mark_sold or mark_joker.
     */
    public function update(Request $request, Card $card): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:mark_sold,mark_joker'],
        ]);

        $action = $validated['action'];

        DB::transaction(function () use ($card, $action) {
            $card = Card::where('id', $card->id)->lockForUpdate()->firstOrFail();

            if ($action === 'mark_sold') {
                if ($card->status === 'pending' || $card->status === 'sold') {
                    $card->update([
                        'status' => 'sold',
                        'admin_sold' => true,
                    ]);
                }
            }

            if ($action === 'mark_joker') {
                $card->update([
                    'status' => 'joker',
                    'revealed' => true,
                ]);
                $card->load('board');
                broadcast(new JokerRevealed($card))->toOthers();
            }
        });

        return back()->with('success', $action === 'mark_sold' ? 'Card marked as sold.' : 'Joker revealed.');
    }
}
