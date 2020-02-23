# Procost

[![Build Status](https://travis-ci.com/kevinhenschen/symfony_procost_khenschen.svg?token=p8JLqPUFtphJzKYU2Hky&branch=master)](https://travis-ci.org/joemccann/dillinger)

Le sujet porte sur la création d'une interface web, intitulée Procost, et permettant de calculer de manière sommaire les coûts de développement des projets réalisés par les équipes de production d'une entreprise..

# Les Fonctionnalités

✔️ = AUTORISÉE
❌ = INTERDIT
❔ = SEULEMENT LUI MÊME

| Fonctionnalité | Mananger | Employeur | État 
| ------ | ------ | ------ | ------ |
| Tableau de Bord |✔|✔| Fait
| Liste des Projets |✔|✔| Fait
| Voir un Projet |✔|✔| Fait
| Créer un Projet | ✔|❌| Fait
| Éditer un Projet |✔|❌| Fait
| Livrer un Projet |✔|❌| Fait
| Liste des Métiers |✔|✔| Fait
| Ajouter un Métier |✔|❌| Fait
| Éditer un Métier |✔|❌| Fait
| Supprimer un Métier |✔ (si aucun employé) |❌| Fait
| Liste des Employés |✔|✔| Fait
| Voir un Employé |✔|❔| Fait
| Ajouter un Employé |✔| ❌| Fait
| Éditer un Employé |❌| ❔|Supprimer autorisation Manager
| Ajouter du temps |✔|❔| Fait

# Le Formattage du Code

| Formattage | Avancement (*ressenti personnel)
| ------ | ------ |
| Code Général | Assez Propre (80% Formatée)
| Template | Aménagement et organisation à voir (65% Formatée)
| DataBase | Fait (100% Formatée)
| Fixtures | Vérification Supplémentaire (80% Formatée)

### Installation

L'installation demande [Composer](https://getcomposer.org/) (v1.9.2 lors du développement)

Premièrement récupérer les données et rendez vous dans un terminal pour effectuer les commandes suivantes :

```sh
cd path_du_projet
composer i
```
Cela aura pour effet d'installer les dépendances (et donc le vendor)

Ensuite il vous faudra installer la database :

```sh
php bin/console doctrine:update:schema --sql-dump
php bin/console doctrine:update:schema --force
```

Enfin vous pourrez executer les fixtures pour la génération aléatoire d'utilisateur etc ... :

```sh
php bin/console doctrine:fixtures:load
```
****⚠ATTENTION⚠****  
**Les mots de passe sont générées de manière aléatoire et hashée dans la base de données ! Il faudra donc faire attention à bien regarder la console lors de la fixture car elle possèdera la liste des mots de passe générées par utilisateur. Car sinon il sera impossible de les récupérer de manière automatique après génération des données. Toute fois il sera possible de regénérer un nouveau mot de passe mais cette étape peut être longue si il faut éditer tout les mots de passe pour chaque utilisateur.**

Pour finir il vous suffit de lancer le serveur ... :

```sh
composer server
```


### Dépendances Supplémentaires

| Plugin | README |
| ------ | ------ |
| Faker | [fzaninotto/Faker/README.md](https://github.com/fzaninotto/Faker/blob/master/readme.md)
| KnpPaginatorBundle | [KnpPaginatorBundle/README.md](https://github.com/KnpLabs/KnpPaginatorBundle/blob/master/README.md) |

