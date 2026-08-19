# CAHIER DES CHARGES GLOBAL
# ERP OptimiZe - Solution de Gestion Integree d'Entreprise

---

| Information          | Valeur                              |
|----------------------|-------------------------------------|
| **Reference**        | OPTIMIZE-CDC-GLOBAL-V1.0            |
| **Date**             | Mars 2026                           |
| **Classification**   | Confidentiel - Usage Interne        |
| **Auteur**           | Equipe Technique Yubile             |
| **Maitre d'ouvrage** | Yubile Technologie                  |
| **Version**          | 1.0                                 |

---

## TABLE DES MATIERES

1. [Introduction et Contexte du Projet](#1-introduction-et-contexte-du-projet)
2. [Architecture Technique Generale](#2-architecture-technique-generale)
3. [Module Finance](#3-module-finance)
4. [Module RH / Paie](#4-module-rh--paie)
5. [Module Achats / Approvisionnements](#5-module-achats--approvisionnements)
6. [Module Moyens Generaux](#6-module-moyens-generaux)
7. [Exigences Transversales](#7-exigences-transversales)
8. [Base de Donnees](#8-base-de-donnees)
9. [Planning et Livrables](#9-planning-et-livrables)
10. [Conclusion et Recommandations](#10-conclusion-et-recommandations)

---

## 1. INTRODUCTION ET CONTEXTE DU PROJET

### 1.1 Presentation du Projet OptimiZe

OptimiZe est une solution ERP modulaire concue pour accompagner les entreprises dans la digitalisation de leurs processus de gestion. La solution couvre **quatre domaines fonctionnels majeurs** :

- **Gestion Financiere** : Pilotage budgetaire, comptabilite, tresorerie
- **Gestion des Ressources Humaines et Paie** : Administration du personnel, paie, GPEC
- **Gestion des Achats et Approvisionnements** : Cycle complet des achats, gestion des fournisseurs
- **Moyens Generaux** : Gestion du patrimoine, maintenance, logistique interne

### 1.2 Objectifs Strategiques

| Objectif                        | Description                                                          | Priorite |
|---------------------------------|----------------------------------------------------------------------|----------|
| Centralisation des donnees      | Unifier les informations metier dans une base unique                 | Haute    |
| Automatisation des processus    | Reduire les taches manuelles et les erreurs                          | Haute    |
| Conformite reglementaire        | Respecter les normes comptables et sociales en vigueur               | Haute    |
| Pilotage decisionnel            | Fournir des tableaux de bord et reporting en temps reel              | Moyenne  |
| Evolutivite                     | Permettre l'ajout de modules et l'adaptation aux besoins clients     | Moyenne  |

### 1.3 Perimetre Fonctionnel

```
SOLUTION OPTIMIZE
|
|-- Module Finance
|   - Planification budgetaire
|   - Gestion des lignes budgetaires
|   - Comptabilite generale
|   - Tresorerie et banques
|   - Reporting financier
|
|-- Module RH/Paie
|   - Administration du personnel
|   - Gestion des carrieres
|   - Paie et cotisations sociales
|   - Formation et competences
|   - Temps et activites
|
|-- Module Achats
|   - Gestion des fournisseurs
|   - Commandes et approvisionnements
|   - Receptions et controles
|   - Factures fournisseurs
|
|-- Module Moyens Generaux
    - Gestion du patrimoine
    - Maintenance et interventions
    - Gestion des stocks internes
    - Logistique et deplacements
```

### 1.4 Parties Prenantes

| Role             | Responsable |
|------------------|-------------|
| Chef de Projet   | Stevy       |
| Pilote           | Arnold      |
| Support          | Lionel      |

---

## 2. ARCHITECTURE TECHNIQUE GENERALE

### 2.1 Stack Technique

| Couche             | Technologie              | Version     | Justification                      |
|--------------------|--------------------------|-------------|------------------------------------|
| Backend            | PHP                      | 8.2+        | Support LTS, performance           |
| Framework          | Laravel                  | 12.x        | Ecosysteme riche, securite         |
| Frontend           | Bootstrap / Tailwind CSS | 5.x / 4.x  | UX moderne, responsive             |
| Base de donnees    | MySQL / PostgreSQL       | 8.0+ / 14+  | Fiabilite, replication             |
| Cache              | Redis                    | 7.x         | Performance des requetes           |
| File d'attente     | Laravel Queue            | -           | Traitement asynchrone              |
| Authentification   | Laravel Sanctum/Passport | -           | API tokens, OAuth2                 |
| Stockage fichiers  | S3-compatible            | -           | Scalabilite stockage               |
| Build Tool         | Vite                     | 7.x         | Compilation rapide des assets      |

### 2.2 Contraintes Generales

| Contrainte              | Valeur                          |
|-------------------------|---------------------------------|
| Architecture            | Client-Serveur                  |
| Type d'application      | Web et Mobile                   |
| SGBDR                   | MySQL / PostgreSQL              |
| Programmation           | PHP, Javascript, HTML           |
| Framework Backend       | Laravel                         |
| Framework UI            | Bootstrap / Tailwind CSS        |
| Utilisation             | 1 instance par client           |
| Authentification API    | OAuth2 (Passport) + Sanctum     |

### 2.3 Description des Interfaces

- La solution devra echanger des informations avec d'autres applications du Systeme d'Information
- Support des echanges de fichiers en differents formats (XML, Web Service, TXT, XLS, PDF)
- Connecteurs pour integration avec les systemes financiers externes
- Integrations bancaires : Import releves CFONB, SEPA, API bancaires
- Integrations administratives : Declaration TVA, liasses fiscales (format EDI-TDFC)
- Connecteurs ERP tiers : SAP, Sage, Cegid via API REST

---

## 3. MODULE FINANCE

### 3.1 Presentation Fonctionnelle

Le module Finance permet la gestion complete du cycle financier de l'entreprise, de la planification budgetaire au reporting, en passant par la comptabilite et la tresorerie.

### 3.2 Sous-Modules et Fonctionnalites

#### 3.2.1 Planification Budgetaire

| Fonctionnalite          | Description                                                     | Priorite |
|-------------------------|-----------------------------------------------------------------|----------|
| Gestion des exercices   | Creation, activation, cloture des exercices budgetaires         | Haute    |
| Saisie des budgets      | Interface de saisie multi-niveaux avec validation               | Haute    |
| Reports budgetaires     | Transfert des credits non consommes d'un exercice a l'autre     | Moyenne  |
| Dotations               | Allocation des enveloppes par centre de responsabilite          | Haute    |
| Simulations             | Scenarios "what-if" pour l'aide a la decision                   | Moyenne  |
| Modifications           | Transferts, virements, abondements avec workflow                | Haute    |

Fonctionnalites detaillees :
- Gestion des exercices budgetaires (planifie, en cours d'execution, clotures...)
- Planification des budgets
- Gestion des reports et des dotations
- Suivi budgetaire ou reporting
- Simulations budgetaires
- Modifications budgetaires (ajout ou transferts de budgets...)
- Workflow de validation

#### 3.2.2 Gestion des Lignes Budgetaires

- Tableau de gestion des lignes budgetaires
- Tableau de gestion des familles/types de lignes
- Workflow de validation
- Modele d'impression personnalisable

Entites principales :
- **titres** : Imputation, libelle, seuil, type_ligne (depense/recette)
- **lignes** : Nature, seuil, libelle, rattachee a un titre
- **budget_lignes** : Budget par ligne, exercice, dotation, engagement, transferts

#### 3.2.3 Comptabilite Generale

| Entite             | Description              | Champs Cles                                            |
|--------------------|--------------------------|--------------------------------------------------------|
| `comptes`          | Plan comptable           | code, nom, type (banque/caisse/tiers), solde, rib      |
| `grand_livres`     | Ecritures comptables     | date, montant, compte, exercice, piece justificative   |
| `grand_livre_details` | Lignes d'ecriture     | facture, produit, quantites, taxes                     |
| `mode_reglements`  | Modes de paiement        | libelle, code, statut                                  |

**Regles de Gestion Comptables :**
- Double entree obligatoire pour toute ecriture
- Lettrage automatique des comptes tiers
- Controle de coherence : Debit = Credit
- Archivage legal des pieces justificatives (10 ans)

#### 3.2.4 Tresorerie et Banques

Fonctionnalites :
- Rapprochement bancaire automatique/manuel
- Previsionnel de tresorerie glissant
- Alertes de seuils de solde
- Gestion multi-devises avec taux de change

#### 3.2.5 Reporting Financier

| Rapport                         | Periodicite    | Destinataires                   | Format              |
|---------------------------------|----------------|---------------------------------|---------------------|
| Balance generale                | Mensuelle      | DAF, Commissaire aux comptes    | PDF/Excel           |
| Compte de resultat              | Mensuelle      | Direction Generale              | PDF/Dashboard       |
| Tableau de flux de tresorerie   | Hebdomadaire   | Tresorier                       | Excel               |
| Execution budgetaire            | Mensuelle      | Managers, DG                    | Dashboard interactif|
| Etat des engagements            | Quotidienne    | Achats, Finance                 | PDF                 |

### 3.3 Workflows de Validation Finance

Les processus financiers (budgets, depenses, recettes, modifications budgetaires) sont soumis a des workflows de validation multi-niveaux configures selon la hierarchie de l'organisation.

### 3.4 Gestion des Depenses et Recettes

- Saisies des ordres de depenses / recettes
- Gestion des fournisseurs et clients
- Gestion des factures
- Historique des depenses et recettes
- Journal des evenements

---

## 4. MODULE RH / PAIE

### 4.1 Presentation Fonctionnelle

Le module RH/Paie couvre l'ensemble du cycle de vie du collaborateur, de l'embauche au depart, avec une gestion conforme des paies et des obligations sociales.

### 4.2 Sous-Modules et Fonctionnalites

#### 4.2.1 Administration du Personnel

| Entite          | Description                 | Donnees Gerees                                |
|-----------------|-----------------------------|-----------------------------------------------|
| `employees`     | Fiches collaborateurs       | Identite, contrat, poste, rattachement        |
| `affilies`      | Ayants-droit                | Conjoint, enfants, beneficiaires              |
| `competences`   | Competences                 | Certifications, formations, evaluations       |
| `qualifications`| Diplomes et titres          | Niveaux, dates, organismes                    |

**Donnees personnelles gerees :**
- Informations biologiques : nom, prenom, date de naissance, sexe, nationalite, situation matrimoniale
- Informations professionnelles : matricule, poste, departement, date d'embauche, type de contrat
- Informations de contact : adresse, telephone, email
- Hierarchie : superieur hierarchique
- Pieces jointes : CV, documents administratifs

**Gestion des employes :**
- Gestion des informations biologiques et professionnelles
- Gestion des affilies (conjoint, enfants, proches)
- Gestion des absences (conges, absences injustifiees, maladie, mission...)
- Gestion des calendriers de vacances
- Gestion des equipes de travail
- Gestion des planning/emplois du temps de travail

#### 4.2.2 Gestion des Carrieres et GPEC

- Gestion des qualifications (certificats, diplomes, competences)
- Gestion de la mobilite (affectation et mutation)
- Groupes sociaux professionnels
- Distinctions et sanctions
- Evenements de carriere (promotion, mutation, licenciement...)

**Fonctionnalites GPEC :**
- Cartographie des competences par metier
- Detection des ecarts competences/besoins
- Plans de developpement individuels (PDI)
- Gestion des viviers de talents et successeurs

#### 4.2.3 Gestion de la Paie

| Composant         | Description         | Regles de Calcul                                   |
|-------------------|---------------------|----------------------------------------------------|
| `gpaies`          | Bulletins de paie   | Base brute, cotisations, net a payer               |
| `rubriques`       | Elements de paie    | Formules parametrables (fixe, variable, conditionnel) |
| `payements`       | Reglements          | Virement, cheque, especes avec tracabilite         |
| `payementglobals` | Agregats            | Masse salariale, charges sociales                  |

**Fonctionnalites de la paie :**
- Calcul des salaires et cotisations sociales
- Generation des bulletins de paie
- Simulation de la masse salariale
- Etats de salaires individuels et collectifs
- Gestion des acomptes et avances

#### 4.2.4 Gestion du Temps et des Absences

| Type d'absence              | Gestion                    | Impact Paie                          |
|-----------------------------|----------------------------|--------------------------------------|
| Conges payes                | Solde, prise, report       | Maintien salaire                     |
| Conges maladie              | Justificatif, duree        | IJSS, complement employeur           |
| Absences injustifiees       | Retenue proportionnelle    | Deduction sur salaire                |
| Missions professionnelles   | Ordre de mission, frais    | Remboursement frais                  |
| Heures supplementaires      | Plafond, majoration        | Majoration selon convention          |

#### 4.2.5 Gestion du Recrutement

- Gestion des campagnes de recrutement (ouvert, ferme, pourvu)
- Fiches de profils recherches (nombre de postes, competences)
- Gestion des postulants (candidatures, CV, lettres)
- Suivi du processus : nouveau -> entretenu -> retenu -> rejete
- Examens et evaluations des candidats
- Processus d'embauche : lien postulant -> employe

#### 4.2.6 Formation et Developpement

- Catalogue de formations interne/externe
- Inscriptions et convocations automatisees
- Suivi de presence et evaluation post-formation
- Mise a jour automatique des competences acquises
- Expression des besoins de formation
- Elaboration et suivi des plans de formation
- Gestion du e-learning (LCMS)

#### 4.2.7 Gestion de la Performance et Sanctions

- Gestion de la performance (evaluations)
- Gestion des sanctions (blame, avertissements, rappel a l'ordre, fautes...)
- Gestion des departs (licenciement, demissions, retraite, fin de contrat)

### 4.3 Conformite et Securite RH

- Conformite RGPD pour les donnees personnelles
- Anonymisation et droit a l'oubli
- Audit trail complet sur les modifications de donnees sensibles
- Gestion des entretiens individuels et evaluations

---

## 5. MODULE ACHATS / APPROVISIONNEMENTS

### 5.1 Presentation Fonctionnelle

Le module Achats gere le cycle complet des approvisionnements, depuis l'expression du besoin jusqu'au paiement du fournisseur, en assurant tracabilite et controle des couts.

### 5.2 Processus Metier Achats

```
Expression du besoin
    |
    v
Demande d'achat
    |
    v
Consultation fournisseurs (Appel d'offres)
    |
    v
Comparaison des offres
    |
    v
Commande fournisseur
    |
    v
Reception et controle qualite
    |
    v
Facturation et paiement
```

### 5.3 Entites Principales

#### 5.3.1 Gestion des Fournisseurs

- Fichier fournisseur complet (coordonnees, RIB, conditions commerciales)
- Evaluation et notation des fournisseurs
- Historique des commandes par fournisseur
- Gestion multi-fournisseurs par produit

#### 5.3.2 Cycle des Commandes

| Entite                       | Role                     | Relations                                           |
|------------------------------|--------------------------|-----------------------------------------------------|
| `approvisionnements`         | Demande/Commande d'achat | Vers produits, fournisseurs                         |
| `approvisionnementsproduits` | Lignes de commande       | Produit, quantite, prix unitaire                    |
| `livraisonsfournisseurs`     | Reception physique       | Lien vers commande, quantites recues                |
| `commandesfournisseurs`      | Commande validee         | Statut, dates, references                           |

**Workflow de Validation des Commandes :**
Demande -> Validation N+1 -> Validation budgetaire -> Commande -> Reception -> Facturation

#### 5.3.3 Gestion des Produits et Stocks

- Catalogue produits avec categorisation multi-niveaux
- Gestion des stocks (entrees, sorties, inventaires)
- Alertes de seuils de reapprovisionnement
- Valorisation des stocks (FIFO, CMUP)

#### 5.3.4 Reception et Controle Qualite

- Reception partielle/totale avec tolerance
- Controle qualite avec grille de criteres parametrable
- Gestion des non-conformites : retour, avoir, remplacement
- Mise a jour automatique des stocks et comptabilite

### 5.4 Integration Comptable Achats

- Imputation automatique sur les lignes budgetaires
- Generation des ecritures comptables a la reception et au paiement
- Rapprochement commande / reception / facture (3 voies)
- Alertes de depassement budgetaire

---

## 6. MODULE MOYENS GENERAUX

### 6.1 Presentation Fonctionnelle

Le module Moyens Generaux assure la gestion operationnelle du patrimoine, de la maintenance et des services supports de l'entreprise.

### 6.2 Sous-Modules

#### 6.2.1 Gestion du Patrimoine et Immobilisations

- Fichier des immobilisations (acquisitions, localisations, affectations)
- Calcul automatique des amortissements comptables
- Alertes de maintenance preventive
- Historique des interventions et couts
- Etat du parc avec indicateurs de vetuste

**Gestion Immobiliere :**
- Gestion des plans, lotissements, baux
- Gestion des loyers, charges, indices, servitudes
- Contrats d'assurance
- Gestion des documents legaux (quittances, convocations, revisions indices)

#### 6.2.2 Gestion des Interventions et Maintenance

| Entite                    | Description                  | Workflow                                      |
|---------------------------|------------------------------|-----------------------------------------------|
| `dysfonctionnements`      | Signalements d'incident      | Utilisateur -> Validation -> Affectation      |
| `interventions`           | Ordres de travail            | Planification -> Realisation -> Cloture       |
| `typesdysfonctionnements` | Categories d'incidents       | Parametrage par type de bien                  |

**Fonctionnalites :**
- Declarations de dysfonctionnements, sinistres, infractions
- Demandes d'achats de fournitures et materiels
- Portail intranet "services clients internes" pour les demandes

#### 6.2.3 Gestion des Deplacements et Missions

- Ordres de mission avec validation hierarchique
- Reservations integrees (transport, hebergement)
- Notes de frais avec justificatifs numerises
- Rapprochement avec la paie (indemnites, avances)

#### 6.2.4 Gestion des Espaces et Logistique

- Plan des locaux avec affectation des postes
- Gestion des cles, badges, equipements
- Reservation de salles et ressources partagees
- Suivi des consommables (fournitures, petits equipements)

#### 6.2.5 Module Finance associe aux Moyens Generaux

- Budgets, suivi des realisations et des engagements
- Reporting budgetaire
- Tableau de bord instantane
- Imputation par ligne budgetaire
- Archivage des factures

---

## 7. EXIGENCES TRANSVERSALES

### 7.1 Gestion des Utilisateurs et Securite

#### 7.1.1 Modele de Roles et Permissions

Le systeme utilise **Spatie Laravel Permission** pour la gestion fine des droits d'acces.

Tables concernees : `users`, `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

Les permissions sont scopees par module (finance, rh, achat, mg).

#### 7.1.2 Profils Utilisateurs Standards

| Profil          | Acces Fonctionnel              | Acces Donnees                  | Validation      |
|-----------------|--------------------------------|--------------------------------|-----------------|
| **Gestionnaire**| CRUD sur son perimetre         | Donnees de son service         | Niveau N+1      |
| **Manager**     | Lecture + validation           | Donnees de son departement     | Niveau N+2      |
| **Directeur**   | Lecture + validation haute     | Donnees direction              | Niveau DG       |
| **DG / CA**     | Consultation reporting         | Donnees consolidees            | Validation finale|
| **Auditeur**    | Lecture seule + export         | Toutes donnees (anonymisees)   | Aucune          |

#### 7.1.3 Journalisation et Tracabilite

Table `logs` - Donnees enregistrees :
- Identifiant utilisateur et role au moment de l'action
- Adresse IP et empreinte navigateur
- Horodatage precis (UTC + fuseau local)
- Action realisee (CRUD + parametre cible)
- Valeurs avant/apres pour les modifications sensibles

Table `authentifications` - Suivi des connexions :
- IP, email, operations (login, logout, failed), lieu, user_agent

Table `sessions` - Sessions actives :
- user_id, ip_address, user_agent, last_activity

#### 7.1.4 Securite d'Acces

- Controle d'acces par login et mot de passe
- Modification de mot de passe a tout moment
- Authentification a deux facteurs (2FA) disponible
- Toute connexion/deconnexion repertoriee
- Compatibilite avec les standards de securite antivirus
- Historique des changements de mots de passe

### 7.2 Gestion des Donnees et Conformite

#### 7.2.1 Politique de Conservation

| Type de Donnee                          | Duree de Conservation | Base Legale         |
|-----------------------------------------|-----------------------|---------------------|
| Donnees personnelles employes           | 5 ans apres depart    | Code du travail     |
| Bulletins de paie                       | 50 ans                | Code du travail     |
| Ecritures comptables                    | 10 ans                | Code de commerce    |
| Factures fournisseurs/clients           | 10 ans                | Code de commerce    |
| Logs d'acces et d'audit                 | 3 ans                 | RGPD / Securite     |
| Donnees de recrutement (non retenus)    | 2 ans                 | RGPD                |

#### 7.2.2 Anonymisation et Droit a l'Oubli

- Processus d'anonymisation des donnees personnelles sur demande
- Respect du RGPD et de la legislation locale

### 7.3 Performance et Disponibilite

#### 7.3.1 Objectifs de Service (SLA)

| Metrique                               | Cible           | Methode de Mesure                |
|----------------------------------------|-----------------|----------------------------------|
| Disponibilite applicative              | 99.5% mensuel   | Monitoring externe               |
| Temps de reponse API (P95)             | < 500 ms        | APM (New Relic / Datadog)        |
| Temps de generation rapport complexe   | < 30 secondes   | Logs applicatifs                 |
| RTO (Recovery Time Objective)          | < 4 heures      | Tests de restauration            |
| RPO (Recovery Point Objective)         | < 15 minutes    | Frequence des backups            |

#### 7.3.2 Strategie de Cache et Optimisation

- Cache Redis pour les requetes frequentes
- Pagination et lazy loading des donnees volumineuses
- Optimisation des requetes Eloquent

### 7.4 Ergonomie et Convivialite

- Interface graphique conviviale avec navigation simple entre les menus
- Simplicite d'utilisation pour les utilisateurs non inities
- Charte graphique identique pour l'ensemble des pages et menus
- Moyen d'impression integre a l'outil a tout moment
- Interface responsive (Web et Mobile)
- Multi-langue
- Solution multi-systeme (Windows, Linux, Mac)
- Multi-device (PC, Tablette, Mobile)

### 7.5 Sauvegarde et Restauration

- Utilisation des outils standards du marche pour archivages, sauvegardes et restauration
- Procedure de restauration disponible en cas d'incident
- Procedures de retour en arriere fournies
- Politique detaillee de sauvegarde, d'archivage et de restauration (periodicite, frequence par type de donnees)

**Procedure de Restauration d'Urgence :**
1. Alerte et analyse : Confirmation de l'incident, evaluation de l'impact
2. Isolation : Mise en mode maintenance de l'application
3. Restauration base de donnees
4. Restauration fichiers : Extraction des uploads et configurations
5. Verification d'integrite : Tests de coherence des donnees
6. Bascule : Redirection du trafic vers l'environnement restaure
7. Post-mortem : Analyse des causes et mise a jour des procedures

### 7.6 Parametrage de l'Application

**Informations de parametrages :**
- Gestion des branches / centres de responsabilites ou entites
- Gestion des departements (type d'entite ou centre de responsabilite)
- Gestion des domaines de prestations
- Gestion des villes, pays, regions, arrondissements, quartiers
- Gestion des groupes d'employes ou grade
- Gestion des organisations et types d'organisations
- Configurations systeme (table `configs`)

### 7.7 Export et Import de Donnees

- Export Excel/PDF fiable pour tous les modules
- Import/Export de donnees en differents formats (XML, CSV, XLS)
- Option d'exportation/importation de donnees massives
- Connecteurs pour echanges avec systemes tiers

---

## 8. BASE DE DONNEES

### 8.1 Schema General

La base de donnees est organisee en **6 sections principales** :

#### Section 1 : Tables Systeme et Authentification
| Table                      | Description                                  |
|----------------------------|----------------------------------------------|
| `users`                    | Comptes utilisateurs                         |
| `roles`                    | Roles du systeme                             |
| `permissions`              | Permissions par module                       |
| `model_has_roles`          | Association utilisateur-role                 |
| `model_has_permissions`    | Association utilisateur-permission           |
| `role_has_permissions`     | Association role-permission                  |
| `sessions`                 | Sessions actives                             |
| `logs`                     | Journal d'audit                              |
| `authentifications`        | Historique connexions                        |
| `changespasswords`         | Historique changements MDP                   |

#### Section 2 : Tables Geographiques et Organisation
| Table                | Description                                        |
|----------------------|----------------------------------------------------|
| `pays`               | Referentiel pays (codes ISO)                       |
| `regions`            | Regions par pays                                   |
| `type_localites`     | Types de localites                                 |
| `localites`          | Localites par region/pays                          |
| `arrondissements`    | Arrondissements par localite                       |
| `quartiers`          | Quartiers par arrondissement                       |
| `entites`            | Entites/branches de l'entreprise                   |
| `organisations`      | Structure organisationnelle hierarchique            |
| `typesorganisations` | Types d'organisations                              |

#### Section 3 : Module Finance
| Table                      | Description                                  |
|----------------------------|----------------------------------------------|
| `configs`                  | Configurations systeme                       |
| `exercices`                | Exercices budgetaires                        |
| `titres`                   | Categories budgetaires (depense/recette)     |
| `lignes`                   | Lignes budgetaires par titre                 |
| `budget_lignes`            | Budgets alloues par ligne et exercice        |
| `modification_budgetaires` | Modifications/transferts budgetaires         |
| `comptes`                  | Plan comptable (banque, caisse, tiers)       |
| `transaction_comptes`      | Mouvements sur comptes (debit/credit)        |
| `grand_livres`             | Ecritures du grand livre                     |
| `grand_livre_details`      | Details des ecritures (factures, produits)    |
| `mode_reglements`          | Modes de reglement                           |
| `transactions`             | Transactions financieres                     |

#### Section 4 : Module RH et Paie
| Table                      | Description                                  |
|----------------------------|----------------------------------------------|
| `employees`                | Fiches employes                              |
| `affilies`                 | Ayants-droit des employes                    |
| `absences`                 | Gestion des absences/conges                  |
| `competences`              | Competences des employes                     |
| `qualifications`           | Diplomes et qualifications                   |
| `evenementscarrieres`      | Evenements de carriere                       |
| `typesevenementscarrieres` | Types d'evenements carriere                  |
| `recrutements`             | Campagnes de recrutement                     |
| `postulants`               | Candidats                                    |
| `profils`                  | Profils de poste recherches                  |
| `embauches`                | Processus d'embauche                         |
| `pcompetences`             | Competences des postulants                   |
| `pqualifications`          | Qualifications des postulants                |
| `pexamens`                 | Examens des postulants                       |
| `formations`               | Sessions de formation                        |
| `gpaies`                   | Bulletins de paie                            |
| `rubriques`                | Rubriques de paie parametrables              |
| `payements`                | Reglements salaires                          |
| `payementglobals`          | Agregats paie (masse salariale)              |

#### Section 5 : Module Achats/Approvisionnements
| Table                          | Description                              |
|--------------------------------|------------------------------------------|
| `produits`                     | Catalogue produits                       |
| `fournisseurs`                 | Fichier fournisseurs                     |
| `approvisionnements`           | Demandes/commandes d'achat               |
| `approvisionnementsproduits`   | Lignes de commande                       |
| `commandesfournisseurs`        | Commandes validees                       |
| `livraisonsfournisseurs`       | Receptions de marchandises               |
| `factures`                     | Factures fournisseurs                    |

#### Section 6 : Module Moyens Generaux
| Table                          | Description                              |
|--------------------------------|------------------------------------------|
| `immobilisations`              | Biens immobilises                        |
| `dysfonctionnements`           | Signalements d'incidents                 |
| `typesdysfonctionnements`      | Categories d'incidents                   |
| `interventions`                | Ordres de travail/maintenance            |
| `missions`                     | Ordres de mission                        |

### 8.2 Conventions Techniques de la BDD

- **Moteur** : InnoDB (support des transactions et FK)
- **Charset** : utf8mb4 / utf8mb4_unicode_ci
- **Cles primaires** : `id` bigint(20) UNSIGNED AUTO_INCREMENT
- **Soft Delete** : `deleted_at` timestamp (suppression logique)
- **Timestamps** : `created_at`, `updated_at` sur toutes les tables
- **Attributs flexibles** : `extra_attributes` JSON pour extensions futures
- **Pieces jointes** : `fichiersjoin` TEXT pour stockage des references de fichiers
- **Statuts** : Codes numeriques avec commentaires SQL descriptifs

---

## 9. PLANNING ET LIVRABLES

### 9.1 Phasage du Projet

| Phase                                                          | Description                                    |
|----------------------------------------------------------------|------------------------------------------------|
| **Phase 1** : Conception et Specifications                     | DSFD, DAT, Maquettes, MCD/MLD, Plan de tests  |
| **Phase 2** : Developpement et Implementation                  | Code source, API REST, Scripts de seeding       |
| **Phase 3** : Recette et Mise en Production                    | Tests, guides utilisateurs, procedures backup  |
| **Phase 4** : Accompagnement au changement et VSR              | Formation, support, traitement anomalies       |

### 9.2 Livrables par Phase

**Phase 1 : Conception et Specifications**
- Dossier de specifications fonctionnelles detaillees (DSFD)
- Dossier d'architecture technique (DAT)
- Maquettes interactives (Figma / Adobe XD)
- Modele de donnees MCD/MLD valide
- Plan de tests et criteres d'acceptation
- **Livrable cle** : PV de validation de fin de conception

**Phase 2 : Developpement et Implementation**
- Code source versionne (Git) avec documentation inline
- API REST documentee (OpenAPI/Swagger)
- Jeux de donnees de test et scripts de seeding
- Documentation d'installation et de configuration
- Procedures de deploiement (CI/CD : GitHub Actions / GitLab CI)
- **Livrable cle** : Environnement de pre-production operationnel

**Phase 3 : Recette et Mise en Production**
- Cahier de recette avec cas de tests executes
- Rapport de tests de charge et de securite
- Guide utilisateur et administrateur (PDF + video)
- Procedures de sauvegarde, restauration et supervision
- Plan de migration des donnees (si applicable)
- **Livrable cle** : PV de recette et autorisation de mise en production

**Phase 4 : Accompagnement**
- Plan de formation valide
- Plan de communication
- PV de validation de fin de phase
- Rapports de traitement des anomalies

### 9.3 Criteres d'Acceptation par Module

| Module            | Criteres Techniques              | Criteres Fonctionnels                              | Criteres Utilisabilite                    |
|-------------------|----------------------------------|----------------------------------------------------|-------------------------------------------|
| **Finance**       | Tests unitaires > 90%, Audit comptable valide | Double entree systematique, Workflows operationnels | Saisie intuitive, Export Excel/PDF fiable |
| **RH/Paie**       | Calculs de paie certifies, Conformite RGPD | Generation bulletins conforme, Gestion absences integree | Fiche employe complete, Simulation paie temps reel |
| **Achats**        | Rapprochement 3 voies automatise, Alertes seuils | Cycle commande->reception->facture trace, Multi-fournisseurs | Interface comparaison offres, Suivi visuel commandes |
| **Moyens Generaux** | Alertes maintenance proactives, Historique complet | Cycle vie immobilisations, Suivi interventions | Tableau de bord patrimoine, Saisie rapide incidents |

---

## 10. CONCLUSION ET RECOMMANDATIONS

### 10.1 Points Forts de la Solution OptimiZe

- **Modularite** : Deploiement progressif par module selon les priorites client
- **Conformite** : Respect des normes comptables, sociales et RGPD integrees des la conception
- **Evolutivite** : Architecture micro-services ready pour montee en charge
- **Experience utilisateur** : Interface unifiee et responsive pour tous les modules
- **Multi-langue et multi-device** : Accessible sur tous types d'appareils et systemes

### 10.2 Points de Vigilance

- **Complexite des regles metier** : Necessite une phase de parametrage approfondie avec les experts metier
- **Integrations externes** : Prevoir des connecteurs robustes pour les systemes existants (comptabilite, paie externe)
- **Formation des utilisateurs** : Accompagnement indispensable pour l'adoption des nouveaux processus

### 10.3 Recommandations pour le Demarrage

1. **Commencer par un pilote** : Deployer d'abord le module Finance sur un perimetre restreint
2. **Impliquer les utilisateurs finaux** : Ateliers de co-conception pour valider les workflows
3. **Prevoir une phase de parallelisme** : Fonctionnement en double avec l'ancien systeme pendant 1 mois
4. **Mettre en place un support dedie** : Equipe projet + helpdesk pour les 3 premiers mois post-deploiement

### 10.4 Clause de Surete

Le prestataire est tenu, ainsi que l'ensemble de son personnel et sous-traitants, au secret professionnel et a l'obligation de discretion pour tout ce qui concerne les faits, informations, etudes et decisions dont il aura eu connaissance durant l'execution du projet. Une clause de confidentialite sera etablie et paraphee entre les deux parties avant demarrage des travaux.

---

*Document approuve par :*
- [ ] Direction Technique
- [ ] Direction Fonctionnelle
- [ ] Direction Generale

*Prochaine etape : Atelier de priorisation des user stories et estimation detaillee des sprints.*

*Document confidentiel - Toute reproduction ou diffusion non autorisee est interdite.*
*Yubile Technologie 2026 - Tous droits reserves*
