<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intranet_projets', function (Blueprint $t) {
            // Liens vers CRM
            $t->foreignId('organisation_id')->nullable()->after('chef_projet_id')
              ->constrained('intranet_contact_organisations')->nullOnDelete();
            $t->foreignId('contact_id')->nullable()->after('organisation_id')
              ->constrained('intranet_contacts')->nullOnDelete();
            $t->foreignId('opportunite_id')->nullable()->after('contact_id')
              ->constrained('intranet_opportunites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('intranet_projets', function (Blueprint $t) {
            $t->dropForeign(['organisation_id']);
            $t->dropForeign(['contact_id']);
            $t->dropForeign(['opportunite_id']);
            $t->dropColumn(['organisation_id', 'contact_id', 'opportunite_id']);
        });
    }
};
