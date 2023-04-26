<?php
include_once "vendor/autoload.php";

use App\Modele\Modele_Catalogue;
use function App\Fonctions\guidv4;

//Il me faut la liste des categories de produit
$listeCategories = Modele_Catalogue::Categorie_Select_Tous();

//Parcours de la liste des produits
foreach ($listeCategories as $categorie) {

     Modele_Catalogue::Categorie_SetUUID($categorie["idCategorie"], guidv4());
}