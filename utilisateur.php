<?php

require 'helper.php';

class Utilisateur {

  public $id;
  public $nom;
  public $prenom;
  public $nb_session;
  public $loginId;
  public $specialite;

  public function __construct($nom, $prenom, $nb_session, $loginId, $specialite)
  {
    $this->nom = $nom;
    $this->prenom = $prenom;
    $this->nb_session = $nb_session;
    $this->loginId = $loginId;
    $this->specialite = $specialite;
  }

  final public function setId($id) {
    $this->id = $id;
  }

  public function estValide() {
    $int = isInt($this->nb_session) && isInt($this->loginId) && isInt($this->specialite);
    $hasValue = $this->nom != "" && $this->prenom != "" && $this->nb_session != "" && $this->loginId != "" && $this->specialite != "";
    return $int && $hasValue;
  }

}

?>
