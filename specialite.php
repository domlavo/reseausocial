<?php

class Specialite {

  public $id;
  public $nom;

  public function __construct($nom)
  {
    $this->nom = $nom;
  }

  final public function setId($id) {
    $this->id = $id;
  }

  public function afficherOption() {
    ob_start();
    ?>
    <option value="<?= $this->id ?>"><?= $this->nom ?></option>
    <?php
    return ob_get_clean();
  }

}

?>
