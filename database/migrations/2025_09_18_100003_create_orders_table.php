<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('orders', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
      $table->foreignId('pack_id')->constrained()->cascadeOnDelete();
      $table->integer('amount_cents');
      $table->string('currency',3)->default('EUR');
      $table->string('status')->default('pending'); // pending|paid|canceled
      $table->string('stripe_session_id')->nullable();
      $table->string('stripe_payment_intent')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('orders'); }
};
