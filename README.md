# SnowTricks

## Context

Site communautaire pour l'apprentissage des figures de snowboard.
  
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2145def8-c859-4a43-869a-900cd0e9aa64/big.png)](https://insight.sensiolabs.com/projects/2145def8-c859-4a43-869a-900cd0e9aa64)  

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/bb622e34688046d2a46a4bd54ffcc566)](https://www.codacy.com/app/Giildo/p6?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=Giildo/p6&amp;utm_campaign=Badge_Grade)

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