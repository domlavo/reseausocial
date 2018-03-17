<?php

require_once 'helper.php';

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
    $int = isInt($this->nb_session) && isInt($this->loginID) && isInt($this->specialite->id);
    $hasValue = $this->nom != "" && $this->prenom != "" && $this->nb_session != "" && $this->loginID != "" && $this->specialite->id != "";
    return $int && $hasValue;
  }

  public function afficher() {
    ob_start();
    ?>
    <div class="profile-banner" style="background-image: url(https://www.splitshire.com/wp-content/uploads/2014/11/SplitShire-03692-1800x1200.jpg);">
      <div class="profile-avatar">
        <img class="profile-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      </div>
      <div class="profile-nom">
        <h3><?= $this->prenom . ' ' . $this->nom ?></h3>
      </div>
      <div class="profile-stat-group">
        <p class="profile-stat"><?= $this->nb_session ?></p>
        <p>Nombre de sessions</p>
      </div>
      <div class="profile-stat-group">
        <p class="profile-stat"><?= $this->specialite->nom ?></p>
        <p>Spécialité</p>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  public function afficherListe() {
    ob_start();
    ?>
    <li class="sidebar-utilisateur-bloc">
      <a href="profile.php?utilisateur=<?= $this->loginID ?>">
        <div class="sidebar-utilisateur-avatar">
          <img class="sidebar-utilisateur-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
        </div>
        <div class="sidebar-utilisateur-nom">
          <h3><?= $this->prenom . ' ' . $this->nom ?></h3>
        </div>
      </a>
    </li>
    <?php
    return ob_get_clean();
  }

  public function equals( $autre ) {
    return is_a($autre, 'Utilisateur') && $this->loginID == $autre->loginID;
  }

}

?>
