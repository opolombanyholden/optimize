<?php

namespace Database\Seeders;

use App\Models\Intranet\Template;
use App\Models\Intranet\TemplateCategorie;
use App\Models\User;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) return;

        $cats = TemplateCategorie::pluck('id', 'nom');

        $templates = [

            // ─── CONTRAT ───────────────────────────────────────
            [
                'titre'       => 'Contrat de prestation de services',
                'description' => 'Modèle standard de contrat de prestation entre deux parties.',
                'categorie'   => 'Contrat',
                'variables'   => ['entreprise', 'adresse_entreprise', 'prestataire', 'adresse_prestataire', 'objet', 'montant', 'devise', 'duree', 'date_debut', 'date_signature', 'lieu'],
                'contenu'     => <<<'HTML'
<h1 style="text-align:center;">CONTRAT DE PRESTATION DE SERVICES</h1>
<p style="text-align:center;color:#64748B;">Réf. : CPR-{{date_signature}}</p>
<hr>
<h2>ENTRE LES SOUSSIGNÉS</h2>
<p><strong>Le Client :</strong><br>
{{entreprise}}, dont le siège social est situé au {{adresse_entreprise}},<br>
ci-après dénommé « le Client »,</p>
<p><strong>Le Prestataire :</strong><br>
{{prestataire}}, dont le siège social est situé au {{adresse_prestataire}},<br>
ci-après dénommé « le Prestataire ».</p>

<h2>ARTICLE 1 — OBJET</h2>
<p>Le présent contrat a pour objet la réalisation par le Prestataire de la prestation suivante :</p>
<p><strong>{{objet}}</strong></p>

<h2>ARTICLE 2 — DURÉE</h2>
<p>Le présent contrat est conclu pour une durée de <strong>{{duree}}</strong>, à compter du <strong>{{date_debut}}</strong>.</p>

<h2>ARTICLE 3 — RÉMUNÉRATION</h2>
<p>En contrepartie de la réalisation des prestations, le Client versera au Prestataire la somme de :</p>
<p style="font-size:1.2em;text-align:center;"><strong>{{montant}} {{devise}}</strong></p>
<p>Ce montant est payable selon les conditions définies en annexe.</p>

<h2>ARTICLE 4 — OBLIGATIONS DU PRESTATAIRE</h2>
<ul>
<li>Exécuter les prestations avec diligence et professionnalisme</li>
<li>Respecter les délais convenus</li>
<li>Informer le Client de tout obstacle à la bonne exécution</li>
<li>Maintenir la confidentialité des informations communiquées</li>
</ul>

<h2>ARTICLE 5 — OBLIGATIONS DU CLIENT</h2>
<ul>
<li>Fournir au Prestataire toutes les informations nécessaires</li>
<li>Procéder au paiement dans les délais convenus</li>
<li>Faciliter l'accès aux locaux si nécessaire</li>
</ul>

<h2>ARTICLE 6 — RÉSILIATION</h2>
<p>Chaque partie peut résilier le contrat avec un préavis de 30 jours par lettre recommandée avec accusé de réception.</p>

<h2>ARTICLE 7 — LITIGES</h2>
<p>En cas de litige, les parties s'engagent à rechercher une solution amiable. À défaut, le tribunal compétent sera saisi.</p>

<br><br>
<p>Fait à <strong>{{lieu}}</strong>, le <strong>{{date_signature}}</strong>, en deux exemplaires originaux.</p>
<br>
<table style="width:100%;">
<tr>
<td style="width:50%;vertical-align:top;"><strong>Pour le Client</strong><br><br><br><br>_________________________<br>{{entreprise}}</td>
<td style="width:50%;vertical-align:top;"><strong>Pour le Prestataire</strong><br><br><br><br>_________________________<br>{{prestataire}}</td>
</tr>
</table>
HTML,
            ],

            // ─── ATTESTATION ───────────────────────────────────
            [
                'titre'       => 'Attestation de travail',
                'description' => 'Attestation confirmant qu\'un employé travaille au sein de l\'entreprise.',
                'categorie'   => 'Attestation',
                'variables'   => ['entreprise', 'adresse_entreprise', 'directeur', 'nom_employe', 'poste', 'date_embauche', 'date', 'lieu'],
                'contenu'     => <<<'HTML'
<div style="text-align:right;margin-bottom:2em;">
<p>{{lieu}}, le {{date}}</p>
</div>

<h1 style="text-align:center;text-decoration:underline;">ATTESTATION DE TRAVAIL</h1>

<br>
<p>Je soussigné(e), <strong>{{directeur}}</strong>, agissant en qualité de Directeur Général de la société <strong>{{entreprise}}</strong>, sise au {{adresse_entreprise}},</p>

<p>atteste par la présente que :</p>

<p style="font-size:1.1em;text-align:center;margin:1.5em 0;">
<strong>{{nom_employe}}</strong>
</p>

<p>est employé(e) au sein de notre entreprise depuis le <strong>{{date_embauche}}</strong> en qualité de <strong>{{poste}}</strong>.</p>

<p>Cette attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>

<br><br>
<div style="text-align:right;">
<p><strong>{{directeur}}</strong><br>
Directeur Général<br>
{{entreprise}}</p>
<br><br>
<p>Signature et cachet</p>
</div>
HTML,
            ],

            // ─── PV / CR ───────────────────────────────────────
            [
                'titre'       => 'Procès-verbal de réunion',
                'description' => 'Modèle de PV pour les réunions de travail ou comités.',
                'categorie'   => 'PV / CR',
                'variables'   => ['titre_reunion', 'date', 'heure_debut', 'heure_fin', 'lieu', 'president', 'secretaire', 'participants', 'ordre_du_jour', 'decisions', 'prochaine_reunion'],
                'contenu'     => <<<'HTML'
<h1 style="text-align:center;">PROCÈS-VERBAL DE RÉUNION</h1>
<p style="text-align:center;color:#64748B;font-size:1.1em;"><strong>{{titre_reunion}}</strong></p>
<hr>

<table style="width:100%;margin-bottom:1.5em;">
<tr><td style="width:30%;"><strong>Date :</strong></td><td>{{date}}</td></tr>
<tr><td><strong>Heure :</strong></td><td>{{heure_debut}} — {{heure_fin}}</td></tr>
<tr><td><strong>Lieu :</strong></td><td>{{lieu}}</td></tr>
<tr><td><strong>Président de séance :</strong></td><td>{{president}}</td></tr>
<tr><td><strong>Secrétaire de séance :</strong></td><td>{{secretaire}}</td></tr>
</table>

<h2>Participants</h2>
<p>{{participants}}</p>

<h2>Ordre du jour</h2>
<p>{{ordre_du_jour}}</p>

<h2>Discussions et décisions</h2>
<p>{{decisions}}</p>

<h2>Prochaine réunion</h2>
<p>{{prochaine_reunion}}</p>

<br><br>
<table style="width:100%;">
<tr>
<td style="width:50%;vertical-align:top;"><strong>Le Président de séance</strong><br><br><br><br>_________________________<br>{{president}}</td>
<td style="width:50%;vertical-align:top;"><strong>Le Secrétaire de séance</strong><br><br><br><br>_________________________<br>{{secretaire}}</td>
</tr>
</table>
HTML,
            ],

            // ─── DEVIS ─────────────────────────────────────────
            [
                'titre'       => 'Devis commercial',
                'description' => 'Modèle de devis commercial avec tableau de prestations.',
                'categorie'   => 'Devis',
                'variables'   => ['entreprise', 'adresse_entreprise', 'telephone', 'email', 'client', 'adresse_client', 'reference_devis', 'date', 'validite', 'objet', 'montant_ht', 'tva', 'montant_ttc', 'devise', 'conditions'],
                'contenu'     => <<<'HTML'
<table style="width:100%;margin-bottom:2em;">
<tr>
<td style="width:50%;vertical-align:top;">
<h2 style="margin:0;color:#4F46E5;">{{entreprise}}</h2>
<p style="color:#64748B;font-size:.9em;">
{{adresse_entreprise}}<br>
Tél : {{telephone}}<br>
Email : {{email}}
</p>
</td>
<td style="width:50%;text-align:right;vertical-align:top;">
<h1 style="margin:0;color:#4F46E5;">DEVIS</h1>
<p>
<strong>Réf :</strong> {{reference_devis}}<br>
<strong>Date :</strong> {{date}}<br>
<strong>Validité :</strong> {{validite}}
</p>
</td>
</tr>
</table>

<div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:1em;margin-bottom:1.5em;">
<p style="margin:0;"><strong>Client :</strong> {{client}}</p>
<p style="margin:0;color:#64748B;">{{adresse_client}}</p>
</div>

<h3>Objet : {{objet}}</h3>

<table style="width:100%;border-collapse:collapse;margin:1.5em 0;">
<thead>
<tr style="background:#4F46E5;color:#fff;">
<th style="padding:10px;text-align:left;">Désignation</th>
<th style="padding:10px;text-align:center;">Qté</th>
<th style="padding:10px;text-align:right;">P.U. HT</th>
<th style="padding:10px;text-align:right;">Total HT</th>
</tr>
</thead>
<tbody>
<tr>
<td style="padding:10px;border-bottom:1px solid #E2E8F0;">Prestation décrite ci-dessus</td>
<td style="padding:10px;border-bottom:1px solid #E2E8F0;text-align:center;">1</td>
<td style="padding:10px;border-bottom:1px solid #E2E8F0;text-align:right;">{{montant_ht}} {{devise}}</td>
<td style="padding:10px;border-bottom:1px solid #E2E8F0;text-align:right;">{{montant_ht}} {{devise}}</td>
</tr>
</tbody>
</table>

<table style="width:50%;margin-left:auto;">
<tr><td style="padding:5px;"><strong>Total HT</strong></td><td style="padding:5px;text-align:right;">{{montant_ht}} {{devise}}</td></tr>
<tr><td style="padding:5px;"><strong>TVA ({{tva}}%)</strong></td><td style="padding:5px;text-align:right;">—</td></tr>
<tr style="background:#F8FAFC;font-size:1.1em;"><td style="padding:8px;"><strong>Total TTC</strong></td><td style="padding:8px;text-align:right;"><strong>{{montant_ttc}} {{devise}}</strong></td></tr>
</table>

<br>
<h3>Conditions</h3>
<p>{{conditions}}</p>

<br><br>
<p><em>Bon pour accord — Date et signature du client :</em></p>
<br><br>
<p>_________________________</p>
HTML,
            ],

            // ─── NOTE INTERNE ──────────────────────────────────
            [
                'titre'       => 'Note de service',
                'description' => 'Communication officielle interne de la direction.',
                'categorie'   => 'Note interne',
                'variables'   => ['entreprise', 'expediteur', 'fonction_expediteur', 'destinataires', 'objet', 'contenu', 'date', 'lieu'],
                'contenu'     => <<<'HTML'
<div style="text-align:center;margin-bottom:2em;">
<h2 style="margin:0;">{{entreprise}}</h2>
<p style="color:#64748B;">Note de service</p>
</div>

<table style="width:100%;margin-bottom:1.5em;">
<tr><td style="width:20%;"><strong>De :</strong></td><td>{{expediteur}}, {{fonction_expediteur}}</td></tr>
<tr><td><strong>À :</strong></td><td>{{destinataires}}</td></tr>
<tr><td><strong>Date :</strong></td><td>{{date}}</td></tr>
<tr><td><strong>Objet :</strong></td><td><strong>{{objet}}</strong></td></tr>
</table>

<hr>

<p>{{contenu}}</p>

<br><br>
<div style="text-align:right;">
<p>{{expediteur}}<br>
<em>{{fonction_expediteur}}</em></p>
</div>
HTML,
            ],

            // ─── COURRIER ──────────────────────────────────────
            [
                'titre'       => 'Lettre officielle',
                'description' => 'Modèle de courrier officiel à en-tête.',
                'categorie'   => 'Courrier',
                'variables'   => ['entreprise', 'adresse_entreprise', 'destinataire', 'adresse_destinataire', 'lieu', 'date', 'objet', 'formule_appel', 'corps', 'signataire', 'fonction'],
                'contenu'     => <<<'HTML'
<table style="width:100%;margin-bottom:3em;">
<tr>
<td style="width:50%;vertical-align:top;">
<strong>{{entreprise}}</strong><br>
<span style="color:#64748B;">{{adresse_entreprise}}</span>
</td>
<td style="width:50%;text-align:right;vertical-align:top;">
{{lieu}}, le {{date}}
</td>
</tr>
</table>

<p style="margin-top:2em;">
<strong>À l'attention de :</strong><br>
{{destinataire}}<br>
<span style="color:#64748B;">{{adresse_destinataire}}</span>
</p>

<p style="margin-top:2em;"><strong>Objet : {{objet}}</strong></p>

<br>
<p>{{formule_appel}},</p>

<p>{{corps}}</p>

<p>Veuillez agréer, {{formule_appel}}, l'expression de nos salutations distinguées.</p>

<br><br>
<div style="text-align:right;">
<p><strong>{{signataire}}</strong><br>
<em>{{fonction}}</em><br>
{{entreprise}}</p>
</div>
HTML,
            ],

            // ─── RAPPORT ───────────────────────────────────────
            [
                'titre'       => 'Rapport d\'activité mensuel',
                'description' => 'Template de rapport mensuel avec sections standards.',
                'categorie'   => 'Rapport',
                'variables'   => ['entreprise', 'service', 'mois', 'annee', 'redacteur', 'resume', 'realisations', 'difficultes', 'perspectives', 'date'],
                'contenu'     => <<<'HTML'
<h1 style="text-align:center;color:#4F46E5;">RAPPORT D'ACTIVITÉ MENSUEL</h1>
<p style="text-align:center;font-size:1.1em;">{{service}} — {{mois}} {{annee}}</p>
<p style="text-align:center;color:#64748B;">{{entreprise}}</p>
<hr>

<h2>1. Résumé exécutif</h2>
<p>{{resume}}</p>

<h2>2. Réalisations du mois</h2>
<p>{{realisations}}</p>

<h2>3. Difficultés rencontrées</h2>
<p>{{difficultes}}</p>

<h2>4. Perspectives pour le mois suivant</h2>
<p>{{perspectives}}</p>

<br><br>
<p style="color:#64748B;">Rapport rédigé par <strong>{{redacteur}}</strong> le {{date}}.</p>
HTML,
            ],

            // ─── FORMULAIRE ────────────────────────────────────
            [
                'titre'       => 'Demande de congé',
                'description' => 'Formulaire de demande de congé standard.',
                'categorie'   => 'Formulaire',
                'variables'   => ['nom_employe', 'service', 'poste', 'type_conge', 'date_debut', 'date_fin', 'nombre_jours', 'motif', 'date_demande', 'nom_responsable'],
                'contenu'     => <<<'HTML'
<h1 style="text-align:center;">DEMANDE DE CONGÉ</h1>
<hr>

<h3>Informations de l'employé</h3>
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:8px;border:1px solid #E2E8F0;width:30%;background:#F8FAFC;"><strong>Nom complet</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{nom_employe}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Service</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{service}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Poste</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{poste}}</td></tr>
</table>

<h3>Détails du congé</h3>
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:8px;border:1px solid #E2E8F0;width:30%;background:#F8FAFC;"><strong>Type de congé</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{type_conge}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Date de début</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{date_debut}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Date de fin</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{date_fin}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Nombre de jours</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{nombre_jours}}</td></tr>
<tr><td style="padding:8px;border:1px solid #E2E8F0;background:#F8FAFC;"><strong>Motif</strong></td><td style="padding:8px;border:1px solid #E2E8F0;">{{motif}}</td></tr>
</table>

<br>
<p>Fait le {{date_demande}}</p>

<br>
<table style="width:100%;">
<tr>
<td style="width:50%;vertical-align:top;"><strong>Signature de l'employé</strong><br><br><br><br>_________________________<br>{{nom_employe}}</td>
<td style="width:50%;vertical-align:top;"><strong>Avis du responsable</strong><br><br>☐ Approuvé &nbsp; ☐ Refusé<br><br>_________________________<br>{{nom_responsable}}</td>
</tr>
</table>
HTML,
            ],

        ];

        foreach ($templates as $tplData) {
            $catId = $cats[$tplData['categorie']] ?? null;

            Template::firstOrCreate(
                ['titre' => $tplData['titre']],
                [
                    'description' => $tplData['description'],
                    'contenu'     => $tplData['contenu'],
                    'format'      => 'html',
                    'categorie_id'=> $catId,
                    'variables'   => $tplData['variables'],
                    'tags'        => [],
                    'is_public'   => true,
                    'utilisations'=> 0,
                    'created_by'  => $user->id,
                ]
            );
        }
    }
}
