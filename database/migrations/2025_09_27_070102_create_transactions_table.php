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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->decimal('amount', 20, 4); // amount transferred to receiver
            $table->decimal('commission_fee', 20, 4); // commission charged to sender
            $table->decimal('total_debit', 20, 4); // amount + commission
            $table->string('status')->default('completed'); // completed, failed, pending
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('created_at');

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
