CreationFormulaire
==================

Utilitaire de création de formulaire en ligne à la Google Forms

Codé initialement en Avril 2013 pour un mini-projet de stage en pure PHP avec la librairie Zebra_Form. Repris en Avril 2014 pour nettoyage du code, publication sur Github, et utilisation personnelle. 

Pure PHP. No framework, no bullshit. 

==================

Architecture

* index.php : Affichage de la page d'accueil ou du formulaire désiré (id passé en GET).
* create.php : Page de création de formulaire en plusieurs étapes (step passé en GET). 
* Une base de données
* Lbrairie Zebra_Form (PHP/jQuery)

==================

Changelog et ToDoList

[Fix][DOING] Vérification et protection des arguments en GET et des différentes étapes de création de formulaire.

[Enh][TODO] Stylisation de l'interface de création.
[Enh][TODO] Stylisation de la page d'accueil et de la page formulaire.
[Fix][TODO] Correction des élèments dépendants qui ne fonctionnent pas. 
[Enh][TODO] Enregistrement des résultats en Base de données ou envois par mail, au choix. Affichage par graphique ou simple.
[Enh][TODO] Ajout Mdp (pour le créateur) pour consulter les résultats d'un formulaire. 

[Enh][DONE] Ajout d'une page d'accueil.