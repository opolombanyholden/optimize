# Brochure commerciale OptimiZe — 2026

`OptimiZe-Brochure-Commerciale-2026.pdf` — 6 pages A4 (210 × 297 mm), polices Inter embarquées,
captures réelles de l'application. Prêt à envoyer par email ou à imprimer.

## Sommaire

| Page | Contenu |
|---|---|
| 1 | Couverture — promesse, 4 chiffres clés, capture du portail, les 4 cibles |
| 2 | Le constat (4 douleurs) → la réponse OptimiZe (4 principes) → ce que la centralisation change |
| 3 | Les 7 sous-ensembles, chacun à sa couleur de charte + Administration & Sécurité |
| 4 | Les 4 cibles : TPE · PME · Grande Entreprise · Institution Publique |
| 5 | Tarification modulaire : socle + modules, dégressifs volume et engagement, 3 cas calculés |
| 6 | Les 3 modes d'acquisition, les 15 jours d'essai, FAQ, contact |

## Grille tarifaire retenue (XAF HT)

- **Socle OptimiZe** (obligatoire) : 6 000 / utilisateur / mois
- **Chaque module ERP** : + 2 500 / utilisateur / mois
- **Pack Intégral** (les 6 modules) : + 10 000 au lieu de 15 000 → −33 %
- **Dégressif volume** : 1–10 = 0 % · 11–50 = −20 % · 51–150 = −35 % · 151–500 = −50 % · 500+ = devis
- **Engagement** : mensuel 0 % · trimestriel −5 % · annuel −15 % (cumulable avec le volume)
- **Licence perpétuelle** : 30 × la mensualité équivalente
- **Cession avec droits de propriété** : à partir de 65 000 000

Repère marché : Odoo Online ≈ 16 000 XAF/utilisateur/mois. La grille positionne OptimiZe
en challenger crédible, sous les éditeurs internationaux.

## À compléter avant diffusion

Trois emplacements sont en attente dans le bloc contact de la page 6 (`brochure.html`,
classe `.coords`, marqués `class="ph"`) :

- téléphone — `+241 __ __ __ __`
- site web — `www.___________`
- adresse précise (actuellement « Libreville, Gabon »)

L'email `contact@yubile-tech.com` a été repris de `resources/views/vitrine/home.blade.php`.

## Régénérer le PDF

```bash
cd docs/commercial/source
node build-pdf.mjs        # → OptimiZe-Brochure.pdf + aperçus PNG par page
```

Le script ouvre `brochure.html` dans le Chromium de Playwright, contrôle qu'aucune page ne
déborde de son format A4, puis exporte le PDF. Adapter les chemins `EXEC` et `DIR` en tête du
script si l'environnement change.

## Charte respectée

Extraite de `public/css/vitrine.css` et `resources/views/layouts/partials/sidebar.blade.php`.

| Élément | Valeur |
|---|---|
| Police | Inter (400 → 900), embarquée en local dans `source/fonts/` |
| Primaire | `#0D9488` → `#0F766E` |
| Finance & Budget | `#4F46E5` → `#7C3AED` |
| RH & Paiement | `#059669` → `#0891B2` |
| Achats & Approvisionnement | `#D97706` → `#DC2626` |
| Moyens Généraux | `#0891B2` → `#22D3EE` |
| Projets & Tâches (PMP) | `#0D9488` → `#0F766E` |
| Objectifs & KPI | `#DB2777` → `#BE185D` |
| Intranet | `#7C3AED` → `#A855F7` |
| Administration | `#1E293B` → `#334155` |

## Captures

`source/shots/` contient les captures brutes (1500 × 940 CSS, ×2 DPI) prises sur
l'environnement local après exécution des seeders de démonstration
(`ProjetDemoSeeder`, `ErpDemoSeeder`, `FinanceDemoSeeder`, `AnnuaireSeeder`, `ObjectifsSeeder`).
Le recadrage est fait en CSS dans `brochure.html` (conteneur `.shot` + décalages sur l'`img`),
donc modifiable sans retoucher les PNG.

## Note technique

Les polices sont embarquées et sous-ensemblées en **Type 3**. C'est le comportement de Chrome
avec les `@font-face` locales. Le rendu et l'extraction de texte sont corrects dans tous les
lecteurs. Si un imprimeur exige du Type 1 / CID TrueType (workflow PDF/X), passer le fichier
par un distillage Acrobat ou installer Inter côté système de l'imprimeur.
