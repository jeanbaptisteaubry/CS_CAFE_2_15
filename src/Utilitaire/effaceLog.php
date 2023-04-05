<?php


//Parcours des fichiers du répertoire
    //Si c'est un fichier de log (se terminant par log donc) (soit avec un explode
    // soit avec un objet
        //Si la date est supérieure à 90 jours dans le nom
        // (Sûrement à partir d'un explode)
            //Supprimer le fichier