<?php

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

if($_POST['action'] == 'ajouterPublication' || $_POST['action'] == 'ajouterCommentaire') {
  require_once './library/HTMLPurifier.auto.php';
}
$allowed_tags = 'a, abbr, acronym, b, blockquote, caption, cite, code, dd, del, dfn, div, dl, dt, em, i, ins, kbd, li, ol, p, pre, s, strike, strong, sub, sup, table, tbody, td, tfoot, th, thead, tr, tt, u, ul, var';

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
          'question' => '',
          'detail' => ''
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

    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowed_tags);
    $purifier = new HTMLPurifier($config);
    try {
      if($type == 1)
        $clean_textePublication = $purifier->purify($textePublication);
        $publication = new Publication($clean_textePublication, $type, $utilisateur);
      if($type == 2) {
        $clean_detail = $purifier->purify($detail);
        $publication = new Question($textePublication, $type, $utilisateur);
        $publication->setNbReponse(0);
        $publication->detail = $clean_detail;
      }
      if($type == 3) {
        $question = recupererPersistance()->recupererQuestion($question, $utilisateur);
        if( !$question ) {
          echo json_encode($reponse);
          die();
        }
        $clean_textePublication = $purifier->purify($textePublication);
        $publication = new Reponse($clean_textePublication, $type, $utilisateur, $question->id);
        $publication->utilisateurQuestion = $question->utilisateur;
        $question->nbReponse++;
        $reponse['nbReponse'] = htmlspecialchars($question->formatterNbReponse());
      }
      $publication->setDateCreation( date("Y-m-d H:i:s", time()) );
    } catch(Exception $e) {
      $reponse['error'] = $e->getMessage();
      echo json_encode($reponse);
      die();
    }

    $newPublication = recupererPersistance()->ajouterBD($publication);
    if(!is_a($newPublication, 'Publication')) {
      echo json_encode($reponse);
      die();
    } else {
      $reponse['status'] = 'success';
      $reponse['publication'] = htmlspecialchars($newPublication->afficher($utilisateur, true));
      $reponse['type'] = $type;
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

    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', $allowed_tags);
    $purifier = new HTMLPurifier($config);
    try {
      $clean_texteCommentaire = $purifier->purify($texteCommentaire);
      $publication = new Commentaire($clean_texteCommentaire, 1, $utilisateur, $publication);
      $publication->setDateCreation( date("Y-m-d H:i:s", time()) );
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
      $publication = new Publication("nil", 1, $utilisateur);
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
          'isActive' => ''
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

    $question = recupererPersistance()->recupererQuestion($pubid, $utilisateur);
    if( !$question ) {
      echo json_encode($reponse);
      die();
    }

    if( !$utilisateur->equals($question->utilisateur) ) {
      echo json_encode($reponse);
      die();
    }

    $isActive = $isActive === 'true';

    if( recupererPersistance()->selectionnerReponse($pubid, $repid, $isActive) ) {
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
