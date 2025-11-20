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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->dateTime('paid_on')->nullable();
            $table->string('method')->nullable(); // cash, card, bank_transfer, online, etc.
            $table->string('reference')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed, refunded
            $table->timestamps();

            $table->index('school_id');
            $table->index('booking_id');
            $table->index('status');
            $table->index('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
