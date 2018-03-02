<?php

require 'helper.php';

class Utilisateur {

  public $id;
  public $nom;
  public $prenom;
  public $nb_session;
  public $loginID;
  public $specialite;

  public function __construct($nom, $prenom, $nb_session, $loginID, $specialite)
  {
    $this->nom = $nom;
    $this->prenom = $prenom;
    $this->nb_session = $nb_session;
    $this->loginID = $loginID;
    $this->specialite = $specialite;
  }

  final public function setId($id) {
    $this->id = $id;
  }

  public function estValide() {
    $int = isInt($this->nb_session) && isInt($this->loginID) && isInt($this->specialite);
    $hasValue = $this->nom != "" && $this->prenom != "" && $this->nb_session != "" && $this->loginID != "" && $this->specialite != "";
    return $int && $hasValue;
  }

  public function afficher() {
    ob_start();
    ?>
    <div class="profile-banner">
      <div class="profile-avatar">
        <img class="profile-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      </div>
      <div class="profile-nom">
        <h3><?= $this->prenom . ' ' . $this->nom ?></h3>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

}

?>
