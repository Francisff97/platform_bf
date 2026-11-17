<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('privacy_settings', function (Blueprint $table) {
            $table->id();

            // Opzionale: solo per tua organizzazione interna, non vincolante
            $table->string('provider')->nullable(); // es: 'iubenda' | 'custom'

            // Banner cookie
            $table->boolean('banner_enabled')->default(false);
            $table->longText('banner_head_code')->nullable(); // script da mettere in <head>
            $table->longText('banner_body_code')->nullable(); // script da mettere prima di </body>

            // Privacy Policy
            $table->boolean('policy_enabled')->default(false);
            $table->boolean('policy_external')->default(false);
            $table->string('policy_external_url')->nullable(); // se policy_external = true
            $table->longText('policy_html')->nullable(); // contenuto inline (se non usi provider esterno)

            // Cookie Policy
            $table->boolean('cookies_enabled')->default(false);
            $table->boolean('cookies_external')->default(false);
            $table->string('cookies_external_url')->nullable();
            $table->longText('cookies_html')->nullable();

            $table->date('last_updated_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('privacy_settings');
    }
};