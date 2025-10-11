<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $t) {
            // assicurati lunghezze giuste
            if (Schema::hasColumn('seo_pages', 'meta_title')) {
                $t->string('meta_title', 255)->nullable()->change();
            }
            if (Schema::hasColumn('seo_pages', 'meta_description')) {
                $t->text('meta_description')->nullable()->change();
            }
            if (Schema::hasColumn('seo_pages', 'og_image_path')) {
                $t->string('og_image_path', 512)->nullable()->change();
            }

            // indici “tolleranti” (unique solo se non null)
            $t->index('route_name', 'seo_pages_route_idx');
            $t->index('path', 'seo_pages_path_idx');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $t) {
            $t->dropIndex('seo_pages_route_idx');
            $t->dropIndex('seo_pages_path_idx');
        });
    }
};
