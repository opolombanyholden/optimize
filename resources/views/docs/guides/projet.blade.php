{{-- Contenu du guide Gestion de Projet & Tâche (PMP) --}}

<nav class="toc">
    <h2>Sommaire</h2>
    <ol class="toc-list" style="list-style:none; padding:0; margin:0;">
        <li><a href="#ch1">1 · Avant de commencer</a></li>
        <li><a href="#ch2">2 · Se connecter et arriver dans l'espace Projet</a></li>
        <li><a href="#ch3">3 · Comprendre la méthodologie PMP appliquée</a></li>
        <li><a href="#ch4">4 · Vos tâches quotidiennes</a></li>
        <li><a href="#ch5">5 · Trois routines à adopter</a></li>
    </ol>
</nav>

{{-- ═════════════ CH 1 ═════════════ --}}
<h2 class="chapter" id="ch1"><span class="chapter-num">Chapitre 1</span>Avant de commencer</h2>
<p class="chapter-lead">Ce guide accompagne le chef de projet, le membre d'équipe et le PMO dans l'utilisation complète du module Gestion de Projet &amp; Tâche. Il applique les principes du <strong>PMBOK / PMP</strong> (Project Management Professional) tout en restant accessible aux non-spécialistes.</p>

<h3>Ce que vous allez apprendre</h3>
<p>Créer et planifier un projet complet (WBS, phases, jalons), affecter des ressources, suivre l'exécution (tâches, feuilles de temps, livrables), maîtriser les coûts et l'avancement (EVM), tracer les risques et problèmes, gouverner (parties prenantes, changements, leçons), et clôturer proprement.</p>

<h3>Comment utiliser ce guide</h3>
<p>Quatre types d'encarts jalonnent le texte :</p>
<div class="callout callout-tip"><span class="callout-label">Astuce</span>Un raccourci ou une meilleure façon de faire.</div>
<div class="callout callout-warn"><span class="callout-label">Attention</span>Un point de vigilance — action difficilement réversible.</div>
<div class="callout callout-info"><span class="callout-label">Le saviez-vous ?</span>Une info contextuelle pour comprendre le fonctionnement.</div>
<div class="callout callout-ok"><span class="callout-label">Résultat attendu</span>Ce que vous devriez voir une fois l'action réalisée.</div>

<h3>Ce dont vous avez besoin</h3>
<ul class="checklist">
    <li>Vos identifiants OptimiZe.</li>
    <li>Rôle disposant au minimum de <em>read:projet_intranet, read:tache_intranet</em>.</li>
    <li>Rôle <strong>chef de projet</strong> (ou admin) pour créer/éditer/valider un projet.</li>
</ul>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le module applique le <strong>principe de cascade</strong> PMP : les tâches (avec pondération) alimentent l'avancement de leur phase, qui alimente l'avancement du projet. Les coûts remontent de la même manière. Vous ne saisissez qu'au niveau tâche — tout le reste se calcule.
</div>

{{-- ═════════════ CH 2 ═════════════ --}}
<h2 class="chapter" id="ch2"><span class="chapter-num">Chapitre 2</span>Se connecter et arriver dans votre espace</h2>
<p class="chapter-lead">Trois écrans : login, sélecteur d'espace, tableau de bord Projet — votre poste de pilotage.</p>

<h3>Étape 1 · La page de connexion</h3>
<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-login">
            <div class="mock-login-card">
                <div class="mock-login-brand">OptimiZe</div>
                <div class="mock-login-sub">ERP intégré · Yubile Technologie</div>
                <table class="mock-form">
                    <tr><td class="mock-annot"><span>1</span></td><td><div class="mock-label">Adresse e-mail</div><div class="mock-input">chef.projet@entreprise.com</div></td></tr>
                    <tr><td class="mock-annot"><span>2</span></td><td><div class="mock-label">Mot de passe</div><div class="mock-input">••••••••••••</div></td></tr>
                    <tr><td class="mock-annot"><span>3</span></td><td><div class="mock-btn-primary">SE CONNECTER</div></td></tr>
                </table>
            </div>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Écran de connexion</strong></figcaption>
</figure>

<h3>Étape 2 · Choisir l'espace Gestion Projet</h3>
<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-picker">
            <div class="mock-picker-title">Bienvenue</div>
            <div class="mock-picker-sub">Choisissez votre espace de travail — ce choix est requis pour continuer.</div>
            <div class="mock-picker-section">Espaces principaux</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Intranet</div><div class="mock-card-desc">Portail collaboratif</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Réseau Social</div><div class="mock-card-desc">Publications</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Guide &amp; Docs</div><div class="mock-card-desc">Manuels</div></div></td>
                </tr>
            </table>
            <div class="mock-picker-section">Modules métier</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Finance</div><div class="mock-card-desc">Compta</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">GRH &amp; Paie</div><div class="mock-card-desc">Employés</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Achats &amp; MG</div><div class="mock-card-desc">Commandes</div></div></td>
                </tr>
                <tr>
                    <td><div class="mock-card mock-card-featured"><div class="mock-card-name">Gestion Projet →</div><div class="mock-card-desc">Projets, phases, tâches</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Stratégie</div><div class="mock-card-desc">Objectifs, KPI</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Mon profil</div><div class="mock-card-desc">Ma fiche</div></div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Sélecteur d'espace</strong> · La carte Gestion Projet est mise en évidence.</figcaption>
</figure>

<h3>Étape 3 · Le tableau de bord Projet</h3>
<p>Le dashboard Projet vous montre à l'ouverture : projets en cours, tâches urgentes, jalons proches, risques critiques.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-dash">
            <div class="mock-dash-hd">
                <table>
                    <tr>
                        <td>
                            <div class="title">Gestion de Projet &amp; Tâche</div>
                            <div class="sub">Pilotage — lundi 31 août 2026</div>
                        </td>
                        <td style="text-align:right;"><span class="cta">+ Nouveau projet</span></td>
                    </tr>
                </table>
            </div>

            <table class="mock-alerts">
                <tr>
                    <td><div class="mock-alert"><div class="mock-alert-t">3 tâches en retard</div><div class="mock-alert-s">Assignées à moi</div></div></td>
                    <td><div class="mock-alert warn"><div class="mock-alert-t">2 jalons cette semaine</div><div class="mock-alert-s">Livraison prévue</div></div></td>
                    <td><div class="mock-alert"><div class="mock-alert-t">1 risque critique</div><div class="mock-alert-s">Projet Alpha</div></div></td>
                </tr>
            </table>

            <table class="mock-kpis">
                <tr>
                    <td><div class="mock-kpi"><div class="mock-kpi-l">Projets actifs</div><div class="mock-kpi-v">14</div><div class="mock-kpi-s">3 en retard</div></div></td>
                    <td><div class="mock-kpi"><div class="mock-kpi-l">Tâches ouvertes</div><div class="mock-kpi-v">128</div><div class="mock-kpi-s">42 mes tâches</div></div></td>
                    <td><div class="mock-kpi"><div class="mock-kpi-l">Avancement moy.</div><div class="mock-kpi-v">62<small>%</small></div></div></td>
                    <td><div class="mock-kpi"><div class="mock-kpi-l">Budget consommé</div><div class="mock-kpi-v">18 400 <small>K XAF</small></div><div class="mock-kpi-s">Sur 30 500 K planifiés</div></div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Tableau de bord Projet</strong> · Priorités du jour + KPIs globaux.</figcaption>
</figure>

<h3>Sidebar Projet — les priorités mises en avant</h3>
<p>La sidebar met en priorité les 2 fonctions les plus utilisées : <strong>Projets</strong> et <strong>Tâches</strong>. Chacune a un bouton <span class="btn-ref">+</span> inline pour la création rapide.</p>
<ul class="checklist">
    <li><strong>Tableau de bord</strong> (accueil pilotage)</li>
    <li><strong>Projets</strong> — liste + [+] création</li>
    <li><strong>Tâches</strong> — liste + [+] création (avec badges de tâches urgentes)</li>
    <li>Puis les <strong>6 accordéons</strong> : Planification · Exécution · Maîtrise · Risques · Gouvernance · Référentiels</li>
</ul>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Un <strong>bouton flottant teal</strong> en bas à droite (icône check-list) est accessible depuis toute l'application — il ramène vers votre liste de tâches en un clic, où que vous soyez.
</div>

<h3>Alerte tâches perso — un pop-up toutes les 2 minutes</h3>
<p>Dans l'espace Projet, si vous avez des <strong>tâches en dépassement</strong> ou des <strong>tâches non démarrées</strong> assignées, un pop-up apparaît automatiquement toutes les 2 minutes tant qu'elles restent en attente. Comme le pop-up de rupture de stock côté Achats, c'est le système qui vous force à ne pas oublier.</p>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le filtre de <strong>visibilité 3-axes</strong> apparaît en tête des listes Projets et Tâches : cochez « Mes données », « Mes groupes » ou « Publiques » pour cibler ce que vous voyez. Aucune case cochée = tout visible selon vos droits.
</div>

{{-- ═════════════ CH 3 ═════════════ --}}
<h2 class="chapter" id="ch3"><span class="chapter-num">Chapitre 3</span>Comprendre la méthodologie PMP appliquée</h2>
<p class="chapter-lead">Le module suit une hiérarchie stricte inspirée du PMBOK. Comprendre cette structure vous fait gagner du temps sur toutes les opérations.</p>

<h3>La hiérarchie en 4 niveaux</h3>

<table class="data">
    <thead><tr><th>Niveau</th><th>Contient</th><th>Rôle</th></tr></thead>
    <tbody>
        <tr><td><strong>Projet</strong></td><td>Phases + Jalons + Parties prenantes + Risques + …</td><td>Livrable macro avec objectifs, budget, dates de début/fin</td></tr>
        <tr><td><strong>Phase</strong></td><td>Tâches + Sous-phases (WBS)</td><td>Étape structurante du projet — regroupe des activités cohérentes</td></tr>
        <tr><td><strong>Jalon</strong></td><td>—</td><td>Point de contrôle à date fixe (livraison, réunion COPIL, décision go/no-go)</td></tr>
        <tr><td><strong>Tâche</strong></td><td>Checklist + Historique + Pièces jointes</td><td>Unité de travail concrète, assignable à un membre</td></tr>
    </tbody>
</table>

<h3>Cascade automatique — vous saisissez, le système calcule</h3>
<div class="flow">
    <div class="flow-step"><div class="flow-step-num">1</div><div class="flow-step-name">Tâche</div><div class="flow-step-who">Vous saisissez avancement + coût réel</div></div>
    <div class="flow-step"><div class="flow-step-num">2</div><div class="flow-step-name">Phase</div><div class="flow-step-who">Somme pondérée automatique</div></div>
    <div class="flow-step"><div class="flow-step-num">3</div><div class="flow-step-name">Projet</div><div class="flow-step-who">Consolidation globale</div></div>
</div>

<p>Concrètement : chaque tâche a une <strong>pondération %</strong> qui indique son poids dans la phase. Quand vous mettez à jour son avancement (0-100 %), la phase se recalcule automatiquement, puis le projet. Idem pour les coûts (<em>cout_estime</em> + <em>cout_reel</em> par tâche → <em>budget_consomme</em> par projet).</p>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Une tâche sans pondération = 0 % de poids dans la phase — elle n'influence pas l'avancement. Toujours mettre une valeur (défaut : 1 par tâche pour un poids égal).
</div>

<h3>Workflow de clôture (validation)</h3>
<p>Une phase, un jalon, une tâche ou un projet peut nécessiter une <strong>validation formelle avant clôture</strong>. Le workflow :</p>
<ol class="steps">
    <li class="step"><div><div class="step-title">Le porteur soumet</div><p>« Demander la clôture » → l'entité passe en statut <em>Soumise à validation</em>.</p></div></li>
    <li class="step"><div><div class="step-title">Les valideurs statuent</div><p>Chaque valideur désigné voit une notification et peut approuver ou refuser (avec motif).</p></div></li>
    <li class="step"><div><div class="step-title">Décision consolidée</div><p>Si tous approuvent → statut <em>Clôturée</em>. Un refus → renvoi au porteur avec commentaires.</p></div></li>
</ol>

<h3>Protection — verrou anti-modification sauvage</h3>
<p>Les entités avec contenu (projet actif, phase avancée) sont automatiquement <strong>protégées</strong> : toute demande de modification ou suppression doit passer par une <strong>demande formelle</strong> au super-admin qui approuve ou refuse. Historique conservé.</p>

<hr class="divider">

{{-- ═════════════ CH 4 ═════════════ --}}
<h2 class="chapter" id="ch4"><span class="chapter-num">Chapitre 4</span>Vos tâches quotidiennes</h2>
<p class="chapter-lead">Dix workflows couvrent la quasi-totalité de votre usage.</p>

<h3>4.1 · Créer un nouveau projet</h3>
<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Cliquer + à côté de Projets</div><p>Sidebar → clic sur le <span class="btn-ref">+</span> à droite de « Projets » (raccourci direct).</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Renseigner l'entête</div><p>Nom, description, catégorie, chef de projet, sponsor, dates début/fin, budget prévu, priorité.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Choisir la visibilité</div><p><em>Public</em> (tous), <em>Privé</em> avec cibles (users/groupes/entités), <em>Brouillon</em> (non publié).</p></td></tr>
    <tr><td class="step-num"><span>4</span></td><td class="step-body"><div class="step-title">Enregistrer</div><p><span class="btn-ref">Créer</span>. Le projet apparaît dans la liste. Vous pouvez ensuite lui ajouter phases et jalons.</p></td></tr>
</table>

<h3>4.2 · Construire le WBS (Work Breakdown Structure)</h3>
<p>Le WBS décompose votre projet en phases hiérarchiques. C'est le squelette du plan de travail.</p>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Ouvrir le projet</div><p>Sidebar accordéon Planification → <strong>WBS &amp; Phases</strong>.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Ajouter une phase</div><p><span class="btn-ref">+ Nouvelle phase</span>. Nom, dates début/fin, pondération dans le projet (%). Répétez pour chaque grande étape.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Sous-phases (optionnel)</div><p>Vous pouvez créer des sous-phases sous une phase parente pour un WBS à 2-3 niveaux. Utile pour les projets complexes.</p></td></tr>
    <tr><td class="step-num"><span>4</span></td><td class="step-body"><div class="step-title">Réorganiser (drag &amp; drop)</div><p>Glissez-déposez les phases pour changer l'ordre. La numérotation WBS (1, 1.1, 1.2, 2…) se recalcule automatiquement.</p></td></tr>
</table>

<h3>4.3 · Poser les jalons</h3>
<p>Un jalon est un point de contrôle à date fixe (livraison, décision, revue). Pas de durée — c'est un instantané.</p>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Menu Jalons</div><p>Accordéon Planification → <strong>Jalons</strong> → <span class="btn-ref">+ Nouveau jalon</span>.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Renseigner</div><p>Libellé (« Recette utilisateur », « Signature contrat »), date prévue, criticité, propriétaire.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Suivre</div><p>À l'approche de la date, le jalon apparaît sur le dashboard « Jalons prochains ». Après passage : marquer <span class="btn-ref">Atteint</span> ou <span class="btn-ref btn-danger">Manqué</span>.</p></td></tr>
</table>

<h3>4.4 · Créer et gérer les tâches</h3>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Créer une tâche</div><p>Sidebar → <span class="btn-ref">+</span> à côté de Tâches. Ou depuis la fiche projet → phase → <span class="btn-ref">+ Nouvelle tâche</span>.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Renseigner</div><p>Libellé, phase parente, dates début/fin, priorité, statut (<em>Non démarré</em> par défaut), pondération, responsable.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Assigner les intervenants</div><p>Un <strong>responsable</strong> (qui rend compte) et 0-N <strong>assignés</strong> (qui font le travail). Multi-assignation supportée.</p></td></tr>
    <tr><td class="step-num"><span>4</span></td><td class="step-body"><div class="step-title">Configurer la validation</div><p>Ajoutez éventuellement des valideurs (utilisateurs ou groupes) qui devront approuver la clôture.</p></td></tr>
    <tr><td class="step-num"><span>5</span></td><td class="step-body"><div class="step-title">Ajouter checklist et pièces jointes</div><p>La tâche a une checklist interne (sous-tâches concrètes) et un espace pour uploader des documents (spécifications, maquettes, résultats).</p></td></tr>
    <tr><td class="step-num"><span>6</span></td><td class="step-body"><div class="step-title">Faire évoluer</div><p>Au fil du travail, mettez à jour l'<strong>avancement</strong> (0-100 %). Toute modification est tracée dans l'historique.</p></td></tr>
    <tr><td class="step-num"><span>7</span></td><td class="step-body"><div class="step-title">Clôturer</div><p><span class="btn-ref">Demander clôture</span> → si validation activée, workflow d'approbation. Sinon, passage direct au statut <em>Terminé</em>.</p></td></tr>
</table>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Filtrez votre liste avec les 3 cases <strong>Mes données · Mes groupes · Publiques</strong> en tête de page. Cochez « Mes données » pour ne voir que vos tâches personnelles.
</div>

<h3>4.5 · Saisir vos feuilles de temps</h3>
<p>Une feuille de temps enregistre les heures passées sur les tâches d'un projet, semaine par semaine.</p>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Menu Feuilles de temps</div><p>Accordéon Exécution → <strong>Feuilles de temps</strong> → <span class="btn-ref">+ Nouvelle feuille</span>.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Choisir la semaine</div><p>Sélecteur date début/fin. Grille des 7 jours affichée.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Ajouter les lignes</div><p>Chaque ligne : projet → phase → tâche → heures par jour. Total automatique en bas.</p></td></tr>
    <tr><td class="step-num"><span>4</span></td><td class="step-body"><div class="step-title">Soumettre</div><p><span class="btn-ref">Soumettre</span> — envoi au chef de projet pour validation.</p></td></tr>
</table>

<h3>4.6 · Suivre les livrables</h3>

<p>Chaque livrable (document, prototype, code, présentation) a sa fiche avec date prévue, responsable, statut. Le suivi permet de savoir <strong>où on en est dans les productions</strong> — indépendamment de l'avancement de la tâche qui les produit.</p>

<p>Accordéon Exécution → <strong>Livrables</strong> → <span class="btn-ref">+ Nouveau livrable</span>. Nom, description, date prévue, responsable, projet/phase, fichier joint. Marquez <span class="btn-ref">Remis</span> avec pièce jointe finale une fois produit.</p>

<h3>4.7 · Maîtriser les coûts &amp; l'EVM</h3>

<p>La <strong>Valeur Acquise</strong> (Earned Value Management) est l'outil PMP par excellence pour mesurer si votre projet est <em>on time / on budget</em>.</p>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Menu Coûts &amp; Budget</div><p>Accordéon Maîtrise → <strong>Coûts &amp; Budget</strong>. Vous voyez ligne par ligne : coût estimé, coût réel, écart.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Menu Valeur acquise (EVM)</div><p>Accordéon Maîtrise → <strong>Valeur acquise (EVM)</strong>. Trois indicateurs clés :</p><ul class="checklist" style="margin-top:.4rem;"><li><strong>PV</strong> (Planned Value) — budget prévu à ce jour</li><li><strong>EV</strong> (Earned Value) — travail réellement fait × coût prévu</li><li><strong>AC</strong> (Actual Cost) — dépense réelle à ce jour</li></ul></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Interpréter</div><p><strong>SPI = EV / PV</strong> (Schedule Performance Index). Si &lt; 1 → retard sur le planning.<br><strong>CPI = EV / AC</strong> (Cost Performance Index). Si &lt; 1 → dépassement de budget.<br>Les 2 courbes doivent rester proches. Une divergence croissante = signal d'alerte fort.</p></td></tr>
</table>

<h3>4.8 · Gérer les risques et les problèmes</h3>

<h4>Registre des risques (anticipation)</h4>
<p>Un risque est un événement qui <em>pourrait</em> se produire. Menu <strong>Risques → Registre</strong>. Pour chaque risque : description, probabilité (1-5), impact (1-5) → criticité auto (probabilité × impact), plan de mitigation, propriétaire.</p>

<h4>Journal des problèmes (réaction)</h4>
<p>Un problème est un événement <em>qui se produit</em> et bloque l'avancement. Menu <strong>Risques → Journal des problèmes</strong>. Description, date d'apparition, criticité, plan de résolution, propriétaire, statut (Ouvert / En cours / Résolu / Fermé).</p>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Un risque non traité qui se matérialise devient un problème. Le module conserve le lien : depuis un problème, vous pouvez cliquer « Provenait du risque #X » pour tracer l'origine et alimenter les leçons apprises.
</div>

<h3>4.9 · Gouverner (parties prenantes + changements)</h3>

<h4>Parties prenantes</h4>
<p>Menu <strong>Gouvernance → Parties prenantes</strong>. Liste des personnes ou organisations qui influent sur le projet (sponsor, client, fournisseur, régulateur…). Pour chacun : rôle (via référentiel Rôles projet), pouvoir, intérêt, stratégie d'engagement.</p>

<h4>Changements</h4>
<p>Un changement est une modification du périmètre initial (délai, budget, scope, ressources). Menu <strong>Maîtrise → Changements</strong> → <span class="btn-ref">+ Nouveau changement</span>. Motif, impact (délai/budget/scope), demandeur, approbateur, statut. Un changement approuvé met à jour le baseline du projet.</p>

<h3>4.10 · Clôturer un projet</h3>

<table class="steps">
    <tr><td class="step-num"><span>1</span></td><td class="step-body"><div class="step-title">Vérifications préalables</div><p>Toutes les tâches doivent être terminées. Tous les jalons atteints (ou explicitement manqués). Tous les livrables remis. Aucun problème ouvert.</p></td></tr>
    <tr><td class="step-num"><span>2</span></td><td class="step-body"><div class="step-title">Capturer les leçons apprises</div><p>Menu <strong>Gouvernance → Leçons apprises</strong> → ajouter les enseignements du projet (positifs et négatifs) qui serviront aux futurs projets similaires.</p></td></tr>
    <tr><td class="step-num"><span>3</span></td><td class="step-body"><div class="step-title">Rédiger le rapport final</div><p>Menu <strong>Gouvernance → Rapports &amp; CR</strong> → <span class="btn-ref">+ Nouveau rapport</span>. Type « Rapport de clôture ». Synthèse, indicateurs finaux (SPI/CPI), écarts par rapport au baseline.</p></td></tr>
    <tr><td class="step-num"><span>4</span></td><td class="step-body"><div class="step-title">Demander clôture</div><p>Fiche projet → <span class="btn-ref">Demander clôture</span>. Le workflow de validation démarre (valideurs désignés statuent).</p></td></tr>
    <tr><td class="step-num"><span>5</span></td><td class="step-body"><div class="step-title">Clôture définitive</div><p>Après approbation de tous, statut → <em>Clôturé</em>. Le projet reste consultable mais devient <strong>immuable</strong> — toute modification passera par une demande formelle au super-admin.</p></td></tr>
</table>

<hr class="divider">

{{-- ═════════════ CH 5 ═════════════ --}}
<h2 class="chapter" id="ch5"><span class="chapter-num">Chapitre 5</span>Trois routines à adopter</h2>
<p class="chapter-lead">La discipline projet tient dans trois rythmes complémentaires.</p>

<h3>Chaque matin — 10 minutes</h3>
<ul class="checklist">
    <li>Ouvrir le tableau de bord Projet.</li>
    <li>Réagir au <strong>pop-up d'alerte</strong> tâches (en retard + non démarrées).</li>
    <li>Vérifier les <strong>jalons du jour</strong> et de la semaine.</li>
    <li>Consulter les <strong>problèmes ouverts</strong> sur vos projets.</li>
</ul>

<h3>Chaque semaine — 45 minutes (vendredi après-midi)</h3>
<ul class="checklist">
    <li>Mettre à jour l'<strong>avancement</strong> de toutes vos tâches (cascade auto sur phases/projet).</li>
    <li>Soumettre votre <strong>feuille de temps</strong> hebdomadaire.</li>
    <li>Revoir le <strong>registre des risques</strong> — nouveaux risques à ajouter ? Anciens à archiver ?</li>
    <li>Rédiger un <strong>CR hebdomadaire</strong> pour vos projets importants.</li>
</ul>

<h3>Chaque mois — 2 heures (premier vendredi)</h3>
<ul class="checklist">
    <li>Analyser l'<strong>EVM</strong> pour chaque projet actif : SPI, CPI, écarts baseline. Détecter les dérives.</li>
    <li>Passer en revue les <strong>changements en cours</strong> — approuver / rejeter / prioriser.</li>
    <li>Faire un point avec chaque <strong>partie prenante clé</strong> (sponsor, client).</li>
    <li>Compléter le <strong>plan d'action</strong> pour le mois suivant : jalons à atteindre, livrables à produire.</li>
</ul>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Après un trimestre d'application, vos projets seront <strong>prévisibles</strong>. Les surprises deviendront rares. Le dashboard n'affichera presque plus d'alertes rouges. Vous aurez transformé la gestion de projet d'un exercice réactif en une <strong>pratique proactive</strong>.
</div>
