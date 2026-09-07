<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('systeme_config', function (Blueprint $table) {
            $table->id();
            $table->string('cle', 100)->unique();
            $table->text('valeur')->nullable();
            $table->string('type', 20)->default('string'); // string|int|bool|json|url|color|image
            $table->string('categorie', 50)->default('general'); // general|marque|localisation|securite|notifications
            $table->string('libelle')->nullable();
            $table->text('description')->nullable();
            $table->boolean('editable')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
            $table->index(['categorie', 'ordre']);
        });

        // Seed initial — paramètres essentiels
        $now = now();
        \DB::table('systeme_config')->insert([
            ['cle'=>'app_nom',           'valeur'=>'OptimiZe',                     'type'=>'string', 'categorie'=>'marque',        'libelle'=>"Nom de l'application",          'ordre'=>10, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'app_slogan',        'valeur'=>'ERP intégré · Yubile Technologie', 'type'=>'string', 'categorie'=>'marque',        'libelle'=>'Slogan / baseline',              'ordre'=>20, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'app_logo_url',      'valeur'=>null,                             'type'=>'image',  'categorie'=>'marque',        'libelle'=>"URL du logo",                    'ordre'=>30, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'app_couleur_primaire','valeur'=>'#0A66C2',                     'type'=>'color',  'categorie'=>'marque',        'libelle'=>'Couleur primaire',               'ordre'=>40, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'org_pays',          'valeur'=>'Gabon',                          'type'=>'string', 'categorie'=>'localisation',  'libelle'=>'Pays',                           'ordre'=>10, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'org_devise',        'valeur'=>'XAF',                            'type'=>'string', 'categorie'=>'localisation',  'libelle'=>'Devise (code ISO)',              'ordre'=>20, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'org_fuseau',        'valeur'=>'Africa/Libreville',              'type'=>'string', 'categorie'=>'localisation',  'libelle'=>'Fuseau horaire',                 'ordre'=>30, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'org_langue',        'valeur'=>'fr',                             'type'=>'string', 'categorie'=>'localisation',  'libelle'=>'Langue par défaut',              'ordre'=>40, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'sec_min_password',  'valeur'=>'8',                              'type'=>'int',    'categorie'=>'securite',      'libelle'=>'Longueur min. mot de passe',     'ordre'=>10, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'sec_force_change',  'valeur'=>'1',                              'type'=>'bool',   'categorie'=>'securite',      'libelle'=>'Forcer changement au 1er login',  'ordre'=>20, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'sec_session_life',  'valeur'=>'120',                            'type'=>'int',    'categorie'=>'securite',      'libelle'=>'Durée session (minutes)',        'ordre'=>30, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'notif_email_actif', 'valeur'=>'1',                              'type'=>'bool',   'categorie'=>'notifications', 'libelle'=>'Notifications email actives',    'ordre'=>10, 'created_at'=>$now, 'updated_at'=>$now],
            ['cle'=>'notif_expediteur',  'valeur'=>'noreply@optimize.local',         'type'=>'string', 'categorie'=>'notifications', 'libelle'=>'Adresse expéditeur email',       'ordre'=>20, 'created_at'=>$now, 'updated_at'=>$now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('systeme_config');
    }
};
