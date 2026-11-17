<?php
// database/migrations/xxxx_xx_xx_create_coach_prices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('coach_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained()->cascadeOnDelete();
            $table->string('duration'); // es: "30 mins", "1 hr", "1.5 hr"
            $table->integer('price_cents'); // prezzo in centesimi
            $table->string('currency', 3)->default('EUR');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('coach_prices');
    }
};

