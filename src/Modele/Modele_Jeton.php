<?php

namespace App\Modele;

use App\Utilitaire\Singleton_ConnexionPDO;
use PDO;

class Modele_Jeton
{
    /***
     * @param $valeur
     * @param $idUtilisateur
     * @param $codeAction (1 pour renouveller MDP, )
     * @return L'ID du  jeton créé ou false (si pbm!)
     */
    static function  Jeton_Creation($valeur, $idUtilisateur, $codeAction)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        /**
         * Il faut gérer ici la problématique de dateFin.
         * On va dire valide : 15 minutes
         * Pour créer une date à l'horaire de maintenant :
         *  $dateNow = date("Y-m-d H:i:s");
         * Pour ajouter une durée (ici 1h et 20 minutes)
         *  $dateModifiee = date('Y-m-d H:i:s',strtotime('+1 hour +20 minutes',strtotime($dateNow)));
         */
    }

    static function Jeton_Rechercher_ParValeur($valeur)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        /***/
    }

    static function Jeton_Delete_parID($idJeton)
    {
        $connexionPDO = Singleton_ConnexionPDO::getInstance();
        /***/
    }
}