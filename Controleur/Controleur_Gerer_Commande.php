<?php

use App\Modele\Modele_Commande;
use App\Modele\Modele_Entreprise;
use App\Vue\Vue_Action_Sur_Commande_Entreprise;
use App\Vue\Vue_Menu_Administration;
use App\Vue\Vue_Commande_Etat;
use App\Vue\Vue_Panier_Client;
use App\Vue\Vue_Commande_Histo;
use App\Vue\Vue_Commande_Info;
use App\Vue\Vue_Commande_Liste;
use App\Vue\Vue_Structure_Entete;

$Vue->setEntete(new Vue_Structure_Entete());
$Vue->setMenu(new Vue_Menu_Administration($_SESSION["niveauAutorisation"]));

$listeEtatCommande = Modele_Commande::EtatCommande_Liste();
$Vue->addToCorps(new Vue_Commande_Etat($listeEtatCommande));

switch ($action) {
    case "boutonCategorie":
        //On a demandé les commandes d'une catégorie
        $idEtatcommande = $_REQUEST["idEtatCommande"];
        $listeCommande = Modele_Commande::Commande_Select_Par_Etat($idEtatcommande);
        $Vue->addToCorps(new Vue_Commande_Liste($listeCommande));
        break;
    case "Toute" :
        $listeCommande = Modele_Commande::Commande_Select_Toute();
        $Vue->addToCorps(new Vue_Commande_Liste($listeCommande));
        break;
    case "VoirDetailCommande":
        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));
        break;
    case "VerifierVirement":
        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));

        //récupération des informations de la commande
         $infoEntreprise = Modele_Entreprise::Entreprise_Select_ParId($infoCommande["idEntreprise"]);

        //Rappel une référence attendue est sous la forme :
        // Brulerie_<NomEntreprise>_<cleprimaireentreprise>_<cleprimairecommande>
        //Ce n'est pas oufff !!!
        $referenceAttendue = "Brulerie_" . $infoEntreprise["denomination"] . "_" . $infoCommande["idEntreprise"] . "_" . $infoCommande["id"];

        //Pour interroger le serveur de banque, on va utiliser curl pour récupérer notre token
        $url = "http://127.0.0.1:8000/authentication_token";
        $ch = curl_init();
        $headers  = [
            'Content-Type: application/json'
        ];
        $postData = [
            'email' => 'jba2',
            'password' => 'secretsecret'
        ];
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result     = curl_exec ($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($statusCode == 200) {
            $tokenEnObjet = json_decode($result);
            $token = $tokenEnObjet->token;


            curl_close($ch);
            $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("Référence virement attendue : " . $referenceAttendue));

            //on va interroger le serveur de banque pour savoir si on a reçu un virement avec cette référence et son montant
            //en s'authentifiant avec le token reçu !

            $url = "http://127.0.0.1:8000/apisec/virement/1111111111/$referenceAttendue";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);

            $headers = array(
                "Content-Type: application/json",
                "Authorization: Bearer $token"
            );
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $resultat = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("<div>Réponse reçue : " . $referenceAttendue."</div>"));
                $resultatEnObjet = json_decode($resultat);
                $montantVirement = $resultatEnObjet->montant;
                if ($montantVirement == $infoCommande["montantCommande"]) {
                    //On a reçu le bon montant, on peut passer la commande à l'état payée !
                    Modele_Commande::HistoriqueEtatCommande_Inserer($_REQUEST["idCommande"], 3, "Virement reçu", -1, $_SESSION["idUtilisateur"]);

                    $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("Montant OK, Commande en préparation !"));
                } else {
                    $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("Pas bon montant : contacter client !"));
                }
            } else {
                $Vue->addToCorps(new \App\Vue\Vue_AfficherMessage("<div>PAS TROUVE : Référence virement attendue : " . $referenceAttendue ."</div>"));

            }
        }
       // die($result);
        /*
        //On va interroger en curl le serveur de banque pour savoir si on a reçu un virement avec cette référence et son montant !
        $url = "http://127.0.0.1:8000/verifierVirement.php?reference=" . $referenceAttendue . "&montant=" . $infoCommande["montantCommande"];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $resultat = curl_exec($curl);*/

        break;
    case "Signaler_CommandePayee":
        \App\Modele\Modele_Log::Realiser_Ajouter($_SESSION["idUtilisateur"],1,$_REQUEST["idCommande"]);
        if (isset($_REQUEST["info"]))
            $infoComplementaire = $_REQUEST["info"];
        else
            $infoComplementaire = "";
        Modele_Commande::HistoriqueEtatCommande_Inserer($_REQUEST["idCommande"], 3, $infoComplementaire, -1, $_SESSION["idUtilisateur"]);

        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));
        break;
    case "Signalee_CommandeEnPreparation":
        if (isset($_REQUEST["info"]))
            $infoComplementaire = $_REQUEST["info"];
        else
            $infoComplementaire = "";
        Modele_Commande::HistoriqueEtatCommande_Inserer($_REQUEST["idCommande"], 4, $infoComplementaire, -1, $_SESSION["idUtilisateur"]);
        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));
        break;
    case "Signalee_CommandeProblemeStock":
        if (isset($_REQUEST["info"]))
            $infoComplementaire = $_REQUEST["info"];
        else
            $infoComplementaire = "";
        Modele_Commande::HistoriqueEtatCommande_Inserer($_REQUEST["idCommande"], 5, $infoComplementaire, -1, $_SESSION["idUtilisateur"]);
        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));
        break;
    case "Signalee_CommandeEnvoyée":
        if (isset($_REQUEST["info"]))
            $infoComplementaire = $_REQUEST["info"];
        else
            $infoComplementaire = "";
        Modele_Commande::HistoriqueEtatCommande_Inserer($_REQUEST["idCommande"], 6, $infoComplementaire, -1, $_SESSION["idUtilisateur"]);
        $listeArticleCommande = Modele_Commande::Commande_Avoir_Article_Select_ParIdCommande($_REQUEST["idCommande"]);
        $infoCommande = Modele_Commande::Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $histoEtatCommande = Modele_Commande::Historique_Etat_Commande_Select_ParIdCommande($_REQUEST["idCommande"]);
        $Vue->addToCorps(new Vue_Panier_Client($listeArticleCommande, true, $infoCommande));
        $Vue->addToCorps(new Vue_Action_Sur_Commande_Entreprise($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Info($infoCommande));
        $Vue->addToCorps(new Vue_Commande_Histo($histoEtatCommande));
        break;
}
