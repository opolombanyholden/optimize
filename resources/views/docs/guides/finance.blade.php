{{-- Contenu du guide Finance & Budget --}}

<nav class="toc">
    <h2>Sommaire</h2>
    <ol class="toc-list" style="list-style:none; padding:0; margin:0;">
        <li><a href="#ch1">1 · Avant de commencer</a></li>
        <li><a href="#ch2">2 · Se connecter et arriver dans votre espace</a></li>
        <li><a href="#ch3">3 · Configurer le référentiel Finance (à faire au démarrage)</a></li>
        <li><a href="#ch4">4 · Vos tâches quotidiennes</a></li>
        <li><a href="#ch5">5 · Trois routines à adopter</a></li>
    </ol>
</nav>

{{-- ═════════════ CH 1 ═════════════ --}}
<h2 class="chapter" id="ch1"><span class="chapter-num">Chapitre 1</span>Avant de commencer</h2>
<p class="chapter-lead">Ce guide accompagne le responsable financier de la première connexion à la maîtrise complète du module Finance &amp; Budget. Il s'adresse au DAF, au comptable et à toute personne chargée de l'exécution budgétaire ou de la comptabilité générale.</p>

<h3>Ce que vous allez apprendre</h3>
<p>À la fin de ce manuel, vous serez capable de <strong>configurer</strong> les référentiels finance (sources de financement, titres, lignes analytiques, rubriques), d'<strong>ouvrir et planifier un exercice</strong>, de <strong>gérer les modifications budgétaires</strong> (transferts, apports), de <strong>suivre l'exécution</strong> par ligne budgétaire, de <strong>saisir des écritures</strong> au grand-livre, de <strong>traiter les factures</strong> clients et fournisseurs, et d'<strong>ordonnancer les paiements</strong>.</p>

<h3>Comment utiliser ce guide</h3>
<p>Chaque chapitre est indépendant. Quatre types d'encarts jalonnent le texte :</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Un raccourci ou une meilleure façon de faire.
</div>
<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Un point de vigilance — action difficilement réversible ou erreur à éviter.
</div>
<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Une information contextuelle pour comprendre le fonctionnement.
</div>
<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Ce que vous devriez voir une fois l'action réalisée.
</div>

<h3>Ce dont vous avez besoin pour commencer</h3>
<ul class="checklist">
    <li>Vos identifiants OptimiZe (e-mail + mot de passe).</li>
    <li>Un rôle disposant des permissions <em>read:exercice, read:budget, read:grandlivre, read:compte, read:facture, read:operation</em> — au minimum.</li>
    <li>Pour les actions sensibles (validation, ordonnancement, clôture), le rôle <strong>admin</strong> ou <strong>super-admin</strong>.</li>
</ul>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le module Finance s'appuie sur un exercice budgétaire annuel. Toutes les opérations (écritures, engagements, factures) sont rattachées à un exercice — c'est le point de départ obligatoire de la comptabilité.
</div>

{{-- ═════════════ CH 2 ═════════════ --}}
<h2 class="chapter" id="ch2"><span class="chapter-num">Chapitre 2</span>Se connecter et arriver dans votre espace</h2>
<p class="chapter-lead">Trois écrans à traverser : la page de connexion, le sélecteur d'espace, puis le tableau de bord Finance — votre poste de pilotage financier.</p>

<h3>Étape 1 · La page de connexion</h3>
<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-login">
            <div class="mock-login-card">
                <div class="mock-login-brand">OptimiZe</div>
                <div class="mock-login-sub">ERP intégré · Yubile Technologie</div>
                <table class="mock-form">
                    <tr><td class="mock-annot"><span>1</span></td><td>
                        <div class="mock-label">Adresse e-mail</div>
                        <div class="mock-input">daf@entreprise.com</div>
                    </td></tr>
                    <tr><td class="mock-annot"><span>2</span></td><td>
                        <div class="mock-label">Mot de passe</div>
                        <div class="mock-input">••••••••••••</div>
                    </td></tr>
                    <tr><td class="mock-annot"><span>3</span></td><td>
                        <div class="mock-btn-primary">SE CONNECTER</div>
                    </td></tr>
                </table>
            </div>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Écran de connexion</strong> · Trois champs, un bouton.</figcaption>
</figure>

<h3>Étape 2 · Choisir l'espace Finance</h3>
<p>Après connexion, la fenêtre de sélection d'espace apparaît. Cliquez sur la carte <strong>Finance</strong>.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-picker">
            <div class="mock-picker-title">Bienvenue</div>
            <div class="mock-picker-sub">Choisissez votre espace de travail — ce choix est requis pour continuer.</div>
            <div class="mock-picker-section">Espaces principaux</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Intranet</div><div class="mock-card-desc">Portail collaboratif</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Réseau Social</div><div class="mock-card-desc">Publications, groupes</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Guide &amp; Docs</div><div class="mock-card-desc">Manuels</div></div></td>
                </tr>
            </table>
            <div class="mock-picker-section">Modules métier</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card mock-card-featured"><div class="mock-card-name">Finance →</div><div class="mock-card-desc">Compta, budget, factures</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">GRH &amp; Paie</div><div class="mock-card-desc">Employés, paie</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Achats &amp; MG</div><div class="mock-card-desc">Commandes, stocks</div></div></td>
                </tr>
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Gestion Projet</div><div class="mock-card-desc">Projets, phases</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Stratégie</div><div class="mock-card-desc">Objectifs, KPI</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Mon profil</div><div class="mock-card-desc">Ma fiche</div></div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Sélecteur d'espace</strong> · La carte Finance est mise en évidence.</figcaption>
</figure>

<h3>Étape 3 · Découvrir le tableau de bord Finance</h3>
<p>Le tableau de bord Finance est votre <strong>poste de pilotage financier</strong>. Il affiche à l'ouverture tout ce qui demande votre attention.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-dash">
            <div class="mock-dash-hd">
                <table>
                    <tr>
                        <td>
                            <div class="title">Finance &amp; Budget</div>
                            <div class="sub">Exercice 2026 · du 01/01/2026 au 31/12/2026</div>
                        </td>
                        <td style="text-align:right;"><span class="cta">+ Nouvelle écriture</span></td>
                    </tr>
                </table>
            </div>

            <table class="mock-alerts">
                <tr>
                    <td><div class="mock-alert">
                        <div class="mock-alert-t">2 lignes en dépassement</div>
                        <div class="mock-alert-s">Décision urgente</div>
                    </div></td>
                    <td><div class="mock-alert">
                        <div class="mock-alert-t">3 factures échues</div>
                        <div class="mock-alert-s">4 200 K XAF non réglé</div>
                    </div></td>
                    <td><div class="mock-alert warn">
                        <div class="mock-alert-t">1 planification à valider</div>
                        <div class="mock-alert-s">Top management</div>
                    </div></td>
                </tr>
            </table>

            <table class="mock-kpis">
                <tr>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Taux d'exécution</div>
                        <div class="mock-kpi-v">36,8<small>%</small></div>
                        <div class="mock-kpi-s">Reste : 326 720 K XAF</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Dépenses ce mois</div>
                        <div class="mock-kpi-v">42 100 <small>K XAF</small></div>
                        <div class="mock-kpi-s">↑ 8 % vs mois -1</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Créances clients</div>
                        <div class="mock-kpi-v">18 400 <small>K XAF</small></div>
                        <div class="mock-kpi-s">À encaisser</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Dettes fournisseurs</div>
                        <div class="mock-kpi-v">9 850 <small>K XAF</small></div>
                        <div class="mock-kpi-s">À décaisser</div>
                    </div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Tableau de bord Finance</strong> · Header + barre de priorités + 4 KPIs. Plus bas : graphique dépenses 6 mois, top clients, top 5 postes budgétaires, lignes en dépassement, factures échues, dernières écritures.</figcaption>
</figure>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Sur les KPIs financiers, la <strong>hausse des dépenses s'affiche en rouge</strong> — pour un DAF, une masse qui monte est un signal à surveiller. Une baisse s'affiche en vert. C'est la convention inverse des KPIs opérationnels.
</div>

{{-- ═════════════ CH 3 ═════════════ --}}
<h2 class="chapter" id="ch3"><span class="chapter-num">Chapitre 3</span>Configurer le référentiel Finance</h2>
<p class="chapter-lead">Avant de créer votre premier exercice, prenez le temps de configurer les listes de base. Ce travail se fait <em>une seule fois</em> mais conditionne toute votre gestion budgétaire.</p>

<h3>Pourquoi commencer par le référentiel ?</h3>
<p>Un référentiel finance structure votre plan budgétaire. Quand vous créerez une ligne budgétaire, il vous faudra choisir une source de financement, un titre (famille), une rubrique d'opération — s'ils n'existent pas, vous ne pourrez pas les sélectionner.</p>

<p>Tous les référentiels finance se trouvent dans la sidebar Finance, dans l'accordéon <strong>Référentiels</strong> (en bas). L'ordre logique :</p>

<ol class="checklist">
    <li>Sources de financement (fond propre, fond alloué, dotations)</li>
    <li>Titres / Familles (grandes catégories de lignes)</li>
    <li>Lignes analytiques (codes analytiques utilisés pour le suivi)</li>
    <li>Rubriques d'opération (natures de dépense / recette)</li>
    <li>Modèles d'ordre (templates de dépenses/recettes récurrents)</li>
</ol>

<h3>3.1 · Sources de financement</h3>
<p>Une source représente l'origine du financement de vos budgets. Exemples classiques : <em>FP (Fonds Propres)</em>, <em>ETAT (Dotation de l'État)</em>, <em>RB (Reports Budgétaires)</em>, <em>SUB (Subvention externe)</em>.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la page</div>
            <p>Sidebar → Référentiels → <strong>Sources de financement</strong>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer une source</div>
            <p>Cliquez <span class="btn-ref">+ Nouvelle source</span>. Renseignez :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Code</strong> — abréviation courte majuscule (« FP »).</li>
                <li><strong>Libellé</strong> — nom complet (« Fonds propres »).</li>
                <li><strong>Description</strong> — précisions optionnelles.</li>
            </ul>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Une source utilisée dans au moins un budget ne peut plus être supprimée. Le bouton « Supprimer » est désactivé si le compteur « utilisée dans » est > 0.
</div>

<h3>3.2 · Titres et familles budgétaires</h3>
<p>Les titres (aussi appelés familles) regroupent vos lignes budgétaires en grandes catégories — par exemple « Personnel », « Fonctionnement », « Investissement ». Ils apparaissent dans les rapports agrégés.</p>
<p>Configurez-les dans <em>Référentiels → Titres / Familles</em>. Renseignez le code d'imputation, le libellé, le type (dépense / recette), le seuil éventuel.</p>

<h3>3.3 · Lignes analytiques</h3>
<p>Les lignes analytiques (codes analytiques) permettent un suivi fin par activité, service ou projet. Elles se déclinent au sein des titres. Configurez-les dans <em>Référentiels → Lignes (codes analyt.)</em>.</p>

<h3>3.4 · Rubriques d'opération</h3>
<p>Les rubriques précisent la nature d'une opération (« Salaires », « Fournitures de bureau », « Prestations informatiques »). Elles alimentent les ordres de dépense et de recette. Configurez-les dans <em>Référentiels → Rubriques opérations</em>.</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Utilisez une <strong>convention de nommage stricte</strong> pour vos rubriques (majuscules, absence d'abréviations). Cohérence = analyses fiables.
</div>

<h3>3.5 · Modèles d'ordre</h3>
<p>Un modèle d'ordre est un <strong>template de dépense ou de recette récurrent</strong> — loyer mensuel, cotisations sociales, facture d'électricité. Configurer un modèle permet de générer un ordre en un clic plutôt que de tout ressaisir.</p>
<p>Menu <em>Référentiels → Modèles d'ordre</em> → <span class="btn-ref">+ Nouveau modèle</span>. Renseignez le type (dépense/recette), la rubrique, le montant standard, les champs de formulaire personnalisés.</p>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Une fois vos 5 référentiels configurés, vous pourrez ouvrir votre premier exercice et créer des lignes budgétaires cohérentes en quelques clics.
</div>

<hr class="divider">

{{-- ═════════════ CH 4 ═════════════ --}}
<h2 class="chapter" id="ch4"><span class="chapter-num">Chapitre 4</span>Vos tâches quotidiennes, une par une</h2>
<p class="chapter-lead">Huit workflows couvrent 90 % de votre travail dans le module Finance.</p>

<h3>4.1 · Ouvrir un nouvel exercice budgétaire</h3>
<p>Un exercice représente une année budgétaire. C'est le point de départ obligatoire de toute activité financière.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Menu Exercices</div>
            <p>Sidebar → Comptabilité → <strong>Exercices</strong> → <span class="btn-ref">+ Nouvel exercice</span>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner les dates</div>
            <p>Libellé (« Exercice 2027 »), date de début (01/01/2027), date de fin (31/12/2027), type (dépense / recette / mixte).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Planifier les lignes</div>
            <p>Depuis la fiche de l'exercice, cliquez <span class="btn-ref">Planifier</span>. Vous ouvrez la matrice de planification où vous créez toutes les lignes budgétaires : chaque ligne a un titre, un code analytique, une rubrique, et un montant réparti entre les sources de financement (dotation État, fonds propres, reports…).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Soumettre pour validation</div>
            <p>Quand la planification est complète, cliquez <span class="btn-ref">Soumettre</span>. L'exercice passe en statut <em>Soumis</em> et attend l'approbation du top management.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Activer l'exécution</div>
            <p>Après validation, le statut passe à <em>En exécution</em>. Toutes les opérations comptables et budgétaires deviennent possibles pour cet exercice.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Une fois l'exercice en <em>Exécution</em>, la planification initiale devient <strong>immuable</strong>. Toute évolution passe désormais par une <strong>modification budgétaire</strong> (§4.3).
</div>

<h3>4.2 · Suivre l'exécution budgétaire</h3>
<p>Le tableau de bord <strong>Exécution budgétaire</strong> est votre écran de pilotage n°1. Il affiche ligne par ligne : Fond propre, Fond alloué, Budget initial, Modifications, Budget final, Consommation, Solde, Taux d'exécution, État.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la page</div>
            <p>Sidebar → Comptabilité → <strong>Exécution budgétaire</strong>. La page s'ouvre sur l'exercice en cours par défaut.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Lire les 7 KPIs globaux</div>
            <p>De gauche à droite : Fond propre · Fond alloué · Budget initial · Modifications nettes · Budget final · Consommation avec jauge · Solde disponible.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Filtrer les lignes</div>
            <p>Barre de filtres : recherche texte, sélection de titre, et 4 chips à cliquer : <span class="btn-ref">OK</span> · <span class="btn-ref">Alerte ≥90%</span> · <span class="btn-ref">Épuisé</span> · <span class="btn-ref btn-danger">Dépassé</span>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Analyser une ligne</div>
            <p>Chaque ligne affiche une <strong>barre d'exécution colorée</strong> : bleu &lt; 90 %, orange 90-99 %, ambre à 100 %, rouge &gt; 100 %. Le solde est rouge s'il est négatif, vert s'il est positif.</p>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    <strong>Budget initial = Fond propre + Fond alloué</strong>. Le « Fond propre » regroupe les fonds internes de l'organisation (fonds propres au sens strict + reports budgétaires + reports trésorerie). Le « Fond alloué » correspond à la dotation externe (typiquement de l'État).
</div>

<h3>4.3 · Modifier un budget en cours d'exercice</h3>
<p>En cours d'année, les besoins évoluent. Deux types de modification :</p>
<ul class="checklist">
    <li><strong>Transfert</strong> — vous déplacez un montant d'une ligne à une autre. Somme constante.</li>
    <li><strong>Apport</strong> — vous ajoutez un montant sur une ligne (nouvelle dotation, subvention exceptionnelle). Somme totale du budget augmente.</li>
</ul>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Créer la modification</div>
            <p>Sidebar → Comptabilité → <strong>Modifications budg.</strong> → <span class="btn-ref">+ Nouvelle modification</span>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner</div>
            <p>Type (transfert / apport), ligne source (si transfert), ligne destination, montant, objet, commentaire.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Soumettre → Approuver → Appliquer</div>
            <p>Circuit de validation en 3 étapes :</p>
            <ul style="list-style:none; padding:0; margin:.4rem 0;">
                <li style="padding:.25rem 0;"><span class="btn-ref">Soumettre</span> — le créateur transmet la demande.</li>
                <li style="padding:.25rem 0;"><span class="btn-ref">Approuver</span> — le décideur valide (ou <span class="btn-ref btn-danger">Rejeter</span> avec motif).</li>
                <li style="padding:.25rem 0;"><span class="btn-ref">Appliquer</span> — l'écriture est passée : les lignes budgétaires sont mises à jour, la colonne « Modifs » de l'exécution évolue.</li>
            </ul>
        </td>
    </tr>
</table>

<h3>4.4 · Consulter le grand-livre</h3>
<p>Le grand-livre est le <strong>journal chronologique</strong> de toutes les écritures comptables (débit et crédit). Dans OptimiZe il est en <strong>lecture seule</strong> — aucune écriture ne se saisit directement ici.</p>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Toutes les écritures du grand-livre proviennent des <strong>ordres de dépense et de recette</strong> (§4.7). Quand un ordre passe l'ensemble du circuit (validation → ordonnancement → paiement), l'écriture correspondante est automatiquement enregistrée au grand-livre. Cette contrainte garantit la traçabilité et la séparation des fonctions : impossible de bricoler manuellement une écriture pour couvrir une opération sans pièce justificative.
</div>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir le grand-livre</div>
            <p>Sidebar → Comptabilité → <strong>Grand-livre</strong>. Vous voyez la liste chronologique de toutes les écritures.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Filtrer</div>
            <p>Filtres disponibles : exercice, compte, période, sens (débit / crédit), texte libre (libellé, bénéficiaire, n° de pièce).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Consulter le détail</div>
            <p>Cliquez sur l'icône <i class="fas fa-eye"></i> d'une ligne pour voir toutes les données : imputation budgétaire, journal, référence de la pièce d'origine (facture, ordre), utilisateur émetteur, horodatages.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Exporter (CSV ou PDF)</div>
            <p>Trois boutons en haut de la liste :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><span class="btn-ref">Exporter CSV</span> — télécharge un fichier CSV (séparateur <code>;</code>, ouvrable dans Excel FR) contenant les écritures selon vos filtres actifs. Utile pour rapprochements, analyses Excel, transmission à un cabinet comptable.</li>
                <li><span class="btn-ref">PDF</span> — rapport imprimable du grand-livre pour vos archives ou vos audits.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Importer en masse (super-admin/admin)</div>
            <p>Le bouton <span class="btn-ref">Importer CSV</span> ouvre le formulaire d'import. Cas d'usage :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Migration</strong> depuis un ancien logiciel comptable</li>
                <li><strong>Intégration</strong> de relevés bancaires exportés par la banque</li>
                <li><strong>Reprise d'à-nouveaux</strong> en début d'exercice</li>
            </ul>
            <p style="margin-top:.5rem;">Téléchargez d'abord le <span class="btn-ref btn-neutral">modèle CSV</span> pour connaître la structure attendue (colonnes obligatoires : <code>date_ecriture</code>, <code>libelle</code>, <code>sens</code>, <code>montant_tc</code>, <code>compte_code</code>). Les écritures importées sont créées <strong>en brouillon</strong> — à valider ensuite au cas par cas.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Pour <strong>corriger une écriture erronée</strong>, il faut créer un <strong>nouvel ordre de sens inverse</strong> (§4.7) et le faire passer par le workflow habituel. L'import CSV est réservé aux opérations de migration / intégration — jamais pour rattraper une erreur ponctuelle.
</div>

<h3>4.5 · Émettre une facture (client ou fournisseur)</h3>
<p>Une facture matérialise une transaction commerciale. OptimiZe distingue deux sens :</p>
<ul class="checklist">
    <li><strong>Recette</strong> — facture émise vers un client (encaissement attendu)</li>
    <li><strong>Dépense</strong> — facture reçue d'un fournisseur (décaissement à faire)</li>
</ul>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Nouvelle facture</div>
            <p>Sidebar → Dépenses &amp; Recettes → <strong>Factures</strong> → <span class="btn-ref">+ Nouvelle facture</span>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Choisir le sens et le tiers</div>
            <p>Sens (recette / dépense), tiers (client ou fournisseur — via annuaire), date d'émission, date d'échéance, objet.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner les montants</div>
            <p>Montant HT, taux de TVA, montant TTC calculé automatiquement.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Valider</div>
            <p><span class="btn-ref">Valider</span> — la facture passe en statut <em>Validée</em> et devient exigible à l'échéance.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Suivre le règlement</div>
            <p>Quand l'encaissement / décaissement a lieu : ouvrez la facture → renseignez le montant réglé → le statut passe automatiquement à <em>Partiellement réglée</em> ou <em>Réglée</em>.</p>
        </td>
    </tr>
</table>

<h3>4.6 · Ordonnancer un paiement</h3>
<p>L'ordonnancement est l'acte juridique qui autorise le paiement d'une facture fournisseur. Sans lui, aucun décaissement possible.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la facture</div>
            <p>Sidebar → Dépenses &amp; Recettes → <strong>Factures</strong> → sélectionner une facture <em>Validée</em>, sens dépense.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Ordonnancer</div>
            <p>Cliquez <span class="btn-ref">Ordonnancer</span>. Vous saisissez la motivation d'ordonnancement (raison, référence du bon de commande, imputation budgétaire).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Le paiement peut être exécuté</div>
            <p>La facture apparaît alors dans « À payer » du dashboard. Le comptable ou le trésorier peut effectuer le décaissement puis marquer la facture <em>Réglée</em>.</p>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le circuit <strong>Facture reçue → Validée → Ordonnancée → Réglée</strong> respecte le principe de séparation des fonctions : l'ordonnateur n'est pas le payeur. Un audit peut retracer chaque décision.
</div>

<h3>4.7 · Créer un ordre de dépense ou de recette</h3>
<p>Un ordre est une <strong>opération financière planifiée</strong> (avant sa matérialisation en écriture au grand-livre). Utile pour les engagements récurrents ou les prévisions.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Nouvel ordre</div>
            <p>Sidebar → Dépenses &amp; Recettes → <strong>Ordres de paiement</strong> → <span class="btn-ref">+ Nouvel ordre</span>. Choisissez le sens et éventuellement un modèle (§3.5).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner</div>
            <p>Objet, montant, ligne budgétaire imputée, rubrique, bénéficiaire, date prévue.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Suivre le workflow</div>
            <p>Ordre → Validation → Ordonnancement → Paiement. À chaque étape, le statut évolue et une trace est conservée.</p>
        </td>
    </tr>
</table>

<h3>4.8 · Clôturer un exercice</h3>
<p>À la fin de l'année, l'exercice est clôturé pour figer les comptes.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Contrôles préalables</div>
            <p>Vérifier qu'aucune écriture n'est en brouillon (dashboard → « Écritures à valider »). Toutes les factures échues doivent être réglées ou lettrées.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Rapports de clôture</div>
            <p>Générer les exports PDF depuis <em>Finance → Exports</em> : Budget exécuté, Grand-livre, Balance des comptes.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Clôturer</div>
            <p>Menu <strong>Exercices</strong> → sélectionner l'exercice → <span class="btn-ref">Clôturer</span>. Le statut passe à <em>Clôturé</em>.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Après clôture, <strong>plus aucune écriture n'est possible</strong> sur l'exercice. Toute correction devra passer par une écriture d'à-nouveau sur l'exercice suivant. Prenez le temps du contrôle final avant de cliquer « Clôturer ».
</div>

<hr class="divider">

{{-- ═════════════ CH 5 ═════════════ --}}
<h2 class="chapter" id="ch5"><span class="chapter-num">Chapitre 5</span>Trois routines à adopter</h2>
<p class="chapter-lead">La discipline financière repose sur trois habitudes régulières.</p>

<h3>Chaque matin — 10 minutes</h3>
<ul class="checklist">
    <li>Ouvrir le tableau de bord Finance.</li>
    <li>Traiter en priorité les <strong>alertes rouges</strong> : lignes en dépassement, factures échues.</li>
    <li>Vérifier les <strong>écritures brouillon</strong> — aucune ne doit rester en instance plus de 24h.</li>
    <li>Consulter les <strong>demandes d'ordonnancement</strong> arrivées durant la nuit.</li>
</ul>

<h3>Chaque semaine — 1 heure (lundi matin)</h3>
<ul class="checklist">
    <li>Ouvrir l'<strong>Exécution budgétaire</strong>, filtrer sur « Alerte ≥90% » — analyser chaque ligne, décider : virement, apport, ou attente ?</li>
    <li>Passer en revue les <strong>modifications budgétaires en attente</strong> — approuver / rejeter / appliquer.</li>
    <li>Éditer et envoyer les <strong>factures clients</strong> de la semaine.</li>
    <li>Contrôler les <strong>relances</strong> sur factures échues (créances clients).</li>
</ul>

<h3>Chaque mois — 3 heures (5 premiers jours du mois)</h3>
<ul class="checklist">
    <li>Clôturer les <strong>écritures du mois précédent</strong> — plus aucun brouillon.</li>
    <li>Analyser le <strong>graphique dépenses 6 mois</strong> — détecter les dérives.</li>
    <li>Vérifier le <strong>top 5 clients</strong> et le <strong>top 5 postes budgétaires</strong> — cohérence avec la stratégie ?</li>
    <li>Générer les <strong>exports PDF</strong> mensuels (budget, grand-livre, balance) pour le comité de direction.</li>
    <li>Rapprocher les comptes de <strong>trésorerie</strong> avec les relevés bancaires.</li>
</ul>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Après un trimestre d'application, votre dashboard n'affichera presque plus d'alertes rouges. Vous ne réagirez plus aux urgences — vous les anticiperez, et la clôture d'exercice se fera sans stress.
</div>
