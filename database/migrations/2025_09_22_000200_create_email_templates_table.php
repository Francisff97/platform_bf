<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('email_templates', function (Blueprint $table) {
      $table->id();
      $table->string('slug')->unique();      // es: order_placed, contact_received
      $table->string('name');                // label leggibile
      $table->string('subject');             // soggetto
      $table->text('html_body')->nullable(); // HTML con placeholder {{customer_name}}
      $table->text('text_body')->nullable(); // fallback testo puro
      $table->boolean('enabled')->default(true);
      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('email_templates');
  }
};