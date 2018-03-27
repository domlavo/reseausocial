<?php
require_once 'interfaces.php';
require_once 'helper.php';

class Publication implements IAjouter, ISupprimer
{
  public $id;
  protected $texte;
  protected $type;
  public $utilisateur;
  protected $parent;
  protected $specialite;
  protected $commentaires;
  protected $nbVotes;
  protected $voteUtilisateur;
  protected $dateCreation;

  function __construct( $texte, $type, $utilisateur, $parent = null, $specialite = null )
  {
    if($texte == "")
      throw new Exception("Le texte n'est pas conforme");
    $this->texte = $texte;
    $this->type = $type;
    $this->utilisateur = $utilisateur;
    $this->parent = $parent;
    $this->specialite = $specialite;
    $this->commentaires = array();
    $this->nbVotes = 0;
    $this->voteUtilisateur = 0;
    $this->dateCreation = "";
  }

  final public function setId($id) {
    $this->id = $id;
  }

  final public function getParentId() {
    return $this->parent;
  }

  final public function setDateCreation($dateCreation) {
    try {
      $date = strtotime($dateCreation);
      $this->dateCreation = "il y a " . elapsedTime($date);
    } catch (Exception $e) {
      $this->dateCreation = "";
    }
  }

  final public function setNbVotes($nbVotes) {
    if($nbVotes == '' || $nbVotes == null)
      $this->nbVotes = 0;
    else
      $this->nbVotes = $nbVotes;
  }

  public function getNbVotes() {
    if($this->nbVotes > 0)
      return "+" . $this->nbVotes;
    return $this->nbVotes;
  }

  final public function setVoteUtilisateur($voteUtilisateur) {
    if($voteUtilisateur == '' || $voteUtilisateur == null)
      $this->voteUtilisateur = 0;
    else
      $this->voteUtilisateur = $voteUtilisateur;
  }

  public function ajouterCommentaire($commentaire) {
    $this->commentaires[] = $commentaire;
  }

  public function requeteAjouter() {
    $requete = "INSERT INTO publication
                ( texte, fk_type_publication, fk_utilisateur, fk_publication, fk_specialite)
                VALUES ( ?, ?, ?, ?, ? );";
    $valeurs = array( $this->texte, $this->type, $this->utilisateur->id, $this->parent, $this->specialite );
    return array(
      'requete' => $requete,
      'valeurs' => $valeurs
    );
  }

  public function requeteSupprimer() {
    $requete = "DELETE FROM publication
                WHERE pk_publication = ? AND fk_utilisateur = ?;";
    $valeurs = array( $this->id, $this->utilisateur->id );
    return array(
      'requete' => $requete,
      'valeurs' => $valeurs
    );
  }

  public function determinerClass($thumbs) {
    if( $thumbs == 'up' && $this->voteUtilisateur == 1 )
      return ' active';
      if( $thumbs == 'down' && $this->voteUtilisateur == -1 )
        return ' active';
      return '';
  }

  public function afficher($utilisateur, $fadeOut = false) {
    $class = $fadeOut ? ' fadeOut' : '';
    $aCommentaires = !empty($this->commentaires) ? ' aCommentaires' : '';
    ob_start();
    ?>
    <li id="publication-block-<?= $this->id ?>" class="publication-block<?= $class ?>">
      <?= $this->afficherAvatar($utilisateur); ?>
      <div class="publication-content">
        <?= $this->afficherTitre(); ?>
        <?= $this->afficherContenu(); ?>
        <?= $this->afficherActions($utilisateur); ?>
        <ul class="publication-commentaires<?= $aCommentaires ?>">
          <?php
          foreach ($this->commentaires as $commentaire) {
            echo $commentaire->afficher($utilisateur);
          }
          ?>
        </ul>
        <?= $this->afficherFormulaireCommenter(); ?>
      </div>
    </li>
    <?php
    return ob_get_clean();
  }

  protected function afficherAvatar($utilisateur) {
    ob_start();
    ?>
    <div class="publication-avatar">
      <img class="publication-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      <p class="publication-utilisateur"><strong><?= $this->utilisateur->prenom ?></strong></p>
      <p class="publication-date"><?= $this->dateCreation ?></p>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherTitre() {
    return "";
  }

  protected function afficherContenu() {
    ob_start();
    ?>
    <div class="publication-texte sans-header">
      <p><?= $this->texte ?></p>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherActions($utilisateur, $avecReply = true) {
    ob_start();
    ?>
    <div class="publication-actions" data-pubid=<?= $this->id ?>>
      <?php if($avecReply) : ?>
        <a href="#" class="fa fa-reply"></a>
      <?php endif; ?>
      <a href="#" data-vote="1" class="fa fa-thumbs-o-up vote<?= $this->determinerClass('up'); ?>"></a>
      <a href="#" data-vote="-1" class="fa fa-thumbs-o-down vote<?= $this->determinerClass('down'); ?>"></a>
      <span class="badge badge-pill badge-primary"><?= $this->getNbVotes() ?></span>
      <?php if($this->utilisateur->equals($utilisateur)) : ?>
        <a href="#" class="fa fa-trash"></a>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherFormulaireCommenter() {
    ob_start();
    ?>
    <div class="publication-commenter slideDown">
      <form class="ajouter-commentaire-form">
        <div class="form-group">
          <textarea class="form-control texteCommentaire" name="texteCommentaire" rows="3"></textarea>
        </div>
        <input type="hidden" name="publication" value="<?= $this->id ?>">
        <button type="submit" class="btn btn-primary submitCommentaire">Commenter</button>
        <button type="button" class="btn btn-light commentaireAnnuler">Annuler</button>
        <div class="clearfix"></div>
      </form>
    </div>
    <?php
    return ob_get_clean();
  }

  public static function formulaireSupprimerPublication() {
    ob_start();
    ?>
    <form id="form-supprimer-publication">
      <input type="hidden" id="idSupprimerPublication" name="idSupprimerPublication" value="">
      <input type="hidden" id="typePublication" name="typePublication" value="">
    </form>
    <?php
    return ob_get_clean();
  }
}

class Commentaire extends Publication {

  public function afficher($utilisateur, $fadeOut = false) {
    $class = $fadeOut ? ' fadeOut' : '';
    ob_start();
    ?>
    <li id="publication-block-<?= $this->id ?>" class="publication-block<?= $class ?>">
      <?= $this->afficherAvatar($utilisateur); ?>
      <div class="publication-content">
        <?= $this->afficherTitre(); ?>
        <?= $this->afficherContenu(); ?>
        <?= $this->afficherActions($utilisateur, false); ?>
      </div>
    </li>
    <?php
    return ob_get_clean();
  }

  protected function afficherAvatar($utilisateur) {
    ob_start();
    ?>
    <div class="publication-avatar">
      <img class="publication-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherTitre() {
    ob_start();
    ?>
    <div class="publication-header">
      <p><strong><?= $this->utilisateur->prenom ?></strong> a commenté <?= $this->dateCreation ?></p>
    </div>
    <?php
    return ob_get_clean();
  }

}

class Question extends Publication {

  public $nbReponse;
  public $aBonneReponse;
  public $detail;

  public function __construct( $texte, $type, $utilisateur, $parent = null, $specialite = null ) {
    parent::__construct( $texte, $type, $utilisateur, $parent, $specialite );
    $this->nbReponse = 0;
    $this->aBonneReponse = false;
    $this->detail = "";
  }

  public function requeteAjouter() {
    $requete = "INSERT INTO publication
                ( texte, fk_type_publication, fk_utilisateur, fk_publication, fk_specialite, detail_question )
                VALUES ( ?, ?, ?, ?, ?, ? );";
    $valeurs = array( $this->texte, $this->type, $this->utilisateur->id, $this->parent, $this->specialite, $this->detail );
    return array(
      'requete' => $requete,
      'valeurs' => $valeurs
    );
  }

  public function setNbReponse($nbReponse) {
    if($nbReponse == '' || $nbReponse == null)
      $this->nbReponse = 0;
    else
      $this->nbReponse = $nbReponse;
  }

  public function formatterNbReponse() {
    if($this->nbReponse > 0)
      return '<p>'. ($this->nbReponse > 1 ? $this->nbReponse . " réponses" : $this->nbReponse . " réponse") . '</p>';
    return '<p>Aucune réponse</p>';
  }

  public function afficher($utilisateur, $fadeOut = false) {
    $class = $fadeOut ? ' fadeOut' : '';
    $aBonneReponse = $this->aBonneReponse ? ' class="active"' : '';
    ob_start();
    ?>
    <li id="question-block-<?= $this->id ?>" class="question-block<?= $class ?>">
      <div class="question-titre-bloc">
        <div class="question-titre"><a href="./reponse.php?question=<?= $this->id ?>"><?= $this->texte ?></a></div>
        <span class="badge badge-info"><?= $this->specialite->nom ?></span>
        <div class="question-sous-titre"><?= $this->dateCreation ?></div>
      </div>
      <div class="question-reponse">
        <span<?= $aBonneReponse ?>><?= $this->nbReponse ?></span>
      </div>
      <div class="question-user">
        <?= $this->afficherAvatar($utilisateur); ?>
        <p><strong><?= $this->utilisateur->prenom ?></strong></p>
      </div>
    </li>
    <?php
    return ob_get_clean();
  }

  public function afficherDetail($utilisateur) {
    $aCommentaires = !empty($this->commentaires) ? ' aCommentaires' : '';
    ob_start();
    ?>
    <div class="publication-block">
      <?= parent::afficherAvatar($utilisateur); ?>
      <div class="publication-content">
        <?= $this->afficherTitre(); ?>
        <?= $this->afficherContenu(); ?>
        <?= $this->afficherActions($utilisateur); ?>
        <ul class="publication-commentaires<?= $aCommentaires ?>">
          <?php
          foreach ($this->commentaires as $commentaire) {
            echo $commentaire->afficher($utilisateur);
          }
          ?>
        </ul>
        <?= $this->afficherFormulaireCommenter(); ?>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherTitre() {
    ob_start();
    ?>
    <div class="publication-header question">
      <p><?= $this->texte ?></p>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherContenu() {
    ob_start();
    ?>
    <div class="publication-texte">
      <p><?= $this->detail_question ?></p>
      <div class="publication-specialite">
        <span class="badge badge-info"><?= $this->specialite->nom ?></span>
      </div>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherActions($utilisateur, $avecReply = true) {
    ob_start();
    ?>
    <div class="publication-actions" data-pubid=<?= $this->id ?> data-type=<?= $this->type ?>>
      <?php if($avecReply) : ?>
        <a href="#" class="fa fa-reply"></a>
      <?php endif; ?>
      <a href="#" data-vote="1" class="fa fa-thumbs-o-up vote<?= $this->determinerClass('up'); ?>"></a>
      <a href="#" data-vote="-1" class="fa fa-thumbs-o-down vote<?= $this->determinerClass('down'); ?>"></a>
      <span class="badge badge-pill badge-primary"><?= $this->getNbVotes() ?></span>
      <?php if($this->utilisateur->equals($utilisateur)) : ?>
        <a href="#" class="fa fa-trash"></a>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
  }

  protected function afficherAvatar($utilisateur) {
    ob_start();
    ?>
    <div class="question-avatar">
      <img class="question-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
    </div>
    <?php
    return ob_get_clean();
  }

}

class Reponse extends Publication {

  public $estReponse;
  public $utilisateurQuestion;

  public function __construct( $texte, $type, $utilisateur, $parent = null, $specialite = null ) {
    parent::__construct( $texte, $type, $utilisateur, $parent, $specialite );
    $this->estReponse = false;
    $this->utilisateurQuestion = null;
  }

  protected function afficherAvatar($utilisateur) {
    $checkReponse = $this->estReponse ? ' active' : '';
    ob_start();
    ?>
    <div class="publication-avatar">
      <img class="publication-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      <p class="publication-utilisateur"><strong><?= $this->utilisateur->prenom ?></strong></p>
      <p class="publication-date"><?= $this->dateCreation ?></p>
      <?php if($this->utilisateurQuestion->equals($utilisateur) || $this->estReponse) : ?>
      <a href="#" class="fa fa-check<?= $checkReponse ?>" data-pubid=<?= $this->id ?>></a>
      <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
  }

}

?>
