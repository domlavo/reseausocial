<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if( !isset($_POST['action']) ) {
  echo "error";
  die();
}

$handler = array( 'Ajax', $_POST['action']);
if ( !is_callable($handler) ) {
  echo "error";
  die();
}

require_once 'persistance.php';
require_once 'helper.php';
require_once 'publication.php';

$params = $_POST;
call_user_func( $handler , $params );

class Ajax {

  public function __construct(){}

  public static function ajouterPublication(array $params = array()) {
    extract(
      extractArgs(
        array(
        	'textePublication' => '',
          'type' => '',
          'question' => ''
        ),
        $params
      )
    );

    $reponse = array('status' => 'error');
    $utilisateur = verifierConnection();
    if(!$utilisateur) {
      echo json_encode($reponse);
      die();
    }

    try {
      if($type == 1)
        $publication = new Publication($textePublication, $type, $utilisateur);
      if($type == 2) {
        $publication = new Question($textePublication, $type, $utilisateur);
        $publication->setNbReponse(0);
        $publication->setDateCreation( date("Y-m-d H:i:s", time()) );
      }
      if($type == 3) {
        $publication = new Reponse($textePublication, $type, $utilisateur, $question);
      }
    } catch(Exception $e) {
      echo json_encode($reponse);
      die();
    }

    $newPublication = recupererPersistance()->ajouterBD($publication);
    if(!is_a($newPublication, 'Publication')) {
      $reponse['status'] = $newPublication;
      echo json_encode($reponse);
      die();
    } else {
      $reponse['status'] = 'success';
      $reponse['publication'] = htmlspecialchars($newPublication->afficher($utilisateur, true));
      echo json_encode($reponse);
      die();
    }
  }

  public static function ajouterCommentaire(array $params = array()) {
    extract(
      extractArgs(
        array(
          'texteCommentaire' => '',
          'publication' => '',
        ),
        $params
      )
    );

    $reponse = array('status' => 'error');
    $utilisateur = verifierConnection();
    if(!$utilisateur) {
      echo json_encode($reponse);
      die();
    }

    try {
      $publication = new Commentaire($texteCommentaire, 1, $utilisateur, $publication);
    } catch(Exception $e) {
      echo json_encode($reponse);
      die();
    }

    $newPublication = recupererPersistance()->ajouterBD($publication);
    if(!is_a($newPublication, 'Publication')) {
      $reponse['status'] = $newPublication;
      echo json_encode($reponse);
      die();
    } else {
      $reponse['status'] = 'success';
      $reponse['publication'] = htmlspecialchars($newPublication->afficher($utilisateur, true));
      echo json_encode($reponse);
      die();
    }
  }

  public static function supprimerPublication(array $params = array()) {
    extract(
      extractArgs(
        array(
          'idSupprimerPublication' => '',
        ),
        $params
      )
    );

    $reponse = array('status' => 'error');
    $utilisateur = verifierConnection();
    if(!$utilisateur) {
      echo json_encode($reponse);
      die();
    }

    $publication = null;
    try {
      $publication = new Publication("", 1, $utilisateur);
      $publication->setId($idSupprimerPublication);
    } catch(Exception $e) {
      echo json_encode($reponse);
      die();
    }

    if(recupererPersistance()->supprimerBD($publication) == "true") {
      $reponse["status"] = "success";
      $reponse["publication"] = "publication-block-".$idSupprimerPublication;
      echo json_encode($reponse);
      die();
    } else {
      echo json_encode($reponse);
      die();
    }
  }

  public static function likePublication(array $params = array()) {
    extract(
      extractArgs(
        array(
          'pubid' => '',
          'vote' => '',
          'isActive' => '',
        ),
        $params
      )
    );

    $reponse = array('status' => 'error');
    if($vote != 1 && $vote != 0 && $vote != -1) {
      echo json_encode($reponse);
      die();
    }

    $utilisateur = verifierConnection();
    if(!$utilisateur) {
      echo json_encode($reponse);
      die();
    }

    if( $vote != 1 && $vote != -1 ) {
      echo json_encode($reponse);
      die();
    }

    if( $isActive === 'true' )
      $vote = 0;

    $retourVote = recupererPersistance()->votePublication($pubid, $utilisateur->id, $vote);

    if( $retourVote !== false ) {
      $reponse = array('status' => 'success');
      $reponse['vote'] = $vote;
      if($retourVote > 0)
        $retourVote = "+" . $retourVote;
      $reponse['nbVote'] = $retourVote;
      echo json_encode($reponse);
      die();
    }
    echo json_encode($reponse);
    die();

  }

  public static function selectionnerReponse(array $params = array()) {
    extract(
      extractArgs(
        array(
          'pubid' => '',
          'repid' => '',
        ),
        $params
      )
    );

    $reponse = array('status' => 'error');
    $utilisateur = verifierConnection();
    if(!$utilisateur) {
      echo json_encode($reponse);
      die();
    }

    $question = recupererPersistance()->recupererQuestion($pubid);
    if( !$question ) {
      echo json_encode($reponse);
      die();
    }

    if( !$utilisateur->equals($question->utilisateur) ) {
      echo json_encode($reponse);
      die();
    }

    if( recupererPersistance()->selectionnerReponse($pubid, $repid) ) {
      $reponse = array('status' => 'success');
      $reponse['reponse'] = $repid;
      echo json_encode($reponse);
      die();
    }

    echo json_encode($reponse);
    die();

  }

}

function extractArgs( $pairs, $atts ) {
  $atts = (array)$atts;
  $out = array();
  foreach ($pairs as $name => $default) {
      if ( array_key_exists($name, $atts) )
          $out[$name] = $atts[$name];
      else
          $out[$name] = $default;
  }
  return $out;
}

?>
