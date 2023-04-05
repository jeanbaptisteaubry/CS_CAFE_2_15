<?php

namespace App\Modele;

use App\Utilitaire\Singleton_ConnexionPDO_LOG;
use PDO;

class Modele_Log
{
    static function Realiser_Ajouter($idUtilisateur, $idTypeAction, $idObjet)
    {
        $connexionPDO_LOG = Singleton_ConnexionPDO_LOG::getInstance();

        $requetePreparee = $connexionPDO_LOG->prepare(
            'insert into ... ');
               $dateHeure = date("Y-m-d H:i:s");
        $requetePreparee->bindParam('...', ...);

                $reponse = $requetePreparee->execute();
    }
}