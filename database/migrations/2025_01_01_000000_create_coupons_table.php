<?php
// database/migrations/2025_01_01_000000_create_coupons_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code')->unique();                // es. BASEFORGE (maiuscolo)
            $t->enum('type', ['percent','fixed']);       // percentuale o importo fisso
            $t->unsignedInteger('value')->nullable();    // se type=percent → 1..100 (senza %)
            $t->unsignedInteger('value_cents')->nullable(); // se type=fixed → importo in cents
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('min_order_cents')->default(0); // soglia facoltativa
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->unsignedInteger('usage_count')->default(0);
            $t->unsignedInteger('max_uses')->nullable(); // null = illimitato
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('coupons');
    }
};