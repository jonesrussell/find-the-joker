<?php

namespace App\Events;

use App\Models\Card;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JokerRevealed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Card $card
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('board.'.$this->card->board_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'JokerRevealed';
    }

    /**
     * Get the data to broadcast (full card payload).
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $card = $this->card;
        return [
            'id' => $card->id,
            'uuid' => $card->uuid,
            'number' => $card->number,
            'status' => $card->status,
            'revealed' => $card->revealed,
            'buyer_name' => $card->buyer_name,
            'buyer_email' => $card->buyer_email,
            'admin_sold' => $card->admin_sold,
        ];
    }
}
