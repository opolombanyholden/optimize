<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intranet_media_albums', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->string('couverture')->nullable(); // chemin image de couverture
            $table->string('couleur', 20)->default('#7C3AED');
            $table->string('icone')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('intranet_media_albums')->nullOnDelete();
            $table->boolean('is_public')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
        });

        // Ajouter FK album_id sur intranet_media
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->foreignId('album_id')->nullable()->after('album')
                  ->constrained('intranet_media_albums')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_media', function (Blueprint $table) {
            $table->dropForeign(['album_id']);
            $table->dropColumn('album_id');
        });
        Schema::dropIfExists('intranet_media_albums');
    }
};
