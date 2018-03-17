<?php

require_once 'utilisateur.php';
require_once 'publication.php';
require_once 'helper.php';

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
      $this->db = new PDO('mysql:host=206.167.23.182;dbname='. $this->dbName, $this->usernameBD, $this->passwordBD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
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

  public function recupererPublication($profile, $utilisateur, $type) {

    if( !is_a($utilisateur, 'Utilisateur') || !is_a($profile, 'Utilisateur') )
      return false;

    $requete = "SELECT 	pub.pk_publication AS 'idPub', pub.texte AS 'textePub', pub.fk_type_publication AS 'type',
                    		pub.fk_specialite AS 'specialite', pub.date_creation AS 'datePub', pubVote.valeur AS 'votePub', pubUserVote.valeur AS 'votePubUser',
                    		pubUser.pk_utilisateur AS 'userPubId', pubUser.nom AS 'userPubNom', pubUser.prenom AS 'userPubPrenom',
                    		pubUser.nb_session AS 'userPubNbS', pubUser.loginID AS 'userPubLog', pubUser.fk_specialite AS 'userPubSpe',
                    		com.pk_publication AS 'idCom', com.texte AS 'texteCom', com.fk_utilisateur AS 'utilisateurCom',
                    		com.date_creation AS 'dateCom', comUser.pk_utilisateur AS 'userComId', comUser.nom AS 'userComNom',
                    		comUser.prenom AS 'userComPrenom', comUser.nb_session AS 'userComNbS', comUser.loginID AS 'userComLog',
                    		comUser.fk_specialite AS 'userComSpe', comVote.valeur AS 'voteCom', comUserVote.valeur AS 'voteComUser'
                    FROM publication pub
                    LEFT JOIN utilisateur pubUser
                    	ON pub.fk_utilisateur = pubUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) pubVote
                    	ON pub.pk_publication = pubVote.fk_publication
                    LEFT JOIN vote pubUserVote
                    	ON pub.pk_publication = pubUserVote.fk_publication AND pubUserVote.fk_utilisateur = ?
                    LEFT JOIN publication com
                    	ON pub.pk_publication = com.fk_publication
                    LEFT JOIN utilisateur comUser
                    	ON com.fk_utilisateur = comUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) comVote
                    	ON com.pk_publication = comVote.fk_publication
                    LEFT JOIN vote comUserVote
                    	ON com.pk_publication = comUserVote.fk_publication AND comUserVote.fk_utilisateur = ?
                    WHERE pub.fk_utilisateur = ? AND pub.fk_publication IS NULL AND pub.fk_type_publication = ?
                    ORDER BY pub.date_creation DESC, com.date_creation ASC;";
    $valeurs = array( $utilisateur->id, $utilisateur->id, $profile->id, $type );

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
          $publication->setDateCreation($value['datePub']);
          $publication->setNbVotes($value['votePub']);
          $publication->setVoteUtilisateur($value['votePubUser']);
          $publications[$value['idPub']] = $publication;
        }
        if($value['idCom'] != null) {
          $user = new Utilisateur($value['userComNom'], $value['userComPrenom'], $value['userComNbS'], $value['userComLog'], $value['userComSpe']);
          $user->setId($value['userComId']);
          $commentaire = new Commentaire($value['texteCom'], $value['type'], $user, $value['idPub'], $value['specialite']);
          $commentaire->setId($value['idCom']);
          $commentaire->setDateCreation($value['dateCom']);
          $commentaire->setNbVotes($value['voteCom']);
          $commentaire->setVoteUtilisateur($value['voteComUser']);
          $publications[$value['idPub']]->ajouterCommentaire($commentaire);
        }
      } catch (Exception $e) {}

    }

    return $publications;

  }

  public function recupererQuestions($profile) {

    if( !is_a($profile, 'Utilisateur') )
      return false;

    $requete = "SELECT 	pub.pk_publication AS 'idPub', pub.texte AS 'textePub', pub.fk_type_publication AS 'type', pub.fk_reponse AS 'reponse',
                    		pub.fk_specialite AS 'specialite', pub.date_creation AS 'datePub', pubVote.valeur AS 'votePub', reponse.nbReponse AS 'nbReponse',
                    		pubUser.pk_utilisateur AS 'userPubId', pubUser.nom AS 'userPubNom', pubUser.prenom AS 'userPubPrenom',
                    		pubUser.nb_session AS 'userPubNbS', pubUser.loginID AS 'userPubLog', pubUser.fk_specialite AS 'userPubSpe'
                    FROM publication pub
                    LEFT JOIN utilisateur pubUser
                    	ON pub.fk_utilisateur = pubUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) pubVote
                    	ON pub.pk_publication = pubVote.fk_publication
                    LEFT JOIN (SELECT fk_publication, COUNT(fk_publication) AS 'nbReponse' FROM publication GROUP BY fk_publication) reponse
                    	ON pub.pk_publication = reponse.fk_publication
                    WHERE pub.fk_utilisateur = ? AND pub.fk_publication IS NULL AND pub.fk_type_publication = 2
                    ORDER BY pub.date_creation DESC;";
    $valeurs = array( $profile->id );

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
          $publication = new Question($value['textePub'], $value['type'], $user, null, $value['specialite']);
          $publication->setId($value['idPub']);
          $publication->setDateCreation($value['datePub']);
          $publication->setNbReponse($value['nbReponse']);
          $publication->aBonneReponse = ($value['reponse'] != NULL && $value['reponse'] != '');
          $publications[$value['idPub']] = $publication;
        }
      } catch (Exception $e) {}

    }

    return $publications;

  }

  public function recupererQuestion($question, $utilisateur) {

    if( !isInt($question) )
      return false;

    $requete = "SELECT 	pub.pk_publication AS 'idPub', pub.texte AS 'textePub', pub.fk_type_publication AS 'type', pub.fk_reponse AS 'reponse',
                    		pub.fk_specialite AS 'specialite', pub.date_creation AS 'datePub', pubVote.valeur AS 'votePub', pubUserVote.valeur AS 'votePubUser',
                        reponse.nbReponse AS 'nbReponse', pubUser.pk_utilisateur AS 'userPubId', pubUser.nom AS 'userPubNom',
                        pubUser.prenom AS 'userPubPrenom', pub.detail_question AS 'detail_question',
                    		pubUser.nb_session AS 'userPubNbS', pubUser.loginID AS 'userPubLog', pubUser.fk_specialite AS 'userPubSpe',
                        com.pk_publication AS 'idCom', com.texte AS 'texteCom', com.fk_utilisateur AS 'utilisateurCom',
                    		com.date_creation AS 'dateCom', comUser.pk_utilisateur AS 'userComId', comUser.nom AS 'userComNom',
                    		comUser.prenom AS 'userComPrenom', comUser.nb_session AS 'userComNbS', comUser.loginID AS 'userComLog',
                    		comUser.fk_specialite AS 'userComSpe', comVote.valeur AS 'voteCom', comUserVote.valeur AS 'voteComUser'
                    FROM publication pub
                    LEFT JOIN utilisateur pubUser
                    	ON pub.fk_utilisateur = pubUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) pubVote
                    	ON pub.pk_publication = pubVote.fk_publication
                    LEFT JOIN vote pubUserVote
                    	ON pub.pk_publication = pubUserVote.fk_publication AND pubUserVote.fk_utilisateur = ?
                    LEFT JOIN publication com
                    	ON pub.pk_publication = com.fk_publication AND com.fk_type_publication = 1
                    LEFT JOIN utilisateur comUser
                    	ON com.fk_utilisateur = comUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) comVote
                    	ON com.pk_publication = comVote.fk_publication
                    LEFT JOIN vote comUserVote
                    	ON com.pk_publication = comUserVote.fk_publication AND comUserVote.fk_utilisateur = ?
                    LEFT JOIN (SELECT fk_publication, COUNT(fk_publication) AS 'nbReponse' FROM publication GROUP BY fk_publication) reponse
                    	ON pub.pk_publication = reponse.fk_publication
                    WHERE pub.pk_publication = ? AND pub.fk_publication IS NULL AND pub.fk_type_publication = 2
                    ORDER BY com.date_creation ASC;";
    $valeurs = array( $utilisateur->id, $utilisateur->id, $question );

    $resultat = array();
    try {
      $stmt = $this->db->prepare($requete);
      $stmt->execute($valeurs);
      $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e){
      return false;
    }

    $publications = array();
    $idPub;
    foreach ($resultat as $value) {
      $idPub = $value['idPub'];
      try {
        if( !array_key_exists($value['idPub'], $publications) ) {
          $user = new Utilisateur($value['userPubNom'], $value['userPubPrenom'], $value['userPubNbS'], $value['userPubLog'], $value['userPubSpe']);
          $user->setId($value['userPubId']);
          $publication = new Question($value['textePub'], $value['type'], $user, null, $value['specialite']);
          $publication->setId($value['idPub']);
          $publication->setDateCreation($value['datePub']);
          $publication->setNbVotes($value['votePub']);
          $publication->setVoteUtilisateur($value['votePubUser']);
          $publication->setNbReponse($value['nbReponse']);
          $publication->detail_question = $value['detail_question'];
          $publications[$value['idPub']] = $publication;
        }
        if($value['idCom'] != null) {
          $user = new Utilisateur($value['userComNom'], $value['userComPrenom'], $value['userComNbS'], $value['userComLog'], $value['userComSpe']);
          $user->setId($value['userComId']);
          $commentaire = new Commentaire($value['texteCom'], $value['type'], $user, $value['idPub'], $value['specialite']);
          $commentaire->setId($value['idCom']);
          $commentaire->setDateCreation($value['dateCom']);
          $commentaire->setNbVotes($value['voteCom']);
          $commentaire->setVoteUtilisateur($value['voteComUser']);
          $publications[$value['idPub']]->ajouterCommentaire($commentaire);
        }
      } catch (Exception $e) {}

    }

    if(empty($publications)) {
      return false;
    }

    return $publications[$idPub];

  }

  public function recupererReponse($utilisateur, $question) {

    if( !is_a($utilisateur, 'Utilisateur') || !is_a($question, 'Question') )
      return false;

    $requete = "SELECT 	pub.pk_publication AS 'idPub', pub.texte AS 'textePub', pub.fk_type_publication AS 'type',
                    		pub.fk_specialite AS 'specialite', pub.date_creation AS 'datePub', pubVote.valeur AS 'votePub', pubUserVote.valeur AS 'votePubUser',
                    		pubUser.pk_utilisateur AS 'userPubId', pubUser.nom AS 'userPubNom', pubUser.prenom AS 'userPubPrenom',
                    		pubUser.nb_session AS 'userPubNbS', pubUser.loginID AS 'userPubLog', pubUser.fk_specialite AS 'userPubSpe',
                    		com.pk_publication AS 'idCom', com.texte AS 'texteCom', com.fk_utilisateur AS 'utilisateurCom',
                    		com.date_creation AS 'dateCom', comUser.pk_utilisateur AS 'userComId', comUser.nom AS 'userComNom',
                    		comUser.prenom AS 'userComPrenom', comUser.nb_session AS 'userComNbS', comUser.loginID AS 'userComLog',
                    		comUser.fk_specialite AS 'userComSpe', comVote.valeur AS 'voteCom', comUserVote.valeur AS 'voteComUser',
                        (SELECT fk_reponse FROM publication WHERE pk_publication = ?) AS 'reponse'
                    FROM publication pub
                    LEFT JOIN utilisateur pubUser
                    	ON pub.fk_utilisateur = pubUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) pubVote
                    	ON pub.pk_publication = pubVote.fk_publication
                    LEFT JOIN vote pubUserVote
                    	ON pub.pk_publication = pubUserVote.fk_publication AND pubUserVote.fk_utilisateur = ?
                    LEFT JOIN publication com
                    	ON pub.pk_publication = com.fk_publication
                    LEFT JOIN utilisateur comUser
                    	ON com.fk_utilisateur = comUser.pk_utilisateur
                    LEFT JOIN (SELECT fk_publication, SUM(valeur) AS 'valeur' FROM vote GROUP BY fk_publication) comVote
                    	ON com.pk_publication = comVote.fk_publication
                    LEFT JOIN vote comUserVote
                    	ON com.pk_publication = comUserVote.fk_publication AND comUserVote.fk_utilisateur = ?
                    WHERE pub.fk_publication = ? AND pub.fk_type_publication = 3
                    ORDER BY pub.date_creation DESC, com.date_creation ASC;";

    $valeurs = array( $question->id, $utilisateur->id, $utilisateur->id, $question->id );

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
          $publication = new Reponse($value['textePub'], $value['type'], $user, null, $value['specialite']);
          $publication->setId($value['idPub']);
          $publication->setDateCreation($value['datePub']);
          $publication->setNbVotes($value['votePub']);
          $publication->setVoteUtilisateur($value['votePubUser']);
          if($value['reponse'] == $value['idPub'])
            $publication->estReponse = true;
          $publication->utilisateurQuestion = $question->utilisateur;
          $publications[$value['idPub']] = $publication;
        }
        if($value['idCom'] != null) {
          $user = new Utilisateur($value['userComNom'], $value['userComPrenom'], $value['userComNbS'], $value['userComLog'], $value['userComSpe']);
          $user->setId($value['userComId']);
          $commentaire = new Commentaire($value['texteCom'], $value['type'], $user, $value['idPub'], $value['specialite']);
          $commentaire->setId($value['idCom']);
          $commentaire->setDateCreation($value['dateCom']);
          $commentaire->setNbVotes($value['voteCom']);
          $commentaire->setVoteUtilisateur($value['voteComUser']);
          $publications[$value['idPub']]->ajouterCommentaire($commentaire);
        }
      } catch (Exception $e) {}

    }

    return $publications;

  }

  public function recupereAutreUtilisateur($utilisateur) {

    $requete = "SELECT * FROM utilisateur WHERE pk_utilisateur <> ?;";
    $valeurs = array( $utilisateur->id );
    try {
      $stmt = $this->db->prepare($requete);
      $stmt->execute($valeurs);
      $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e){
      return false;
    }

    $utilisateurs = array();
    foreach ($resultat as $value) {
      $utilisateur = new Utilisateur($value['nom'], $value['prenom'], $value['nb_session'], $value['loginID'], $value['fk_specialite']);
      $utilisateur->setId($value['pk_utilisateur']);
      $utilisateurs[] = $utilisateur;
    }

    if(!empty($utilisateurs)) {
      return $utilisateurs;
    }
    return false;

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

  public function votePublication($pubId, $utilisateur, $vote) {

    try {
      $stmt = $this->db->prepare("INSERT INTO vote (fk_publication, fk_utilisateur, valeur)
                                  VALUES (?, ?, ?);");
      $stmt->execute(array($pubId, $utilisateur, $vote));
    } catch(Exception $e){
      try {
        $stmt = $this->db->prepare("UPDATE vote SET valeur = ?
                                    WHERE fk_publication = ? AND fk_utilisateur = ?;");
        $stmt->execute(array($vote, $pubId, $utilisateur));
      } catch(Exception $e){
        return false;
      }
    }

    $resultat = array();
    try {
      $stmt = $this->db->prepare("SELECT fk_publication, SUM(valeur) AS 'nbVote' FROM vote WHERE fk_publication = ? GROUP BY fk_publication;");
      $stmt->execute(array($pubId));
      $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(Exception $e){
      return false;
    }

    if(empty($resultat)) {
      return false;
    }

    return $resultat[0]['nbVote'];

  }

  public function selectionnerReponse($pubId, $repId, $isActive) {

    if($isActive)
      $repId = NULL;
    try {
      $stmt = $this->db->prepare("UPDATE publication SET fk_reponse = ? WHERE pk_publication = ?;");
      $stmt->execute(array($repId, $pubId));
    } catch(Exception $e){
      return false;
    }

    return true;

  }

}

?>
