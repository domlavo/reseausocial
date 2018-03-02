<?php

require 'utilisateur.php';

final class Persistance {

  private $dbName = 'domlavo';
  private $usernameBD = 'domlavo';
  private $passwordBD = '0839284';
  private $db;

  public static function Instance()
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new Persistance();
    }
    return $inst;
  }

  private function __construct()
  {
    try
    {
      $this->db = new PDO('mysql:host=info10.cegepthetford.ca;dbname='. $this->dbName, $this->usernameBD, $this->passwordBD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOexception $e)
    {
      echo 'Erreur SQL : ' . $e->getMessage() . '<br />';
    }
  }

  public function recupererUtilisateur($loginID) {
    $sql = "SELECT  * FROM utilisateur WHERE loginID='$loginID';";
    $resultat = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if( !empty($resultat) ) {
      $utilisateur = new Utilisateur($resultat[0]['nom'], $resultat[0]['prenom'], $resultat[0]['nb_session'], $resultat[0]['loginID'], $resultat[0]['fk_specialite']);
      $utilisateur->setId($resultat[0]['pk_utilisateur']);
      return $utilisateur;
    }
    echo $sql;
    die();
    return null;
  }

  public function ajouterUtilisateur($utilisateur) {

    if( !is_a($utilisateur, 'Utilisateur') || !$utilisateur->estValide() )
      return false;

    try {
      $sql = "INSERT INTO utilisateur (nom, prenom, nb_session, loginID, fk_specialite)
      VALUES ('$utilisateur->nom','$utilisateur->prenom',$utilisateur->nb_session,'$utilisateur->loginID',$utilisateur->specialite);";
      $stmt = $this->db->prepare($sql);
      $stmt->execute();
    } catch(Exception $e){
      return false;
    }
    return true;

  }

}

?>
