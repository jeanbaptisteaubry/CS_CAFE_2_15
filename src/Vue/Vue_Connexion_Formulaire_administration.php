<?php
namespace App\Vue;
use App\Utilitaire\Vue_Composant;

class Vue_Connexion_Formulaire_administration extends Vue_Composant
{
    private string $msgErreur;
    public function __construct(string $msgErreur="")
    {
        $this->msgErreur=$msgErreur;
    }

    function donneTexte(): string
    {
        $str= "  
                <h1>Adminsitration : Connexion</h1>
  <form action='".$GLOBALS["adminFileName"]."' method='post' style='    width: 50%;    display: block;    margin: auto;'>
".genereChampHiddenCSRF()."
                
                <label><b>Identifiant de connexion </b></label>
                <input type='text' placeholder='login' name='login' required>

                <label><b>Mot de passe</b></label>
                <input type='password' placeholder='mot de passe' name='password' required>
                
                <input type='hidden'  name='case' value='Cas_Par_Defaut'>
                <button type='submit' id='submit' name='action' value='Se connecter'>
                    Se connecter
                </button>";
        if($this->msgErreur != "")
        {
            $str .=  " <label><b>Erreur : $this->msgErreur</b></label>";
        }

        $str .=  "
</form>";
        return $str;
    }
}