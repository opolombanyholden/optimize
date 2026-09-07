{{-- Contenu du guide Achats & MG — inclus depuis docs/show.blade.php (web) OU docs/pdf.blade.php --}}
{{-- Utilise des structures <table> plutôt que grid/flex pour compatibilité dompdf --}}

<nav class="toc">
    <h2>Sommaire</h2>
    <ol class="toc-list" style="list-style:none; padding:0; margin:0;">
        <li><a href="#ch1">1 · Avant de commencer</a></li>
        <li><a href="#ch2">2 · Se connecter et arriver dans votre espace</a></li>
        <li><a href="#ch3">3 · Configurer le référentiel (à faire une seule fois)</a></li>
        <li><a href="#ch4">4 · Vos tâches quotidiennes, une par une</a></li>
        <li><a href="#ch5">5 · Trois routines à adopter</a></li>
    </ol>
</nav>

{{-- ═════════════ CH 1 ═════════════ --}}
<h2 class="chapter" id="ch1"><span class="chapter-num">Chapitre 1</span>Avant de commencer</h2>
<p class="chapter-lead">Ce guide vous accompagne du tout premier clic à la maîtrise complète du module Achats &amp; Moyens Généraux. Aucune connaissance informatique préalable n'est nécessaire.</p>

<h3>Ce que vous allez apprendre</h3>
<p>À la fin de ce manuel, vous serez capable de <strong>configurer</strong> votre espace de travail, de <strong>traiter les demandes internes</strong> qui vous parviennent, de <strong>passer des commandes</strong> à vos fournisseurs, de <strong>suivre vos stocks</strong> et vos engagements contractuels, et de <strong>gérer les dysfonctionnements</strong> signalés dans les locaux.</p>

<h3>Comment utiliser ce guide</h3>
<p>Chaque chapitre est indépendant. Vous pouvez le lire dans l'ordre pour une prise en main complète, ou aller directement au chapitre qui traite de la tâche que vous avez à faire aujourd'hui. Quatre types d'encarts jalonnent le texte :</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Un raccourci ou une meilleure façon de faire qui vous fera gagner du temps.
</div>
<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Un point de vigilance — une action qui ne peut pas être annulée facilement, ou une erreur fréquente à éviter.
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
    <li>Vos identifiants personnels : une adresse e-mail et un mot de passe.</li>
    <li>Votre rôle défini au niveau <em>admin</em> ou disposant des permissions « update:produit », « update:commande » ou « update:dysfonctionnement ».</li>
</ul>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Si vous n'avez pas encore vos identifiants, contactez votre <strong>super-administrateur</strong>. C'est la personne qui gère les comptes utilisateurs dans OptimiZe.
</div>

{{-- ═════════════ CH 2 ═════════════ --}}
<h2 class="chapter" id="ch2"><span class="chapter-num">Chapitre 2</span>Se connecter et arriver dans votre espace</h2>
<p class="chapter-lead">Trois écrans à traverser : la page de connexion, le sélecteur d'espace de travail, puis le tableau de bord — votre poste de pilotage.</p>

<h3>Étape 1 · La page de connexion</h3>
<p>Ouvrez votre navigateur et saisissez l'adresse de votre installation OptimiZe. Vous arrivez sur cet écran :</p>

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
                            <div class="mock-input">responsable.achats@entreprise.com</div>
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
        <tr><td class="num"><span>1</span></td><td>Saisissez votre <strong>adresse e-mail</strong> professionnelle, celle communiquée par votre administrateur.</td></tr>
        <tr><td class="num"><span>2</span></td><td>Saisissez votre <strong>mot de passe</strong>. Si vous vous connectez pour la première fois, il vous sera demandé de le changer.</td></tr>
        <tr><td class="num"><span>3</span></td><td>Cliquez sur <span class="btn-ref">SE CONNECTER</span>. Si tout est correct, vous passez à l'écran suivant.</td></tr>
    </table>
</figure>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Après <strong>5 essais infructueux</strong>, votre compte peut être temporairement bloqué. En cas de doute sur votre mot de passe, utilisez « Mot de passe oublié ? » plutôt que de multiplier les tentatives.
</div>

<h3>Étape 2 · Choisir votre espace de travail</h3>
<p>Dès la connexion réussie, une <strong>fenêtre de sélection</strong> apparaît par-dessus la page d'accueil. Elle vous demande de choisir <em>où vous voulez travailler aujourd'hui</em>. Ce choix est obligatoire — vous ne pouvez pas la fermer sans faire une sélection.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-picker">
            <div class="mock-picker-title">Bienvenue, Marie</div>
            <div class="mock-picker-sub">Choisissez votre espace de travail — ce choix est requis pour continuer.</div>

            <div class="mock-picker-section">Espaces principaux</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Intranet</div><div class="mock-card-desc">Portail collaboratif, actualités, agenda</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Réseau Social</div><div class="mock-card-desc">Fil de publications, groupes</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Guide &amp; Docs</div><div class="mock-card-desc">Manuels de prise en main</div></div></td>
                </tr>
            </table>

            <div class="mock-picker-section">Modules métier</div>
            <table class="mock-grid">
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Finance</div><div class="mock-card-desc">Compta, budget, factures</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">GRH &amp; Paie</div><div class="mock-card-desc">Employés, absences, paie</div></div></td>
                    <td><div class="mock-card mock-card-featured"><div class="mock-card-name">Achats &amp; MG →</div><div class="mock-card-desc">Commandes, stocks, interventions</div></div></td>
                </tr>
                <tr>
                    <td><div class="mock-card"><div class="mock-card-name">Gestion Projet</div><div class="mock-card-desc">Projets, phases, tâches</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Stratégie</div><div class="mock-card-desc">Objectifs, KPI, pilotage</div></div></td>
                    <td><div class="mock-card"><div class="mock-card-name">Mon profil</div><div class="mock-card-desc">Ma fiche, préférences</div></div></td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Sélecteur d'espace de travail</strong> · Cliquez sur la carte encadrée en orange (« Achats &amp; MG ») pour aller directement dans votre module.</figcaption>
</figure>

<p>Cliquez sur la carte <strong>Achats &amp; MG</strong>. La fenêtre se referme et vous êtes redirigé vers votre tableau de bord.</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Vous pouvez ré-ouvrir ce sélecteur à tout moment en cliquant sur l'icône <strong>grille</strong> en haut à droite de l'écran. Utile quand vous devez passer temporairement dans un autre espace, par exemple pour consulter le module Finance ou lire un autre guide.
</div>

<h3>Étape 3 · Découvrir votre tableau de bord</h3>
<p>Le tableau de bord est <strong>votre poste de pilotage</strong>. Il vous montre, dès l'ouverture, tout ce qui demande votre attention aujourd'hui. Ses six zones principales :</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-dash">
            <div class="mock-dash-hd">
                <table>
                    <tr>
                        <td>
                            <div class="title">Achats &amp; Moyens Généraux</div>
                            <div class="sub">Pilotage — vendredi 28 août 2026</div>
                        </td>
                        <td style="text-align:right;"><span class="cta">+ Nouvelle commande</span></td>
                    </tr>
                </table>
            </div>

            <table class="mock-alerts">
                <tr>
                    <td><div class="mock-alert">
                        <div class="mock-alert-t">3 ruptures de stock</div>
                        <div class="mock-alert-s">Réapprovisionnement urgent</div>
                    </div></td>
                    <td><div class="mock-alert warn">
                        <div class="mock-alert-t">2 demandes en retard</div>
                        <div class="mock-alert-s">En attente depuis &gt; 3 jours</div>
                    </div></td>
                    <td><div class="mock-alert warn">
                        <div class="mock-alert-t">1 engagement à renouveler</div>
                        <div class="mock-alert-s">Expiration &lt; 60 jours</div>
                    </div></td>
                </tr>
            </table>

            <table class="mock-kpis">
                <tr>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Engagé ce mois</div>
                        <div class="mock-kpi-v">1 250 <small>K XAF</small></div>
                        <span class="mock-kpi-t">↑ 12%</span>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Commandes ce mois</div>
                        <div class="mock-kpi-v">18</div>
                        <div class="mock-kpi-s">6 en cours</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Délai livraison</div>
                        <div class="mock-kpi-v">4,2 <small>j moy.</small></div>
                        <div class="mock-kpi-s">Sur 90 jours</div>
                    </div></td>
                    <td><div class="mock-kpi">
                        <div class="mock-kpi-l">Valorisation stock</div>
                        <div class="mock-kpi-v">8 430 <small>K XAF</small></div>
                        <div class="mock-kpi-s">97 articles</div>
                    </div></td>
                </tr>
            </table>

            <table class="mock-charts">
                <tr>
                    <td>
                        <div class="mock-chart">
                            <div class="mock-chart-t">Dépenses mensuelles (6 mois)</div>
                            <table class="mock-chart-bars">
                                <tr style="height:80px;">
                                    <td><div class="mock-bar" style="height:30px;"></div></td>
                                    <td><div class="mock-bar" style="height:45px;"></div></td>
                                    <td><div class="mock-bar" style="height:55px;"></div></td>
                                    <td><div class="mock-bar" style="height:40px;"></div></td>
                                    <td><div class="mock-bar" style="height:65px;"></div></td>
                                    <td><div class="mock-bar mock-bar-cur" style="height:75px;"></div></td>
                                </tr>
                                <tr>
                                    <td><div class="mock-bar-lbl">mar</div></td>
                                    <td><div class="mock-bar-lbl">avr</div></td>
                                    <td><div class="mock-bar-lbl">mai</div></td>
                                    <td><div class="mock-bar-lbl">juin</div></td>
                                    <td><div class="mock-bar-lbl">juil</div></td>
                                    <td><div class="mock-bar-lbl">août</div></td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td>
                        <div class="mock-chart">
                            <div class="mock-chart-t">Top fournisseurs (12 mois)</div>
                            <table class="mock-top">
                                <tr>
                                    <td class="rank">1</td>
                                    <td><strong>SOTRAF Industries</strong></td>
                                    <td class="amt">2 400 K</td>
                                </tr>
                                <tr>
                                    <td class="rank">2</td>
                                    <td><strong>Alpha Distribution</strong></td>
                                    <td class="amt">1 850 K</td>
                                </tr>
                                <tr>
                                    <td class="rank">3</td>
                                    <td><strong>Ets Kouassi &amp; Fils</strong></td>
                                    <td class="amt">920 K</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Tableau de bord Achats &amp; MG</strong> · Six zones à connaître.</figcaption>
    <table class="screen-annots">
        <tr><td class="num"><span>1</span></td><td><strong>Barre supérieure</strong> — titre du module et trois boutons d'action rapide (Nouvelle commande, Demande interne, Signaler un dysfonctionnement).</td></tr>
        <tr><td class="num"><span>2</span></td><td><strong>Menu utilisateur</strong> (haut à droite) — votre profil, l'accès au sélecteur d'espace (icône grille), les notifications.</td></tr>
        <tr><td class="num"><span>3</span></td><td><strong>Bandeau de priorités</strong> — cartes cliquables affichées uniquement quand il y a quelque chose à traiter aujourd'hui.</td></tr>
        <tr><td class="num"><span>4</span></td><td><strong>Indicateurs-clés (KPI)</strong> — montant engagé ce mois avec évolution vs mois dernier, nombre de commandes, délai moyen, valeur du stock.</td></tr>
        <tr><td class="num"><span>5</span></td><td><strong>Graphiques</strong> — évolution des dépenses sur 6 mois et classement des 5 principaux fournisseurs.</td></tr>
        <tr><td class="num"><span>6</span></td><td><strong>Santé du stock et engagements</strong> — répartition visuelle des articles et rappel des contrats arrivant à échéance.</td></tr>
    </table>
</figure>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Le tableau de bord se rafraîchit à chaque visite. Toutes les données sont <strong>calculées en direct</strong> à partir de la base — ce que vous voyez est la vérité du moment, pas une photo d'hier.
</div>

<h3>L'alerte de rupture — une notification automatique</h3>
<p>Tant qu'il existe des articles en rupture ou sous le seuil d'alerte, un <strong>pop-up rouge</strong> s'affiche automatiquement toutes les deux minutes, où que vous soyez dans l'espace Achats &amp; MG. C'est le système qui vous force à ne pas oublier de réapprovisionner.</p>

<figure class="screen">
    <div class="screen-frame">
        <div class="mock mock-popup">
            <div class="mock-popup-hd">
                <div class="t">Alerte rupture — 3 article(s)</div>
                <div class="s">Vérification automatique toutes les 2 minutes · Vérifié à 14:32:15</div>
            </div>
            <table class="mock-popup-body">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th style="text-align:right;">Stock actuel</th>
                        <th style="text-align:right;">Seuil</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Cartouche encre HP 305 noire</td>
                        <td class="qty"><strong>0</strong> unité <span class="badge-rupt">RUPTURE</span></td>
                        <td class="qty">5</td>
                        <td style="text-align:right;"><span class="btn-reappro">↑ Réappro.</span></td>
                    </tr>
                    <tr>
                        <td>Ramette papier A4 80g</td>
                        <td class="qty"><strong>2</strong> rame</td>
                        <td class="qty">10</td>
                        <td style="text-align:right;"><span class="btn-reappro">↑ Réappro.</span></td>
                    </tr>
                    <tr>
                        <td>Détergent nettoyage sol 5L</td>
                        <td class="qty"><strong>1</strong> bidon</td>
                        <td class="qty">3</td>
                        <td style="text-align:right;"><span class="btn-reappro">↑ Réappro.</span></td>
                    </tr>
                </tbody>
            </table>
            <div class="mock-popup-ft">
                <span class="btn-cmd">🛒 Créer une commande fournisseur</span>
            </div>
        </div>
    </div>
    <figcaption class="screen-caption"><strong>Pop-up d'alerte rupture</strong> · Trois façons de réagir : réappro. article par article, commande groupée, ou fermer temporairement.</figcaption>
</figure>

<p>Sur cette fenêtre, trois façons de réagir :</p>
<ul class="checklist">
    <li>Cliquer <strong>Réappro.</strong> à côté d'un article pour aller ajuster son stock manuellement.</li>
    <li>Cliquer <strong>Créer une commande fournisseur</strong> pour passer une commande couvrant tous les articles listés.</li>
    <li>Cliquer <strong>Fermer</strong> — l'alerte réapparaîtra dans 2 minutes tant que la situation n'est pas résolue.</li>
</ul>

{{-- ═════════════ CH 3 ═════════════ --}}
<h2 class="chapter" id="ch3"><span class="chapter-num">Chapitre 3</span>Configurer le référentiel</h2>
<p class="chapter-lead">Avant votre première commande, prenez une heure pour renseigner les listes de base. Ce travail se fait <em>une seule fois</em>, mais il conditionne toute votre utilisation quotidienne.</p>

<h3>Pourquoi commencer par le référentiel ?</h3>
<p>Un référentiel, c'est l'ensemble des <strong>listes de choix</strong> qui vous seront proposées quand vous remplirez un formulaire. Quand vous créerez une commande, il vous faudra choisir un fournisseur — s'il n'existe pas dans votre annuaire, vous ne pourrez pas le sélectionner. Quand vous enregistrerez un dysfonctionnement, il vous faudra choisir sa nature — sans nature préalablement définie, pas de choix possible.</p>
<p>L'ordre logique est le suivant : d'abord les <em>éléments de base</em> (fournisseurs, catégories), puis les <em>éléments de contexte</em> (familles de dysfonctionnement, natures d'intervention), puis les <em>documents opérationnels</em> (contrats, commandes, tickets).</p>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Toutes les pages de configuration se trouvent dans la section <strong>Référentiels</strong> du menu latéral, en bas. Cliquez sur l'icône engrenage, puis dépliez « Référentiels ».
</div>

<h3>3.1 · Créer vos fournisseurs</h3>
<p>Les fournisseurs sont gérés dans l'annuaire général des organisations, filtré par type « fournisseur ». Vous y accédez via le menu <strong>Fournisseurs</strong>.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la liste des fournisseurs</div>
            <p>Dans le menu latéral, cliquez sur <strong>Achats fournisseurs → Fournisseurs</strong>. Vous voyez la liste des organisations déjà enregistrées.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer un nouveau fournisseur</div>
            <p>Cliquez sur le bouton <span class="btn-ref">+ Nouveau fournisseur</span> en haut à droite. Un formulaire s'ouvre.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Remplir les informations essentielles</div>
            <p>Les champs marqués d'un astérisque rouge sont obligatoires :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Raison sociale</strong> — le nom légal de l'entreprise (ex. « SOTRAF Industries SARL »).</li>
                <li><strong>Type</strong> — sélectionnez « fournisseur » dans la liste déroulante.</li>
                <li><strong>NIF / RCCM</strong> — les identifiants fiscaux, si vous les possédez.</li>
                <li><strong>Contact principal</strong> — nom, téléphone, e-mail de votre interlocuteur habituel.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Enregistrer</div>
            <p>Cliquez <span class="btn-ref">Créer</span> en bas. Le fournisseur est immédiatement disponible dans toutes les listes de sélection.</p>
        </td>
    </tr>
</table>

<div class="callout callout-tip">
    <span class="callout-label">Astuce</span>
    Vous pouvez enrichir un fournisseur au fil de l'eau — ajouter un logo, des documents contractuels, un secteur d'activité. Rien n'oblige à tout remplir dès la création.
</div>

<h3>3.2 · Créer votre catalogue d'articles</h3>
<p>Chaque produit ou consommable que vous gérez doit exister dans le catalogue. C'est lui qui alimentera les lignes de commande et le suivi de stock.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir le catalogue</div>
            <p>Menu latéral : <strong>Référentiels → Catalogue produits</strong>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Ajouter un article</div>
            <p>Cliquez <span class="btn-ref">+ Nouvel article</span>.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Décrire l'article</div>
            <p>Renseignez la <strong>désignation</strong> (nom clair : « Ramette papier A4 80g blanc »), la <strong>catégorie</strong>, l'<strong>unité de mesure</strong> (unité, litre, kg, rame, boîte…) et le <strong>prix unitaire</strong> indicatif.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Cocher « Article stockable » si nécessaire</div>
            <p>Si l'article est un consommable physique que vous stockez, cochez la case et renseignez :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Stock actuel</strong> — quantité disponible aujourd'hui.</li>
                <li><strong>Stock minimum</strong> — en dessous, vous devez ré-approvisionner.</li>
                <li><strong>Seuil d'alerte</strong> — le niveau qui déclenche le pop-up rouge.</li>
            </ul>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Un article <strong>non-stockable</strong> (une prestation de service, par exemple) ne déclenchera jamais d'alerte de rupture, même s'il apparaît dans une commande. C'est normal.
</div>

<h3>3.3 · Familles et types de dysfonctionnement</h3>
<p>Quand un utilisateur signale un problème (climatisation en panne, fuite d'eau, ampoule grillée), il doit pouvoir en préciser la nature. Ces choix sont issus de <strong>listes que vous configurez ici</strong>.</p>

<p>La logique est en <strong>deux niveaux</strong> :</p>
<table class="data">
    <thead><tr><th>Niveau</th><th>Rôle</th><th>Exemple</th></tr></thead>
    <tbody>
        <tr><td>Famille</td><td>Grande catégorie de problème</td><td>Électricité</td></tr>
        <tr><td>Type</td><td>Précision au sein d'une famille</td><td>Ampoule grillée, Coupure secteur, Prise défaillante</td></tr>
    </tbody>
</table>

<p>Commencez par créer les <strong>familles</strong> dans <em>Référentiels → Familles de dysfonctionnement</em>, puis créez les <strong>types</strong> dans <em>Référentiels → Types de dysfonctionnement</em> en rattachant chaque type à sa famille.</p>

<h3>3.4 · Natures d'intervention</h3>
<p>Une intervention, c'est ce qu'on fait <em>en réaction</em> à un dysfonctionnement (dépannage, remplacement, contrôle préventif…). Ces natures doivent être définies pour pouvoir catégoriser vos interventions.</p>
<p>Rendez-vous dans <em>Référentiels → Natures d'intervention</em> et créez au minimum :</p>
<ul class="checklist">
    <li><strong>Dépannage</strong> — intervention urgente pour rétablir un service.</li>
    <li><strong>Maintenance préventive</strong> — visite planifiée pour éviter la panne.</li>
    <li><strong>Remplacement</strong> — pièce ou équipement changé à neuf.</li>
    <li><strong>Contrôle réglementaire</strong> — vérification obligatoire (extincteurs, ascenseurs…).</li>
</ul>

<h3>3.5 · Types d'engagement et fréquences de paiement</h3>
<p>Les <strong>engagements fournisseur</strong> (aussi appelés contrats) suivent les prestations récurrentes : nettoyage, gardiennage, licences logicielles, maintenance… Deux listes conditionnent leur création :</p>

<table class="flow">
    <tr>
        <td><div class="flow-num">ÉTAPE 1</div><div class="flow-name">Types d'engagement</div><div class="flow-who">Achat, prestation, maintenance, licence…</div></td>
        <td><div class="flow-num">ÉTAPE 2</div><div class="flow-name">Fréquences de paiement</div><div class="flow-who">Mensuel, trimestriel, annuel…</div></td>
        <td><div class="flow-num">ÉTAPE 3</div><div class="flow-name">Vos engagements</div><div class="flow-who">Créés par vous, catégorisés grâce à 1 et 2</div></td>
    </tr>
</table>

<p>Les deux listes se configurent dans <em>Référentiels → Types d'engagement</em> et <em>Référentiels → Fréquences de paiement</em>. Pour les fréquences, un champ important est le <strong>nombre de mois entre deux échéances</strong> : c'est ce qui permet au système de calculer la prochaine date d'échéance de paiement.</p>

<table class="data">
    <thead><tr><th>Code</th><th>Libellé</th><th>Mois entre échéances</th></tr></thead>
    <tbody>
        <tr><td><code>ponctuel</code></td><td>Ponctuel (paiement unique)</td><td>—</td></tr>
        <tr><td><code>mensuel</code></td><td>Mensuel</td><td>1</td></tr>
        <tr><td><code>trimestriel</code></td><td>Trimestriel</td><td>3</td></tr>
        <tr><td><code>semestriel</code></td><td>Semestriel</td><td>6</td></tr>
        <tr><td><code>annuel</code></td><td>Annuel</td><td>12</td></tr>
    </tbody>
</table>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Une fois votre référentiel configuré, quand vous créerez votre premier engagement, tous les menus déroulants vous proposeront des choix cohérents et pré-remplis. Aucune saisie libre — donc pas de doublons, pas de fautes de frappe.
</div>

<hr class="divider">

{{-- ═════════════ CH 4 ═════════════ --}}
<h2 class="chapter" id="ch4"><span class="chapter-num">Chapitre 4</span>Vos tâches quotidiennes, une par une</h2>
<p class="chapter-lead">Six workflows couvrent 90 % de votre travail dans le module. Ce chapitre les détaille chacun, dans l'ordre où vous les rencontrerez.</p>

<h3>4.1 · Traiter une demande interne</h3>
<p>Une demande interne, c'est une <strong>demande d'achat émise par un collaborateur</strong> à travers OptimiZe. Elle suit un circuit de validation avant d'arriver chez vous :</p>

<table class="flow">
    <tr>
        <td><div class="flow-num">1</div><div class="flow-name">Émission</div><div class="flow-who">Le collaborateur</div></td>
        <td><div class="flow-num">2</div><div class="flow-name">Validation N+1</div><div class="flow-who">Son responsable</div></td>
        <td><div class="flow-num">3</div><div class="flow-name">Transmission Appro</div><div class="flow-who">Vous — traitement</div></td>
        <td><div class="flow-num">4</div><div class="flow-name">Livraison</div><div class="flow-who">Vous — validation</div></td>
    </tr>
</table>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la liste des demandes internes</div>
            <p>Menu <strong>Demandes internes</strong>. Les demandes qui vous concernent apparaissent en tête, avec un badge coloré selon leur statut.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir une demande</div>
            <p>Cliquez sur son numéro (ex. <code>DI-2026-0142</code>). Vous voyez le détail : demandeur, articles souhaités, quantités, justification.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Décider — trois options</div>
            <p>Selon la situation, cliquez sur l'un des trois boutons en haut de la fiche :</p>
            <ul style="list-style:none; padding:0; margin:.5rem 0;">
                <li style="padding:.35rem 0;"><span class="btn-ref">Approuver &amp; commander</span> — vous validez et enclenchez une commande fournisseur.</li>
                <li style="padding:.35rem 0;"><span class="btn-ref btn-neutral">Livrer depuis stock</span> — l'article est déjà disponible, vous le sortez.</li>
                <li style="padding:.35rem 0;"><span class="btn-ref btn-danger">Refuser</span> — vous devez indiquer un motif.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Confirmer la livraison</div>
            <p>Une fois les articles remis au demandeur, revenez sur la fiche et cliquez <span class="btn-ref">Marquer livrée</span>. La demande passe en statut « Livrée ». Si vous n'avez livré qu'une partie, utilisez <span class="btn-ref btn-neutral">Livraison partielle</span>.</p>
        </td>
    </tr>
</table>

<h3>4.2 · Créer une commande fournisseur</h3>
<p>Que ce soit pour ré-approvisionner un stock en rupture ou pour répondre à une demande interne validée, la procédure est la même.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Cliquer « Nouvelle commande »</div>
            <p>Depuis le tableau de bord, bouton <span class="btn-ref">+ Nouvelle commande</span> en haut à droite.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Choisir le fournisseur</div>
            <p>Utilisez le menu déroulant. Vous pouvez taper les premières lettres pour filtrer. Si le fournisseur n'existe pas encore, créez-le d'abord (voir §3.1).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Ajouter les lignes d'articles</div>
            <p>Cliquez <span class="btn-ref">+ Ajouter une ligne</span> pour chaque article. Sélectionnez l'article dans le catalogue, la quantité, le prix unitaire (le prix du catalogue est proposé par défaut — vous pouvez l'ajuster pour cette commande).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Vérifier les montants</div>
            <p>Le montant HT, la TVA et le montant TTC se calculent automatiquement. Vérifiez qu'ils correspondent au devis reçu du fournisseur.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Enregistrer puis soumettre</div>
            <p>Cliquez <span class="btn-ref">Enregistrer</span> pour créer la commande en <em>brouillon</em>. Puis, quand elle est prête à partir, cliquez <span class="btn-ref">Soumettre</span> — elle passe au statut « Soumise » et entre dans le circuit d'approbation.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>6</span></td>
        <td class="step-body">
            <div class="step-title">Réceptionner à la livraison</div>
            <p>Quand la livraison arrive, ouvrez la commande, cliquez <span class="btn-ref">Marquer livrée</span> ou <span class="btn-ref btn-neutral">Livraison partielle</span>. <strong>Le stock est mis à jour automatiquement</strong>.</p>
        </td>
    </tr>
</table>

<div class="callout callout-warn">
    <span class="callout-label">Attention</span>
    Une commande <strong>annulée</strong> ne peut plus être ré-activée. Si vous vous êtes trompé, préférez <em>modifier</em> une commande en brouillon plutôt que l'annuler et en recréer une.
</div>

<h3>4.3 · Enregistrer un engagement fournisseur</h3>
<p>Pour les prestations récurrentes (nettoyage, sécurité, licences, maintenance), il faut créer un <strong>engagement</strong>. Cela permet de suivre les échéances de paiement et d'être alerté avant l'expiration.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir la liste des engagements</div>
            <p>Menu <strong>Engagements fournisseur</strong>. Vous voyez la liste des contrats actifs et ceux qui approchent de leur échéance.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer un nouvel engagement</div>
            <p>Cliquez <span class="btn-ref">+ Nouvel engagement</span>. Remplissez :</p>
            <ul class="checklist" style="margin-top:.4rem;">
                <li><strong>Fournisseur</strong>, <strong>type d'engagement</strong> (via référentiel), <strong>objet</strong> (« Nettoyage bureaux siège »).</li>
                <li><strong>Date de début</strong>, <strong>date de fin</strong> (si connue).</li>
                <li><strong>Montant HT et TTC</strong>, <strong>devise</strong>.</li>
                <li><strong>Fréquence de paiement</strong> (via référentiel), <strong>montant par paiement</strong>, <strong>jour du mois</strong>.</li>
                <li><strong>Préavis de résiliation</strong> (en jours) et cochez <strong>Renouvellement automatique</strong> si applicable.</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Activer l'engagement</div>
            <p>Une fois créé, il est en statut « Brouillon ». Cliquez <span class="btn-ref">Activer</span> pour le rendre effectif. Le système calculera automatiquement les prochaines échéances de paiement.</p>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Un engagement dont la date d'expiration est à moins de <strong>60 jours</strong> apparaît automatiquement dans la barre de priorités de votre tableau de bord. Vous n'avez pas besoin de mettre un rappel manuel.
</div>

<h4>Ajouter un avenant</h4>
<p>Si les conditions changent en cours de contrat (nouvelle prestation ajoutée, prolongation, révision tarifaire), utilisez la fonction <strong>Avenant</strong> plutôt que de modifier le contrat original. L'historique est conservé.</p>
<p>Sur la fiche de l'engagement, onglet <em>Avenants</em>, cliquez <span class="btn-ref">+ Nouvel avenant</span>. Précisez l'objet, l'impact financier (variation du montant HT), la nouvelle date de fin si elle change.</p>

<h4>Renouveler ou résilier</h4>
<p>À l'approche de l'échéance, deux actions possibles :</p>
<ul class="checklist">
    <li><span class="btn-ref">Renouveler</span> — crée un nouveau contrat « enfant » à la suite ; l'ancien passe en statut « Renouvelé ». L'historique reste consultable.</li>
    <li><span class="btn-ref btn-danger">Résilier</span> — mettre fin au contrat en indiquant un motif. Aucun renouvellement.</li>
</ul>

<h3>4.4 · Enregistrer une immobilisation</h3>
<p>Une immobilisation, c'est un <strong>bien durable</strong> qui reste dans le patrimoine de l'entreprise : véhicule, mobilier, matériel informatique, équipement technique.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Menu Immobilisations</div>
            <p>Cliquez sur <strong>Immobilisations</strong> dans la section Moyens Généraux du menu latéral.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Créer la fiche</div>
            <p>Bouton <span class="btn-ref">+ Nouvelle immobilisation</span>. Renseignez : désignation, catégorie, numéro d'inventaire, date d'acquisition, valeur d'achat, durée d'amortissement, emplacement, personne affectataire.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Attacher un justificatif</div>
            <p>Dans la section « Pièces jointes », glissez la facture d'achat, le bon de livraison ou la photo du bien. C'est votre traçabilité comptable.</p>
        </td>
    </tr>
</table>

<h3>4.5 · Traiter un dysfonctionnement signalé (Action Interne)</h3>
<p>Quand un collaborateur signale un problème dans les locaux (« la climatisation ne fonctionne plus »), une fiche <strong>Dysfonctionnement</strong> se crée. Elle apparaît dans votre menu <strong>Action Interne</strong>.</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Ouvrir le ticket</div>
            <p>Sur le tableau de bord, cliquez sur l'alerte « X ticket(s) à prendre en charge » — vous atterrissez directement sur la liste filtrée.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Analyser</div>
            <p>Lisez la description, regardez les photos jointes, identifiez la famille et le type via les listes déroulantes que vous avez configurées au §3.3.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Assigner le ticket</div>
            <p>Cliquez <span class="btn-ref">Assigner</span>. Vous pouvez assigner à un <strong>utilisateur</strong> spécifique, à un <strong>service</strong> entier, ou à une <strong>entité</strong>. L'assignation est <strong>multiple</strong> — plusieurs personnes peuvent être responsables en même temps.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>4</span></td>
        <td class="step-body">
            <div class="step-title">Passer en « Pris en charge »</div>
            <p>Cliquez <span class="btn-ref">Prendre en charge</span>. Le statut passe et l'émetteur du signalement est notifié — il sait que son problème est en cours de traitement.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>5</span></td>
        <td class="step-body">
            <div class="step-title">Créer une intervention si nécessaire</div>
            <p>Depuis le ticket, cliquez <span class="btn-ref">+ Planifier une intervention</span>. Cela crée automatiquement une intervention liée au ticket (voir §4.6).</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>6</span></td>
        <td class="step-body">
            <div class="step-title">Marquer résolu</div>
            <p>Une fois le problème réglé, cliquez <span class="btn-ref">Résoudre</span>. <strong>L'émetteur du ticket reçoit une notification et doit confirmer la résolution</strong> : c'est lui qui a le dernier mot. Si le problème persiste, il peut refuser la clôture — le ticket rouvre.</p>
        </td>
    </tr>
</table>

<div class="callout callout-info">
    <span class="callout-label">Le saviez-vous ?</span>
    Cette validation par l'émetteur est une garantie qualité : on ne peut pas clore un ticket sans l'accord de la personne qui l'a signalé. Ça évite les fausses clôtures « pour faire descendre les chiffres ».
</div>

<h3>4.6 · Planifier et clôturer une intervention</h3>
<p>Une intervention est l'<strong>action concrète</strong> qui va résoudre le dysfonctionnement (visite du plombier, remplacement de l'ampoule, contrôle de la clim).</p>

<table class="steps">
    <tr>
        <td class="step-num"><span>1</span></td>
        <td class="step-body">
            <div class="step-title">Planifier l'intervention</div>
            <p>Depuis un dysfonctionnement ou depuis le menu <strong>Interventions</strong>, cliquez <span class="btn-ref">+ Nouvelle intervention</span>. Renseignez : nature (via référentiel §3.4), date planifiée, prestataire ou intervenant interne, description du travail à faire.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>2</span></td>
        <td class="step-body">
            <div class="step-title">Passer en « En cours »</div>
            <p>Le jour de l'intervention, cliquez <span class="btn-ref">Démarrer</span>. Notez à cet instant les observations utiles.</p>
        </td>
    </tr>
    <tr>
        <td class="step-num"><span>3</span></td>
        <td class="step-body">
            <div class="step-title">Clôturer</div>
            <p>Une fois terminé, cliquez <span class="btn-ref">Terminer</span>. Vous renseignez : date effective de fin, coût réel, pièces changées si applicable, éventuel rapport d'intervention.</p>
        </td>
    </tr>
</table>

<hr class="divider">

{{-- ═════════════ CH 5 ═════════════ --}}
<h2 class="chapter" id="ch5"><span class="chapter-num">Chapitre 5</span>Trois routines à adopter</h2>
<p class="chapter-lead">Une bonne utilisation d'OptimiZe tient en trois habitudes. Adoptez-les et le module travaille pour vous, pas contre vous.</p>

<h3>Chaque matin — 5 minutes</h3>
<ul class="checklist">
    <li>Ouvrir le tableau de bord Achats &amp; MG.</li>
    <li>Regarder la barre de priorités en haut. Traiter d'abord les ruptures et les demandes en retard.</li>
    <li>Vérifier les nouveaux dysfonctionnements signalés depuis la veille et les assigner.</li>
    <li>Consulter les commandes en cours qui devaient être livrées aujourd'hui.</li>
</ul>

<h3>Chaque semaine — 30 minutes (lundi matin)</h3>
<ul class="checklist">
    <li>Passer en revue les <strong>engagements qui expirent dans les 60 jours</strong>. Décider : renouveler, résilier, ou négocier une nouvelle offre.</li>
    <li>Contrôler les <strong>livraisons partielles</strong> — relancer les fournisseurs qui n'ont pas complété.</li>
    <li>Vérifier le <strong>délai moyen de livraison</strong> — s'il se dégrade, identifier le ou les fournisseurs concernés.</li>
    <li>Faire un point sur les <strong>dysfonctionnements ouverts depuis plus de 7 jours</strong>. Escalader si nécessaire.</li>
</ul>

<h3>Chaque mois — 1 heure (premier lundi)</h3>
<ul class="checklist">
    <li>Analyser le graphique <strong>Dépenses mensuelles</strong> et le classement <strong>Top fournisseurs</strong>. Détecter des dérives.</li>
    <li>Faire l'inventaire physique d'un échantillon d'articles stockés et corriger les écarts éventuels.</li>
    <li>Mettre à jour le catalogue : nouveaux prix, nouveaux articles, articles obsolètes à désactiver.</li>
    <li>Vérifier les <strong>immobilisations</strong> ajoutées durant le mois — chaque nouveau bien doit avoir une facture jointe.</li>
</ul>

<div class="callout callout-ok">
    <span class="callout-label">Résultat attendu</span>
    Après un mois d'application de ces trois routines, votre tableau de bord ne devrait presque plus jamais afficher d'alertes rouges. Vous ne subissez plus les urgences — vous les anticipez.
</div>
