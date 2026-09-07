{{-- Contenu du guide GRH & Paie — inclus depuis docs/show.blade.php (web) OU docs/pdf.blade.php --}}

<nav class="toc">
    <h2>Sommaire</h2>
    <ol class="toc-list" style="list-style:none; padding:0; margin:0;">
        <li><a href="#ch1">1 · Avant de commencer</a></li>
        <li><a href="#ch2">2 · Se connecter et arriver dans votre espace</a></li>
        <li><a href="#ch3">3 · Configurer le référentiel RH (à faire au démarrage)</a></li>
        <li><a href="#ch4">4 · Vos tâches quotidiennes</a></li>
        <li><a href="#ch5">5 · Trois routines à adopter</a></li>
    </ol>
</nav>

{{-- ═════════════ CH 1 ═════════════ --}}
<h2 class="chapter" id="ch1"><span class="chapter-num">Chapitre 1</span>Avant de commencer</h2>
<p class="chapter-lead">Ce guide vous accompagne du tout premier clic à la maîtrise complète du module GRH &amp; Paie. Il s'adresse au DRH, au gestionnaire de paie et à toute personne chargée de suivre le cycle de vie des collaborateurs.</p>

<h3>Ce que vous allez apprendre</h3>
<p>À la fin de ce manuel, vous serez capable de <strong>configurer</strong> les référentiels RH (grades, contrats, postes…), de <strong>créer et suivre les employés</strong> de leur embauche à leur départ, de <strong>traiter les demandes d'absence</strong>, de <strong>gérer les grades et les avancements</strong> (manuels ou automatiques), de <strong>produire les bulletins de paie</strong>, et de <strong>piloter les recrutements, les évaluations et la discipline</strong>.</p>

<h3>Comment utiliser ce guide</h3>
<p>Chaque chapitre est indépendant. Vous pouvez le lire dans l'ordre pour une prise en main complète, ou aller directement au chapitre qui traite de la tâche que vous avez à faire aujourd'hui. Quatre types d'encarts jalonnent le texte :</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Un raccourci ou une meilleure façon de faire qui vous fera gagner du temps.
</div>
<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Un point de vigilance — une action difficilement réversible ou une erreur à éviter.
</div>
<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Une information contextuelle pour comprendre pourquoi le système fonctionne ainsi.
</div>
<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Ce que vous devriez voir à l'écran une fois l'action réalisée.
</div>

<h3>Ce dont vous avez besoin pour commencer</h3>
<ul class="checklist">
    <li>Un ordinateur, une tablette ou un téléphone connecté à Internet.</li>
    <li>L'adresse d'accès à OptimiZe (fournie par votre administrateur).</li>
    <li>Vos identifiants personnels : e-mail + mot de passe.</li>
    <li>Un rôle permettant les permissions <em>read:employee, read:absence, read:paie, read:recrutement</em> — au minimum.</li>
</ul>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Les données personnelles gérées par le module RH (état civil, salaires, historique disciplinaire) sont considérées comme <strong>sensibles</strong>. OptimiZe intègre nativement des fonctions <strong>d'export RGPD</strong> et <strong>d'anonymisation</strong> réversible tant que l'exercice comptable n'est pas clôturé.
</div>

{{-- ═════════════ CH 2 ═════════════ --}}
<h2 class="chapter" id="ch2"><span class="chapter-num">Chapitre 2</span>Se connecter et arriver dans votre espace</h2>
<p class="chapter-lead">Trois écrans à traverser : la page de connexion, le sélecteur d'espace de travail, puis le tableau de bord RH — votre poste de pilotage social.</p>

<h3>Étape 1 · La page de connexion</h3>
<p>Ouvrez votre navigateur et saisissez l'adresse de votre installation OptimiZe. Vous arrivez sur l'écran de connexion :</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-login">
            <div class="mock-login-card">
                <div class="mock-login-brand">OptimiZe</div>
                <div class="mock-login-sub">ERP intégré · Yubile Technologie</div>
                <table class="mock-form">
                    <tr>
                        <td class="mock-annot"><span>1</span></td>
                        <td>
                            <div class="mock-label">Adresse e-mail</div>
                            <div class="mock-input">drh@entreprise.com</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="mock-annot"><span>2</span></td>
                        <td>
                            <div class="mock-label">Mot de passe</div>
                            <div class="mock-input">••••••••••••</div>
                        </td>
                    </tr>
                    <tr>
                        <td class="mock-annot"><span>3</span></td>
                        <td>
                            <div class="mock-btn-primary">SE CONNECTER</div>
                            <div class="mock-forgot">Mot de passe oublié ?</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Écran de connexion</strong> · Trois champs à remplir, un seul bouton à cliquer.</figcaption>
    <table class="screen-annots">
        <tr><td class="num"><span>1</span></td><td>Saisissez votre <strong>adresse e-mail</strong> professionnelle.</td></tr>
        <tr><td class="num"><span>2</span></td><td>Saisissez votre <strong>mot de passe</strong>. Au premier login, un changement obligatoire vous sera demandé.</td></tr>
        <tr><td class="num"><span>3</span></td><td>Cliquez <span class="btn-ref">SE CONNECTER</span>.</td></tr>
    </table>
</figure>

<h3>Étape 2 · Choisir votre espace de travail</h3>
<p>Dès la connexion réussie, une <strong>fenêtre de sélection</strong> apparaît par-dessus l'accueil. Cliquez sur la carte <strong>GRH &amp; Paie</strong> — vous arrivez directement sur votre tableau de bord.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-picker">
            <div class="mock-picker-title">Bienvenue</div>
            <div class="mock-picker-sub">Choisissez votre espace de travail — ce choix est requis pour continuer.</div>

            <div class="mock-picker-section">Espaces principaux</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Intranet</div><div class="mock-card-desc">Portail collaboratif, actualités</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Réseau Social</div><div class="mock-card-desc">Publications, groupes</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Guide &amp; Docs</div><div class="mock-card-desc">Manuels de prise en main</div></div></td>
                </tr>
            </table>

            <div class="mock-picker-section">Modules métier</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Finance</div><div class="mock-card-desc">Compta, budget, factures</div></div></td>
                    <td><div class="mock-card mock-card-featured"><div class="mock-card-name">GRH &amp; Paie →</div><div class="mock-card-desc">Employés, absences, paie</div></div></td>
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
    <figcaption class="screen-caption"><strong>Sélecteur d'espace de travail</strong> · La carte GRH &amp; Paie est entourée pour la mise en évidence.</figcaption>
</figure>

<h3>Étape 3 · Découvrir votre tableau de bord</h3>
<p>Le tableau de bord RH est <strong>votre poste de pilotage social</strong>. Il vous montre, dès l'ouverture, tout ce qui demande votre attention aujourd'hui.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-dash">
            <div class="mock-dash-hd">
                <table>
                    <tr>
                        <td>
                            <div class="title">RH &amp; Paie</div>
                            <div class="sub">Pilotage — samedi 29 août 2026</div>
                        </td>
                        <td style="text-align:right;"><span class="cta">+ Nouvel employé</span></td>
                    </tr>
                </table>
            </div>

            <table class="mock-alerts">
                <tr>
                    <td><div class="mock-alert warn">
                        <div class="mock-alert-t">3 avancements auto à valider</div>
                        <div class="mock-alert-s">Durée max atteinte</div>
                    </div></td>
                    <td><div class="mock-alert">
                        <div class="mock-alert-t">2 absences en attente &gt; 3j</div>
                        <div class="mock-alert-s">Approbation urgente</div>
                    </div></td>
                    <td><div class="mock-alert warn">
                        <div class="mock-alert-t">1 CDD à échéance</div>
                        <div class="mock-alert-s">Fin de contrat &lt; 30j</div>
                    </div></td>
                </tr>
            </table>

            <table class="mock-kpis">
                <tr>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Effectif actif</div>
                        <div class="mock-kpi-v">142</div>
                        <div class="mock-kpi-s">+3 embauches ce mois</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Masse salariale</div>
                        <div class="mock-kpi-v">18 400 <small>K XAF</small></div>
                        <div class="mock-kpi-s">Ce mois</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Absences en attente</div>
                        <div class="mock-kpi-v">7</div>
                        <div class="mock-kpi-s">4 en cours aujourd'hui</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Bulletins ce mois</div>
                        <div class="mock-kpi-v">142</div>
                        <div class="mock-kpi-s">3 brouillons</div>
                    </div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Tableau de bord RH</strong> · Header, priorités, indicateurs-clés. Plus bas : graphiques masse salariale, top départements, contrats, CDD expirants, absences, embauches récentes, anniversaires du mois.</figcaption>
    <table class="screen-annots">
        <tr><td class="num"><span>1</span></td><td><strong>Header</strong> — titre du module et 3 quick actions (Nouvel employé, Nouvelle absence, Bulletins).</td></tr>
        <tr><td class="num"><span>2</span></td><td><strong>Priorités</strong> — cartes cliquables affichées uniquement quand il y a quelque chose à traiter aujourd'hui.</td></tr>
        <tr><td class="num"><span>3</span></td><td><strong>4 KPIs</strong> — effectif actif, masse salariale ce mois (avec sparkline 6 mois), absences en attente, bulletins ce mois.</td></tr>
    </table>
</figure>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Sur les KPIs financiers (masse salariale), <strong>une hausse s'affiche en rouge</strong> — car pour un DAF, une masse qui monte est un signal budgétaire à surveiller. À l'inverse, une hausse d'embauche s'affiche en vert.
</div>

{{-- ═════════════ CH 3 ═════════════ --}}
<h2 class="chapter" id="ch3"><span class="chapter-num">Chapitre 3</span>Configurer le référentiel RH</h2>
<p class="chapter-lead">Avant de saisir votre premier employé, prenez le temps de renseigner les listes de base. Ce travail se fait <em>une seule fois</em>, mais il conditionne toute votre utilisation quotidienne.</p>

<h3>Pourquoi commencer par le référentiel ?</h3>
<p>Un référentiel, c'est l'ensemble des <strong>listes de choix</strong> qui vous seront proposées quand vous remplirez un formulaire. Quand vous créerez un employé, il vous faudra choisir un type de contrat, un poste, un département — s'ils n'existent pas dans le référentiel, vous ne pourrez pas les sélectionner.</p>

<p>Les référentiels RH se trouvent dans la sidebar de votre espace RH, dans l'accordéon <strong>« Référentiels »</strong> (en bas). Un DRH ou administrateur a tout ce qu'il faut pour les configurer.</p>

<h3>3.1 · Grades et critères d'avancement</h3>
<p>Un <strong>grade</strong> (aussi appelé <em>niveau hiérarchique</em>) classe un employé dans une position — par exemple « Agent d'exécution », « Agent de maîtrise », « Cadre ». C'est le premier référentiel à configurer car il structure toute l'évolution de carrière.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la page Grades</div>
            <p>Sidebar RH → Référentiels → <strong>Grades</strong>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer vos grades dans l'ordre hiérarchique</div>
            <p>Cliquez <span class="btn-ref">+ Nouveau grade</span>. Renseignez :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Code</strong> — abréviation courte (« AE », « AM », « CADRE »).</li>
                <li><strong>Libellé</strong> — nom complet (« Agent d'exécution »).</li>
                <li><strong>Ordre</strong> — nombre indiquant la hiérarchie : 1 pour le grade le plus bas, 2, 3, etc. C'est ce nombre qui détermine <em>quel grade suit lequel</em>.</li>
                <li><strong>Actif</strong> — laissez coché sauf grade obsolète.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Configurer l'avancement automatique (optionnel)</div>
            <p>Si vous voulez que le système propose <em>de lui-même</em> un avancement quand un employé atteint une certaine ancienneté à ce grade :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li>Activez le switch <strong>« Avancement automatique »</strong>.</li>
                <li>Indiquez la <strong>durée max en mois</strong> avant proposition (ex : 36 mois).</li>
                <li>Choisissez le <strong>grade suivant</strong> — laissez vide pour utiliser automatiquement le prochain grade selon l'ordre.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Ajouter les critères pour ce grade</div>
            <p>Cliquez sur le bouton <i class="fas fa-list-check"></i> de la ligne pour ouvrir la fiche du grade. Puis <span class="btn-ref">Ajouter un critère</span> :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Libellé du critère</strong> — condition claire (« 5 ans d'ancienneté minimum », « Master 2 obtenu », « Évaluation satisfaisante 3 années consécutives »).</li>
                <li><strong>Description</strong> — précisions optionnelles.</li>
                <li><strong>Obligatoire ou facultatif</strong> — case à cocher.</li>
            </ul>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Les critères servent de <strong>grille de référence</strong> pour valider un avancement. Quand une proposition automatique arrive, vous consultez les critères du grade cible avant de valider ou refuser.
</div>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Configurez tous vos grades avant de créer vos employés. Ainsi, à l'embauche, vous pourrez leur affecter directement leur grade de départ.
</div>

<h3>3.2 · Types de contrat</h3>
<p>Chaque employé aura un type de contrat (CDI, CDD, Stage, Alternance, Contrat de mission…). Configurez-les depuis <em>Référentiels → Types de contrat</em>. Un code (ex : « CDI »), un libellé.</p>

<h3>3.3 · Postes et départements</h3>
<p>Les postes définissent les rôles opérationnels (« Comptable senior », « Développeur back-end »), les départements structurent votre organigramme (« Direction financière », « IT »). Configurez-les dans <em>Référentiels → Postes</em> et <em>Référentiels → Départements</em>.</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Créer une structure de départements <strong>hiérarchique</strong> (Direction Générale → Directions → Services → Cellules) simplifie ensuite les statistiques par département et les remontées d'information.
</div>

<h3>3.4 · Rubriques et groupes de rubriques (paie)</h3>
<p>Les <strong>rubriques</strong> sont les lignes que vous verrez sur un bulletin de paie : Salaire de base, Prime de transport, Cotisations CNSS, IRPP… Chaque rubrique appartient à un <strong>groupe</strong> (« Rémunération », « Cotisations salariales », « Retenues fiscales »).</p>
<p>Configurez d'abord les <strong>groupes</strong> dans <em>Référentiels → Groupes rubriques paie</em>, puis les <strong>rubriques</strong> dans <em>Référentiels → Rubriques de paie</em> — chaque rubrique est rattachée à un groupe.</p>

<h3>3.5 · Types d'événement carrière</h3>
<p>Un événement carrière est une trace historique — « Mutation », « Promotion », « Détachement », « Retour de mission ». Configurez ces types dans <em>Référentiels → Types évén. carrière</em>. Ils serviront ensuite pour tracer les événements dans la fiche de chaque employé.</p>

<h3>3.6 · Niveaux de qualification &amp; nationalités</h3>
<p>Enfin, deux référentiels de base :</p>
<ul class="checklist">
    <li><strong>Niveaux de qualification</strong> — « Sans diplôme », « CAP/BEP », « Bac », « Bac+2 », « Bac+3 », etc.</li>
    <li><strong>Nationalités</strong> — liste des nationalités présentes dans votre effectif.</li>
</ul>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Une fois vos référentiels configurés, quand vous créerez votre premier employé, toutes les listes déroulantes du formulaire vous proposeront des choix cohérents. Plus de saisie libre — donc pas de doublons, pas de fautes de frappe.
</div>

<hr class="divider">

{{-- ═════════════ CH 4 ═════════════ --}}
<h2 class="chapter" id="ch4"><span class="chapter-num">Chapitre 4</span>Vos tâches quotidiennes, une par une</h2>
<p class="chapter-lead">Huit workflows couvrent 90 % de votre travail dans le module. Ce chapitre les détaille chacun.</p>

<h3>4.1 · Créer un employé (embauche)</h3>
<p>À l'arrivée d'un nouveau collaborateur, ouvrez sa fiche dans OptimiZe.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Bouton « Nouvel employé »</div>
            <p>Depuis le tableau de bord RH, cliquez <span class="btn-ref">+ Nouvel employé</span> en haut à droite.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner l'identité</div>
            <p>Noms, prénoms, matricule (unique), date &amp; lieu de naissance, sexe, nationalité, situation matrimoniale, nombre d'enfants, adresse. Les champs marqués <span class="text-danger">*</span> sont obligatoires.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner le contrat</div>
            <p>Date d'embauche, type de contrat (via référentiel §3.2), date de fin (pour un CDD), poste (via référentiel), département, supérieur hiérarchique, <strong>grade de départ</strong> (via référentiel §3.1), salaire de base.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Enregistrer</div>
            <p>Cliquez <span class="btn-ref">Créer</span>. L'employé est actif et apparaît dans l'effectif. Un compte utilisateur OptimiZe peut être associé pour lui donner accès à l'app.</p>
        </td>
    </tr>
</table>

<h3>4.2 · Gérer les grades et l'historique d'avancement</h3>
<p>Depuis la fiche d'un employé, un bouton vert <strong>« Grade &amp; avancement »</strong> ouvre son historique d'avancement — l'écran le plus important pour le suivi de carrière.</p>

<p>Vous y voyez :</p>
<ul class="checklist">
    <li>Le <strong>profil rapide</strong> à gauche + le <strong>grade actuel</strong> en tête</li>
    <li>Le formulaire <strong>« Nouvel avancement »</strong> — pour enregistrer une évolution manuelle</li>
    <li>La <strong>timeline verticale</strong> à droite — chaque entrée montre : Ancien grade → Nouveau grade, date d'effet, motif, référence du document, personne qui a décidé</li>
</ul>

<h4>Enregistrer un avancement manuel</h4>
<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Sélectionner le nouveau grade</div>
            <p>Dans le formulaire de gauche, choisissez le grade cible dans la liste déroulante. Le système refuse si c'est déjà le grade actuel.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Date d'effet</div>
            <p>Généralement la date d'aujourd'hui. Vous pouvez rétrodater si nécessaire.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Motif, référence, commentaire</div>
            <p><strong>Motif</strong> (« Promotion suite évaluation 2026 »), <strong>référence du document</strong> (n° de note de service ou décision), <strong>commentaire</strong> optionnel.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Enregistrer</div>
            <p>Cliquez <span class="btn-ref">Enregistrer l'avancement</span>. Le grade actuel de l'employé se met à jour <em>immédiatement</em>, la timeline s'enrichit d'une nouvelle entrée en tête.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Un avancement manuel est <strong>directement validé</strong> — pas de circuit d'approbation. Assurez-vous d'avoir la décision hiérarchique avant de saisir.
</div>

<h3>4.3 · Valider les avancements automatiques proposés par le système</h3>
<p>Pour les grades configurés en <strong>avancement automatique</strong> (§3.1), le système détecte les employés éligibles et propose leur avancement dans une section dédiée du tableau de bord :</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir le tableau de bord RH</div>
            <p>Faites défiler jusqu'à la section <strong>« Avancements automatiques en attente de validation »</strong>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Lancer la détection (si nécessaire)</div>
            <p>Cliquez <span class="btn-ref">Détecter maintenant</span> pour analyser les employés qui ont atteint leur durée max au grade actuel. L'opération est <em>idempotente</em> — vous pouvez cliquer plusieurs fois sans créer de doublon.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Étudier chaque proposition</div>
            <p>Pour chaque ligne, vous voyez : le nom de l'employé (cliquable → sa fiche d'avancement), son grade actuel → le grade cible proposé, le motif (« Après 36 mois au grade Agent d'exécution »).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Vérifier les critères</div>
            <p>Ouvrez la fiche du grade cible (menu Référentiels → Grades → cliquer sur le grade) pour consulter la liste des <strong>critères</strong> à remplir. Comparez avec le dossier de l'employé.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Décider — Valider ou Refuser</div>
            <ul style="list-style:none; padding:0; margin:.5rem 0;">
                <li style="padding:.35rem 0;"><span class="btn-ref">Valider</span> — le grade actuel de l'employé est mis à jour. Une trace dans l'historique conserve « validé le … par vous ».</li>
                <li style="padding:.35rem 0;"><span class="btn-ref btn-danger">Refuser</span> — une modale s'ouvre pour saisir le <strong>motif du refus</strong> (obligatoire, min. 5 caractères). L'employé conserve son grade actuel.</li>
            </ul>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Un avancement automatique n'est jamais appliqué sans votre validation. C'est la garantie que la <strong>décision d'évolution reste humaine</strong>, le système n'étant qu'un facilitateur qui vous rappelle les échéances.
</div>

<h3>4.4 · Traiter une demande d'absence</h3>
<p>Un employé demande un congé, une autorisation, un arrêt maladie. Sa demande arrive dans votre file d'attente.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la liste</div>
            <p>Menu <strong>Temps &amp; Présence → Absences</strong> ou cliquez sur l'alerte du dashboard « X absences en attente ».</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la demande</div>
            <p>Cliquez sur son libellé. Vous voyez : demandeur, type d'absence, dates début/fin, motif, justificatifs joints.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Vérifier le solde de congés</div>
            <p>Pour un congé annuel, contrôlez le solde disponible depuis <em>Temps &amp; Présence → Soldes de congés</em>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Valider ou refuser</div>
            <p>Deux boutons en tête de fiche : <span class="btn-ref">Valider</span> ou <span class="btn-ref btn-danger">Refuser</span> (motif requis). L'employé est notifié automatiquement.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Une absence en attente depuis <strong>plus de 3 jours</strong> apparaît en rouge dans le priority bar du dashboard. Traiter rapidement les demandes évite la frustration et les rappels à répétition.
</div>

<h3>4.5 · Créer et éditer un bulletin de paie</h3>
<p>Un bulletin correspond à la rémunération d'un employé pour une période (généralement un mois).</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Menu Paie</div>
            <p>Sidebar RH → Paie &amp; Rémunération → <strong>Bulletins</strong>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer</div>
            <p>Cliquez <span class="btn-ref">+ Nouveau bulletin</span>. Sélectionnez l'employé, la période (début-fin), le libellé.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Ajouter les rubriques</div>
            <p>Ligne par ligne : Salaire de base, Primes, Indemnités, Heures sup, Cotisations, IRPP… Les rubriques disponibles viennent du référentiel (§3.4). Chaque ligne a une valeur (montant, taux, ou base × taux selon la rubrique).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Le net à payer se calcule</div>
            <p>Les totaux (brut, cotisations, IRPP, net imposable, <strong>net à payer</strong>) se recalculent en direct au fur et à mesure de la saisie.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Valider et payer</div>
            <p>Cliquez <span class="btn-ref">Valider</span>. Le bulletin passe en statut <em>Validé</em>. Une fois payé (virement effectué), cliquez <span class="btn-ref">Marquer payé</span> pour finaliser.</p>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le nombre de bulletins <strong>brouillon</strong> apparaît en jaune sur le priority bar. Une routine de fin de mois consiste à vérifier qu'aucun brouillon ne reste à valider avant clôture de la paie.
</div>

<h3>4.6 · Traiter un recrutement</h3>
<p>Vous ouvrez un poste ; les postulants candidatent ; vous suivez le processus jusqu'à l'embauche.</p>

<ol class="steps">
    <li class="step">
        <table><tr>
            <td class="step-num"><span>1</span></td>
            <td class="step-body">
                <div class="step-title">Créer le recrutement</div>
                <p>Menu <strong>Recrutement → Postes ouverts</strong> → <span class="btn-ref">+ Nouveau recrutement</span>. Renseignez le poste, le département, la date limite, le nombre de postes.</p>
            </td>
        </tr></table>
    </li>
</ol>

<table class="steps">
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Ajouter les postulants</div>
            <p>Saisissez chaque postulant (nom, contact, CV joint). Vous pouvez les noter, ajouter des commentaires d'entretien.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Suivre le pipeline</div>
            <p>Chaque postulant a un statut : <em>Reçu</em>, <em>Convoqué</em>, <em>Testé</em>, <em>Retenu</em>, <em>Refusé</em>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Convertir en employé</div>
            <p>Une fois le candidat retenu et le contrat signé, revenez à §4.1 (créer un employé). Vous pouvez copier ses coordonnées depuis sa fiche postulant.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Clôturer le recrutement</div>
            <p>Une fois le poste pourvu, marquez le recrutement <em>Clôturé</em>.</p>
        </td>
    </tr>
</table>

<h3>4.7 · Enregistrer une évaluation de performance</h3>
<p>Les évaluations permettent de suivre la performance des collaborateurs et alimentent les décisions d'avancement.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Nouvelle évaluation</div>
            <p>Menu <strong>Performance &amp; Discipline → Évaluations</strong> → <span class="btn-ref">+ Nouvelle évaluation</span>. Choisissez l'employé, la période évaluée, le type (annuelle, semestrielle, à la demande).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Renseigner la grille</div>
            <p>Notez sur les axes définis (objectifs atteints, savoir-être, compétences techniques…) selon l'échelle configurée.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Commentaires et plan d'action</div>
            <p>Ajoutez le compte-rendu de l'entretien et éventuellement des objectifs pour la période suivante.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Signer et clôturer</div>
            <p>L'évaluation doit être signée par l'évalué + l'évaluateur avant clôture. Une fois clôturée, elle apparaît dans l'historique de l'employé et peut servir de justificatif pour un avancement.</p>
        </td>
    </tr>
</table>

<h3>4.8 · Traiter une sanction ou un départ</h3>

<h4>Enregistrer une sanction disciplinaire</h4>
<p>Menu <strong>Performance &amp; Discipline → Sanctions</strong> → <span class="btn-ref">+ Nouvelle sanction</span>. Choisissez l'employé, le type (avertissement, blâme, mise à pied, licenciement), la date, la référence du document, le motif. La sanction reste active jusqu'à sa clôture (date d'expiration ou levée anticipée).</p>

<h4>Enregistrer un départ</h4>
<p>Menu <strong>Performance &amp; Discipline → Départs</strong> → <span class="btn-ref">+ Nouveau départ</span>. Type de départ (démission, retraite, licenciement, fin de CDD…), date d'effet, préavis, motif, documents de sortie.</p>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Enregistrer un départ change automatiquement le statut de l'employé de « Actif » à « Sorti ». Il n'apparaîtra plus dans l'effectif actif, mais son historique reste consultable et sa paie continue d'être traitable pour le solde de tout compte.
</div>

<hr class="divider">

{{-- ═════════════ CH 5 ═════════════ --}}
<h2 class="chapter" id="ch5"><span class="chapter-num">Chapitre 5</span>Trois routines à adopter</h2>
<p class="chapter-lead">Une bonne utilisation d'OptimiZe RH tient en trois habitudes. Adoptez-les et le module travaille pour vous.</p>

<h3>Chaque matin — 10 minutes</h3>
<ul class="checklist">
    <li>Ouvrir le tableau de bord RH.</li>
    <li>Regarder la barre de priorités en haut. Traiter en priorité : absences en retard &gt; 3j, avancements auto en attente.</li>
    <li>Vérifier les demandes de congés arrivées durant la nuit.</li>
    <li>Consulter les anniversaires du jour (bloc anniversaires du mois) pour un message personnel.</li>
</ul>

<h3>Chaque semaine — 45 minutes (lundi matin)</h3>
<ul class="checklist">
    <li>Lancer la <strong>détection des avancements automatiques</strong> et valider/refuser les propositions.</li>
    <li>Passer en revue les <strong>CDD arrivant à échéance</strong> dans les 30 jours — décider : renouveler, transformer en CDI, ou notifier la fin.</li>
    <li>Vérifier les <strong>bulletins de paie brouillon</strong> — aucun ne doit rester en instance.</li>
    <li>Contrôler les <strong>recrutements ouverts</strong> — relancer les postulants en cours si nécessaire.</li>
</ul>

<h3>Chaque mois — 2 heures (dernier vendredi)</h3>
<ul class="checklist">
    <li>Analyser le graphique <strong>Masse salariale (6 mois)</strong>. Une hausse anormale mérite investigation.</li>
    <li>Faire un point sur les <strong>départements sur-effectifs</strong> (top départements) vs les objectifs organisationnels.</li>
    <li>Clôturer les <strong>bulletins du mois</strong> avant le 5 du mois suivant.</li>
    <li>Consulter le <strong>répartition des contrats</strong> (donut) — cible : maintenir le bon équilibre CDI/CDD/Stage selon votre stratégie RH.</li>
    <li>Extraire l'<strong>audit log</strong> (Vue d'ensemble → Audit log) — traçabilité des modifications sensibles (paie, sanctions, départs).</li>
</ul>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Après un mois d'application de ces trois routines, votre dashboard ne devrait presque plus jamais afficher d'alertes rouges. Vous ne subissez plus les urgences RH — vous les anticipez.
</div>
