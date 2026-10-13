<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Signatures de mail de l'utilisateur (plusieurs, une par défaut), choisies à la rédaction. */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('mail_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('nom', 100);
            $table->text('contenu'); // HTML nettoyé (cf. App\Support\HtmlSur)
            $table->boolean('par_defaut')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'nom']);
        });
        // Au plus une signature par défaut par utilisateur
        DB::statement('CREATE UNIQUE INDEX mail_signatures_un_defaut ON mail_signatures (user_id) WHERE par_defaut');
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_signatures');
    }
};
