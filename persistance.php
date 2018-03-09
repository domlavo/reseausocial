<?php

require_once 'utilisateur.php';
require_once 'publication.php';

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
    return false;
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

  public function recupererPublication($utilisateur) {

    if( !is_a($utilisateur, 'Utilisateur') )
      return false;

    $requete = "SELECT 	pub.pk_publication AS 'idPub', pub.texte AS 'textePub', pub.fk_type_publication AS 'type',
                    		pub.fk_specialite AS 'specialite', pub.date_creation AS 'datePub',
                        pubUser.pk_utilisateur AS 'userPubId', pubUser.nom AS 'userPubNom', pubUser.prenom AS 'userPubPrenom',
                        pubUser.nb_session AS 'userPubNbS', pubUser.loginID AS 'userPubLog', pubUser.fk_specialite AS 'userPubSpe',
                        com.pk_publication AS 'idCom', com.texte AS 'texteCom', com.fk_utilisateur AS 'utilisateurCom',
                        com.date_creation AS 'dateCom', comUser.pk_utilisateur AS 'userComId', comUser.nom AS 'userComNom',
                        comUser.prenom AS 'userComPrenom', comUser.nb_session AS 'userComNbS', comUser.loginID AS 'userComLog',
                        comUser.fk_specialite AS 'userComSpe'
                FROM publication pub
                LEFT JOIN utilisateur pubUser
                	ON pub.fk_utilisateur = pubUser.pk_utilisateur
                LEFT JOIN publication com
                	ON pub.pk_publication = com.fk_publication
                LEFT JOIN utilisateur comUser
                	ON com.fk_utilisateur = comUser.pk_utilisateur
                WHERE pub.fk_utilisateur = ? AND pub.fk_publication IS NULL
                ORDER BY pub.date_creation DESC, com.date_creation ASC;";
    $valeurs = array( $utilisateur->id );

    $resultat = array();
    try {
      $stmt = $this->db->prepare($requete);
      $stmt->execute($valeurs);
      $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e){
      return false;
    }

    $publications = array();
    foreach ($resultat as $value) {

      try {
        if( !array_key_exists($value['idPub'], $publications) ) {
          $user = new Utilisateur($value['userPubNom'], $value['userPubPrenom'], $value['userPubNbS'], $value['userPubLog'], $value['userPubSpe']);
          $user->setId($value['userPubId']);
          $publication = new Publication($value['textePub'], $value['type'], $user, null, $value['specialite']);
          $publication->setId($value['idPub']);
          $publications[$value['idPub']] = $publication;
        }
        if($value['idCom'] != null) {
          $user = new Utilisateur($value['userComNom'], $value['userComPrenom'], $value['userComNbS'], $value['userComLog'], $value['userComSpe']);
          $user->setId($value['userComId']);
          $commentaire = new Commentaire($value['texteCom'], $value['type'], $user, $value['idPub'], $value['specialite']);
          $commentaire->setId($value['idCom']);
          $publications[$value['idPub']]->ajouterCommentaire($commentaire);
        }
      } catch (Exception $e) {}

    }

    return $publications;

  }

  public function ajouterBD($objet) {

    if( !is_a($objet, 'IAjouter') )
      return false;

    $params = $objet->requeteAjouter();
    if(!$params)
      return false;

    $id = null;
    try {
      $stmt = $this->db->prepare($params['requete']);
      $stmt->execute($params['valeurs']);
      $id = $this->db->lastInsertId();
    } catch(Exception $e){
      return $e->getMessage();
    }

    $objet->setId($id);
    return $objet;

  }

  public function supprimerBD($objet) {

    if( !is_a($objet, 'ISupprimer') )
      return false;

    $params = $objet->requeteSupprimer();
    if(!$params)
      return false;

    try {
      $stmt = $this->db->prepare($params['requete']);
      $stmt->execute($params['valeurs']);
    } catch(Exception $e){
      return $e->getMessage();
    }

    return true;

  }

}

?>
