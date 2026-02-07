<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardClaimController extends Controller
{
    /**
     * Claim (reserve) a card with name and email. Card becomes pending.
     */
    public function store(Request $request, Board $board, string $cardUuid): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'website' => ['nullable', 'string', 'max:0'], // honeypot
        ]);

        if (! empty($validated['website'] ?? '')) {
            return back()->with('error', 'Invalid request.');
        }

        if (! $board->is_active) {
            abort(404);
        }

        $card = DB::transaction(function () use ($board, $cardUuid, $validated) {
            $card = Card::query()
                ->where('board_id', $board->id)
                ->where('uuid', $cardUuid)
                ->lockForUpdate()
                ->first();

            if (! $card) {
                return null;
            }

            if ($card->status !== 'available') {
                return null;
            }

            $card->update([
                'status' => 'pending',
                'buyer_name' => $validated['name'],
                'buyer_email' => $validated['email'],
            ]);

            return $card;
        });

        if (! $card) {
            return back()->with('error', 'This card is no longer available.');
        }

        return back()->with('success', 'Card reserved! Send EMT to '.config('findthejoker.emt_email', 'darapatrie32@gmail.com').' — admin will confirm your card.');
    }
}
