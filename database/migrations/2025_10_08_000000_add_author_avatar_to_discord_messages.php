<?php
// database/migrations/2025_10_08_000000_add_author_avatar_to_discord_messages.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('discord_messages', function (Blueprint $t) {
      if (!Schema::hasColumn('discord_messages', 'author_avatar')) {
        $t->string('author_avatar')->nullable()->after('author_name');
      }
    });
  }
  public function down(): void {
    Schema::table('discord_messages', function (Blueprint $t) {
      if (Schema::hasColumn('discord_messages', 'author_avatar')) {
        $t->dropColumn('author_avatar');
      }
    });
  }
};
