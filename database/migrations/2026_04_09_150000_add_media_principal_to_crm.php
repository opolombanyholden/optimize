<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_contacts', function (Blueprint $table) {
            $table->string('media_principal')->nullable()->after('photo');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
        });

        Schema::table('intranet_contact_organisations', function (Blueprint $table) {
            $table->string('media_principal')->nullable()->after('logo');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
        });

        Schema::table('intranet_opportunites', function (Blueprint $table) {
            $table->string('media_principal')->nullable()->after('description');
            $table->enum('media_principal_type', ['image', 'video', 'document'])->nullable()->after('media_principal');
        });
    }

    public function down(): void
    {
        Schema::table('intranet_contacts', function (Blueprint $table) {
            $table->dropColumn(['media_principal', 'media_principal_type']);
        });
        Schema::table('intranet_contact_organisations', function (Blueprint $table) {
            $table->dropColumn(['media_principal', 'media_principal_type']);
        });
        Schema::table('intranet_opportunites', function (Blueprint $table) {
            $table->dropColumn(['media_principal', 'media_principal_type']);
        });
    }
};
