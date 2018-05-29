# SnowTricks

## Context

Site communautaire pour l'apprentissage des figures de snowboard.  
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/1deb4086364340e18cd94537e7554ed4)](https://app.codacy.com/app/Giildo/p6?utm_source=github.com&utm_medium=referral&utm_content=Giildo/p6&utm_campaign=badger)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2145def8-c859-4a43-869a-900cd0e9aa64/big.png)](https://insight.sensiolabs.com/projects/2145def8-c859-4a43-869a-900cd0e9aa64)

## Installation

1. Récupération du code

    Via Git en clonant ce dépôt.

2. Téléchargement des vendors

    `composer install --no-dev`

3. Création de la base de données

    Création des tables :  
    `php bin/console doctrine:database:create`
    
    Création des données :  
    `php bin/console doctrine:fixtures:load`    
    Cela ajoute :  
    - Les status des utilisateurs : "utilisateur", "contributeur" et "administrateur"
    - Les catégories "grab", "rotations" et "flip"
    - 10 figures associées à leurs catégories
    - 3 utilisateurs d'essai pour le site.

Bonne utilisation !