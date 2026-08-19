# Module RH & Paiement — Documentation technique

> OptimiZe ERP — Module Ressources Humaines & Paie
> Stack : Laravel 12 · PostgreSQL 17 · Bootstrap 5 · Spatie Permission · Spatie ActivityLog
> Version : 1.0.0 — 2026-05-22

---

## 1. Vue d'ensemble

Le module RH couvre **l'intégralité du cycle de vie du collaborateur**, de l'embauche au départ, conformément au cahier des charges OptimiZe (section 4).

### Périmètre fonctionnel (18 sous-modules)

| Bloc | Sous-modules |
|---|---|
| **Vue d'ensemble** | Dashboard RH consolidé · Audit log |
| **Administration du personnel** | Employés · Ayants-droit · Évènements de carrière |
| **Carrière & Compétences** | Compétences · Qualifications · Formations |
| **Temps & Activité** | Absences & Congés · Soldes de congés · Plannings · Missions |
| **Performance & Discipline** | Évaluations · Sanctions · Départs |
| **Paie** | Bulletins de paie · Rubriques paramétrables · Payements · Masse salariale |
| **Recrutement** | Campagnes · Profils · Postulants |

---

## 2. Architecture

### Stack & dépendances
- **PHP 8.3 / Laravel 12** — backend MVC
- **PostgreSQL 17** — strict typing (vs MySQL permissif). Migration depuis MySQL : 2026-05-08.
- **Spatie Permission 6** — RBAC (4 rôles : super-admin, admin, manager, user × 9 actions × 18 entités RH)
- **Spatie ActivityLog 4.12** — audit trail sur modèles sensibles
- **Bootstrap 5.3** + **Font Awesome 6.5** — UI
- **Pest 4** — tests (26 tests, 103 assertions, 0 régression)

### Structure de dossiers
```
app/
├── Http/
│   ├── Controllers/Rh/         (18 contrôleurs)
│   └── Requests/Rh/            (14 FormRequests)
├── Models/                     (16 modèles RH)
└── Services/Rh/
    ├── PaieCalculator.php      (moteur de paie à rubriques)
    └── ExpressionEvaluator.php (parser arithmétique sécurisé)

database/
├── migrations/                 (8 nouvelles tables)
└── seeders/RhRubriquesSeeder.php (catalogue Gabon CNSS/CNAMGS/IRPP)

resources/views/rh/             (18 dossiers de vues, ~70 fichiers Blade)
tests/Feature/RhTest.php        (26 tests Pest)
docs/RH_PAIE.md                 (cette documentation)
```

---

## 3. Schéma de données

### Tables principales

| Table | Rôle | Particularité |
|---|---|---|
| `employees` | Fiche collaborateur | `statut` int : 1=actif, 2=inactif, 3=parti. Soft delete. Logué (LogsActivity). |
| `affilies` | Ayants-droit (conjoint/enfants) | belongsTo employee |
| `competences` | Compétences employé | niveau : debutant/intermediaire/avance/expert |
| `qualifications` | Diplômes / certificats | belongsTo employee + organisme |
| `formations` | Formations suivies | statut 0/1/2 (planifié/cours/terminé) |
| `missions` | Missions professionnelles | budget + frais_reels |
| `evenementscarrieres` | Mobilité (promotion, mutation) | type + ancien/nouveau_poste |
| `absences` | Absences & congés | workflow validation (statut 0/1/2/3) |
| `conges_soldes` | Solde par employé/année/type | colonne `solde_disponible` GENERATED par PostgreSQL |
| `plannings` | Emploi du temps | unique(employee_id, date_jour) |
| `sanctions` | Sanctions disciplinaires | type, niveau_gravite. Logué. |
| `departs` | Sorties (démission/licenciement/retraite) | passe employee.statut=3 si finalisé. Logué. |
| `evaluations_performance` | Évaluations annuelles | competences_evaluees JSON, plan_developpement |
| `gpaies` | Bulletins de paie | brut, cotisations, irpp, net. Logué. |
| `rubriques` | Catalogue paramétrable | type gain/retenue/cotisation, base_calcul fixe/pourcentage/formule |
| `gpaie_rubrique` | Pivot bulletin↔rubrique | détail base/taux/montant appliqué |
| `payements` | Règlements de bulletins | mode virement/cheque/especes/mobile_money. Logué. |
| `payements_globals` | Agrégats masse salariale | unique(annee, mois), agregats_par_departement JSON |
| `recrutements`, `profils`, `postulants` | Workflow recrutement | postulant.statut 0/1/2/3 (nouveau→entretenu→retenu→rejeté) |

### Relations clés

```
User ──── 1:1 ──── Employee ──── 1:N ──── Affilie
                       │              │
                       ├──── 1:N ──── Absence ─── (valideur User)
                       ├──── 1:N ──── Paie ──── 1:N ──── Payement
                       │                  │
                       │                  └─ N:N ─── Rubrique (via gpaie_rubrique)
                       ├──── 1:N ──── Mission, Formation, Sanction, Depart…
                       └──── 1:N ──── EvenementCarriere ─── TypeEvenementCarriere
```

---

## 4. Moteur de paie

### Conception

Le bulletin de paie est calculé dynamiquement par **`PaieCalculator`** à partir du catalogue de **rubriques paramétrables** stocké en base. Les rubriques peuvent être :

- **`gain`** : prime, indemnité, heures sup. → entrent dans le brut
- **`cotisation`** : CNSS, CNAMGS, taxe formation. Si le code commence par `PAT_` → patronale, sinon salariale
- **`retenue`** : IRPP, avances → déduites du net imposable

Chaque rubrique a une **`base_calcul`** :
- `fixe` : `montant_fixe` direct
- `pourcentage` : `base × taux / 100`
- `formule` : expression analysée par `ExpressionEvaluator`

### Sécurité de l'évaluateur d'expressions

`ExpressionEvaluator` n'utilise AUCUNE fonction PHP capable d'exécuter du code arbitraire. Il implémente un parser maison :

1. **Tokenisation** : nombres, opérateurs, fonctions, parenthèses
2. **Conversion postfixe** (algorithme Shunting Yard)
3. **Réduction par pile** : seules `+ - * /` et `max/min/round/abs/floor/ceil` sont supportées

Toute syntaxe non whitelistée lève `\InvalidArgumentException`.

**Variables disponibles dans les formules** :
- `salaire_base`, `primes`, `indemnites`, `heures_sup`
- `brut` (disponible après calcul des gains)
- `net_imposable` (disponible après cotisations salariales)
- `jours_travailles` (défaut 30)

Exemple — IRPP simplifié Gabon :
```
round((net_imposable - 150000) * 0.1)
```

### Catalogue Gabon par défaut

Seeder `RhRubriquesSeeder` (10 rubriques) :
- **Gains** : prime ancienneté 2%, indemnité transport 30 000 XAF, indemnité logement 10%, heures sup 25%
- **Cotisations salariales** : CNSS 2.5%, CNAMGS 1%
- **Cotisations patronales** : CNSS 16%, CNAMGS 4.1%, TFPC 0.5%
- **IRPP** : `round((net_imposable - 150000) * 0.1)`

```bash
php artisan db:seed --class=RhRubriquesSeeder
```

### Workflow paie

```
1. PaieController::genererBulletin (POST /rh/paie/generer)
   ↓
2. PaieCalculator::calculer (inputs → bulletin + lignes)
   ↓
3. PaieCalculator::persister (DB transaction)
   ↓ crée Paie (statut=0 brouillon) + lignes gpaie_rubrique
4. PaieController::valider (statut 0 → 1)
   ↓
5. PayementController::store (paiement partiel/total)
   ↓ si totalement payé : Paie.statut = 2 (payée)
6. PayementGlobalController::recalculer (agrège par mois)
   ↓
7. PayementGlobalController::cloturer (lock comptable)
```

### Validation métier

- Le montant d'un Payement ne peut pas dépasser le **reste à payer** sur la Paie
- Une Paie validée ne peut être modifiée que par actions explicites
- Anonymisation RGPD : conserve les Paies (obligations légales) mais efface les PII de l'Employee

---

## 5. Conformité & sécurité

### Permissions Spatie (RBAC)

Pour chaque entité RH, 9 actions sont seedées : `create, read, update, delete, validate, export, publish, assign, process`.

Total : **18 entités × 9 actions = 162 permissions RH**.

Rôles préconfigurés :
- **super-admin** : toutes
- **admin** : toutes sauf delete:role / delete:permission
- **manager** : read:* / validate:* / export:*
- **user** : read:* uniquement

### Audit trail

Modèles tracés via `spatie/laravel-activitylog` (table `activity_log`) :
- `Employee` — modifications de salaire, contrat, statut, IBAN…
- `Paie` — création, validation, paiement
- `Payement` — règlements (mode, montant, référence)
- `Sanction` — toute modification
- `Depart` — workflow sortie

Consultation : `/rh/audit-log` (super-admin uniquement). Filtres : module, action, ID entité.

### Conformité RGPD

**Export portabilité** (`GET /rh/employees/{id}/rgpd/export`)
Retourne un JSON consolidé : employee + affiliés + absences + compétences + qualifications + formations + paies + missions + événements carrière + sanctions + départs + évaluations + payements.

**Droit à l'oubli** (`POST /rh/employees/{id}/rgpd/anonymiser`)
Sur confirmation explicite (`confirmation=ANONYMISER` + `motif`) :
- Remplace : noms, prenoms, matricule, email, contact, adresse, date_naissance, numero_secu, IBAN, situation_matrimoniale, sexe, nationalité → valeurs anonymes/null
- Marque statut=3 (parti)
- Trace l'opération dans `activity_log` (log_name=rgpd)
- **Conserve** : historique paie (obligations légales 10 ans)

---

## 6. Routes — récapitulatif

Préfixe `rh.` (middleware `permission:read:*`).

### Index par sous-module (toutes en GET)
```
/rh                              rh.dashboard          (vue d'ensemble + KPIs)
/rh/audit-log                    rh.audit-log          (journal d'activité)
/rh/employees                    rh.employees.index
/rh/affilies                     rh.affilies.index
/rh/evenements-carriere          rh.evenements-carriere.index
/rh/competences                  rh.competences.index
/rh/qualifications               rh.qualifications.index
/rh/formations                   rh.formations.index
/rh/absences                     rh.absences.index
/rh/conges-soldes                rh.conges-soldes.index
/rh/plannings                    rh.plannings.index
/rh/missions                     rh.missions.index
/rh/evaluations-performance      rh.evaluations-performance.index
/rh/sanctions                    rh.sanctions.index
/rh/departs                      rh.departs.index
/rh/paie                         rh.paie.index
/rh/rubriques                    rh.rubriques.index
/rh/payements                    rh.payements.index
/rh/payements-globals            rh.payements-globals.index
/rh/recrutements                 rh.recrutements.index
```

### Actions spécifiques
```
POST  /rh/paie/apercu                          paie.apercu       (JSON preview du calcul)
POST  /rh/paie/generer                         paie.generer      (génère via moteur rubriques)
POST  /rh/paie/{paie}/valider                  paie.valider      (brouillon → validé)
POST  /rh/payements-globals/recalculer         payements-globals.recalculer
POST  /rh/payements-globals/{pg}/cloturer      payements-globals.cloturer
GET   /rh/employees/{employee}/rgpd/export     employees.rgpd.export
POST  /rh/employees/{employee}/rgpd/anonymiser employees.rgpd.anonymiser
```

---

## 7. Tests

```bash
./vendor/bin/pest --filter=RhTest
```

**26 tests Pest, 103 assertions** :
- 7 tests CRUDs orphelins (affiliés, qualifications, compétences, formations, missions, rubriques, évènements carrière)
- 5 tests workflows critiques (sanctions, départs, évaluations, congés soldés, plannings)
- 6 tests moteur paie (ExpressionEvaluator sûreté + PaieCalculator + payements + agrégats)
- 6 tests audit/RGPD/dashboard
- 1 smoke test sur les 20 routes index

**Suite complète** : 75 tests passent (204 assertions) — aucune régression.

---

## 8. Migrations (chronologique)

```
2026_02_18_233710_create_employees_table              (existante)
2026_02_18_233715_create_absences_table               (existante)
2026_02_18_233731_create_formations_table             (existante)
2026_02_18_233737_create_recrutements_table           (existante)
2026_02_18_233747_create_paies_table                  (existante, table=gpaies)
2026_05_22_090013_create_activity_log_table           (spatie/activitylog)
2026_05_22_090014_add_event_column_to_activity_log_table
2026_05_22_090015_add_batch_uuid_column_to_activity_log_table
2026_05_22_100000_create_sanctions_table
2026_05_22_100100_create_departs_table
2026_05_22_100200_create_evaluations_performance_table
2026_05_22_100300_create_conges_soldes_table          (solde_disponible GENERATED)
2026_05_22_100400_create_plannings_table              (unique employee+date)
2026_05_22_110000_create_payements_table
2026_05_22_110100_create_payements_globals_table      (unique annee+mois)
2026_05_22_110200_create_gpaie_rubrique_table         (pivot)
```

---

## 9. Pièges connus / Points d'attention

1. **`statut` int legacy** : les tables ERP (employees, absences, gpaies…) utilisent `statut` en INTEGER, pas en string. PostgreSQL est strict — toujours passer un int.
2. **Colonne GENERATED** : `conges_soldes.solde_disponible` est calculée par PostgreSQL via `(droit_annuel + report_n_moins_1 + acquis_periode - pris_periode)`. Ne pas la fillable.
3. **`gpaie_rubrique`** : pivot avec `withPivot('base', 'taux', 'montant', 'imposable', 'cotisable')`. Toujours utiliser `$paie->rubriques()->attach($id, [...])`.
4. **Évaluateur formule** : whitelist stricte. Tester localement avec `ExpressionEvaluator::compute(string)` avant de stocker une formule.
5. **Audit trail** : `LogsActivity` doit être utilisé avec `->logOnlyDirty()->dontSubmitEmptyLogs()` pour éviter le bruit sur les saves sans changement.
6. **RGPD** : l'anonymisation est **irréversible**. Toujours exiger la confirmation explicite + motif.
7. **PaieController** : `apercu()` retourne du JSON pour preview AJAX, `genererBulletin()` persiste. Les deux utilisent les mêmes inputs.

---

## 10. Roadmap

- [ ] Calcul automatique du `report_n_moins_1` sur ouverture nouvelle année
- [ ] Export Excel/PDF des bulletins
- [ ] Connexion CNSS/CNAMGS API Gabon pour télédéclaration
- [ ] Module GPEC complet (cartographie compétences/postes, viviers talents)
- [ ] Workflow signatures électroniques (bulletins, évaluations)
- [ ] Application mobile pour pointage employés
