<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Card;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $board = Board::create([
            'name' => "Skylar's Band Trip",
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
    }
}
