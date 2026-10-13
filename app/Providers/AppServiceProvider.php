<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Pagination : le projet utilise Bootstrap 5 (Laravel 12 utilise Tailwind par défaut).
        Paginator::useBootstrapFive();

        // Observer : cascade objectif → évaluation lors d'un changement de statut final
        \App\Models\Intranet\Objectif::observe(\App\Observers\ObjectifObserver::class);


        // Partage les compteurs intranet dans toutes les vues (badges sidebar)
        View::composer('layouts.partials.sidebar', function ($view) {
            if (!auth()->check()) return;

            try {
                $view->with([
                    // ── Communication
                    'totalAnnonces'         => \App\Models\Intranet\Annonce::count(),
                    'annoncesUrgentes'      => \App\Models\Intranet\Annonce::where('is_urgent', true)->count(),
                    // ── Agenda
                    'evenementsAVenir'      => \App\Models\Intranet\Evenement::aVenir()->count(),
                    // ── Documents & GED
                    'courriersEnAttente'    => \App\Models\Intranet\Courrier::enAttente()->count(),
                    'totalDocuments'        => \App\Models\Intranet\Ressource::count(),
                    'totalMedias'           => \App\Models\Intranet\Media::count(),
                    'totalArchives'         => \App\Models\Intranet\Archive::count(),
                    'totalTemplates'        => \App\Models\Intranet\Template::count(),
                    // ── Projets & Tâches
                    'projetsEnCours'        => \App\Models\Intranet\Projet::whereHas('statut', fn($q) => $q->where('libelle', 'En cours'))->count(),
                    'tachesEnCours'         => \App\Models\Intranet\Tache::whereHas('statut', fn($q) => $q->whereIn('libelle', ['En cours', 'Non démarré']))->count(),
                    'tachesUrgentes'        => \App\Models\Intranet\Tache::whereHas('priorite', fn($q) => $q->where('libelle', 'Urgente'))->count(),
                    // ── CRM
                    'totalContacts'         => \App\Models\Intranet\Contact::count(),
                    'totalOrganisations'    => \App\Models\Intranet\ContactOrganisation::count(),
                    'opportunitesOuvertes'  => \App\Models\Intranet\Opportunite::where('statut', 'ouvert')->count(),
                    // ── Objectifs & KPI
                    'objectifsActifs'       => \App\Models\Intranet\Objectif::where('statut', 'actif')->count(),
                    'totalKpi'              => \App\Models\Intranet\Kpi::count(),
                    // ── Wiki
                    'totalWikiArticles'     => \App\Models\Intranet\WikiArticle::count(),
                    // ── Rapports
                    'rapportsBrouillon'     => \App\Models\Intranet\Rapport::where('statut', 'brouillon')->count(),
                    // ── Messagerie instantanée + mails pro (par utilisateur ; les mails viennent du cache
                    //    alimenté par le webmail, jamais d'appel IMAP pendant le rendu)
                    'messagesNonLus'        => \App\Models\Chat\Message::query()
                        ->join('chat_conversations as c', 'c.id', '=', 'chat_messages.conversation_id')
                        ->where(fn($w) => $w->where('c.user_un_id', auth()->id())->orWhere('c.user_deux_id', auth()->id()))
                        ->where('chat_messages.sender_id', '!=', auth()->id())
                        ->whereNull('chat_messages.lu_at')->count(),
                    'mailsNonLus'           => \App\Models\Intranet\MailCompte::where('user_id', auth()->id())->pluck('id')
                        ->sum(fn($id) => (int) \Illuminate\Support\Facades\Cache::get("mail_non_lus_{$id}", 0)),
                    // ── ERP — badges sidebar (statuts legacy en INTEGER : 1 = en attente / en cours / ouvert)
                    'absencesEnCours'       => \App\Models\Absence::where('statut', 1)->count(),
                    'commandesEnCours'      => \App\Models\CommandeFournisseur::where('statut', 1)->count(),
                    'dysfonctionnementsOuverts' => \App\Models\Dysfonctionnement::where('statut', 1)->count(),
                ]);
            } catch (\Exception $e) {
                // Tables pas encore migrées → pas de badges
            }
        });
    }
}
