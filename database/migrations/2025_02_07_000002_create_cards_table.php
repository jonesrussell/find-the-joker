<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->string('uuid')->unique();
            $table->unsignedTinyInteger('number');
            $table->string('status')->default('available');
            $table->boolean('revealed')->default(false);
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->boolean('admin_sold')->default(false);
            $table->timestamps();

            $table->unique(['board_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
