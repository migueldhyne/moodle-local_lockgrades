# Lock Grades (local\_lockgrades)

## Description

`local_lockgrades` est un plugin Moodle permettant aux administrateurs de verrouiller ou déverrouiller de manière récursive une catégorie de notes (grade items) ainsi que toutes ses sous-catégories et éléments associés, en utilisant un champ `idnumber` comme identifiant.

## Fonctionnalités

* **Verrouillage récursif** : Verrouille une catégorie principale et toutes ses sous-catégories, en mettant à jour les champs `locked`, `timemodified` et `locktime` de la table `mdl_grade_items`.
* **Déverrouillage récursif** : Déverrouille les mêmes éléments en remettant `locked` et `locktime` à 0 tout en conservant l’horodatage de la modification (`timemodified`).
* **Interface simple** : Formulaire disponible dans l’administration Moodle pour saisir l’`idnumber` et choisir l’action (verrouiller ou déverrouiller).
* **Sécurité** : Accès limité aux utilisateurs disposant de la capacité `moodle/site:config` (administrateurs).
* **Intégrité des données** : Utilisation de transactions pour garantir la cohérence des mises à jour.

## Prérequis

* Moodle 3.5 ou supérieur
* Accès SSH ou FTP pour copier les fichiers sur le serveur
* Droits d’administrateur sur la plateforme Moodle

## Installation

1. **Copier les fichiers**

   Placer le dossier `lockgrades` dans le répertoire `local/` de votre installation Moodle, de sorte que le chemin complet soit :

   ```
   moodle/local/lockgrades/
   ```

2. **Vérifier les permissions**

   Assurez-vous que les fichiers et dossiers ont les permissions appropriées (lecture par le serveur web) :

   ```bash
   chown -R www-data:www-data moodle/local/lockgrades
   chmod -R 755 moodle/local/lockgrades
   ```

3. **Mise à jour de la base de données**

   Connectez-vous en tant qu’administrateur sur votre site Moodle. Moodle détectera automatiquement le nouveau plugin et vous invitera à lancer la mise à jour de la base de données.

4. **Vérification**

   Allez dans **Administration du site > Plugins > Local plugins** et vérifiez que `Lock Grades` apparaît dans la liste.

## Utilisation

1. Connectez-vous avec un compte administrateur (capacité `moodle/site:config`).

2. Dans votre navigateur, ouvrez l’URL :

   ```
   https://votre-site-moodle.local/local/lockgrades/index.php
   ```

3. Saisissez la valeur de l’**idnumber** de la catégorie principale dont vous souhaitez verrouiller ou déverrouiller les notes (par exemple `totPeriode_1`).

4. Cliquez sur **Verrouiller les évaluations** ou **Déverrouiller les évaluations**.

5. Une notification confirmera la réussite de l’opération.

## Structure des fichiers

```
local/lockgrades/
├── form.php             # Definition du formulaire
├── index.php            # Page principale et logique du plugin
├── version.php          # Version et dépendances
└── lang/
    └── en/
        └── local_lockgrades.php  # Chaînes de langue
```

## Personnalisation

* **Adapter les capacités** : Si vous souhaitez restreindre l’accès à d’autres rôles, modifiez la capacité utilisée dans `index.php` (`moodle/site:config`).
* **Modifier les messages** : Éditez les chaînes de langue dans `lang/en/local_lockgrades.php`.

## Licence

Ce plugin est distribué sous la licence GNU GPL v3. Voir le fichier `LICENSE` pour plus de détails.

## Auteurs et support

* **Nom de l’auteur** : Votre Nom
* **Contact** : [votre.email@exemple.com](mailto:votre.email@exemple.com)

Pour toute question ou contribution, veuillez ouvrir une issue ou soumettre une pull request sur le dépôt GitHub du projet.
