<?php
// database/migrations/XXXX_add_description_to_builders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('builders', function (Blueprint $table) {
            $table->text('description')->nullable()->after('team');
        });
    }
    public function down(): void {
        Schema::table('builders', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};

