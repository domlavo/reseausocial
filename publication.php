<?php
require_once 'interfaces.php';
require_once 'helper.php';

class Publication implements IAjouter, ISupprimer
{
  protected $id;
  protected $texte;
  protected $type;
  protected $utilisateur;
  protected $parent;
  protected $specialite;
  protected $commentaires;
  protected $nbVotes;
  protected $voteUtilisateur;

  function __construct( $texte, $type, $utilisateur, $parent = null, $specialite = null )
  {
    if($texte != sanitizeInput($texte))
      throw new Exception("Le texte n'est pas conforme");
    $this->texte = $texte;
    $this->type = $type;
    $this->utilisateur = $utilisateur;
    $this->parent = $parent;
    $this->specialite = $specialite;
    $this->commentaires = array();
  }

  final public function setId($id) {
    $this->id = $id;
  }

  final public function getParentId() {
    return $this->parent;
  }

  final public function setNbVotes($nbVotes) {
    $this->nbVotes = $nbVotes;
  }

  final public function setVoteUtilisateur($voteUtilisateur) {
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
    ob_start();
    ?>
    <li id="publication-block-<?= $this->id ?>" class="publication-block<?= $class ?>">
      <div class="publication-avatar">
        <img class="publication-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      </div>
      <div class="publication-content">
        <div class="publication-header">
          <p><strong><?= $this->utilisateur->prenom ?></strong> a publié</p>
        </div>
        <div class="publication-texte">
          <p><?= $this->texte ?></p>
        </div>
        <div class="publication-actions" data-pubid=<?= $this->id ?>>
          <a href="#" class="fa fa-reply"></a>
          <a href="#" data-vote="1" class="fa fa-thumbs-o-up vote<?= $this->determinerClass('up'); ?>"></a>
          <a href="#" data-vote="-1" class="fa fa-thumbs-o-down vote<?= $this->determinerClass('down'); ?>"></a>
          <?php if($this->utilisateur->equals($utilisateur)) : ?>
          <a href="#" class="fa fa-trash"></a>
          <?php endif; ?>
        </div>
        <ul class="publication-commentaires">
          <?php
          foreach ($this->commentaires as $commentaire) {
            echo $commentaire->afficher($utilisateur);
          }
          ?>
        </ul>
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
      </div>
    </li>
    <?php
    return ob_get_clean();
  }

  public static function formulaireSupprimerPublication() {
    ob_start();
    ?>
    <form id="form-supprimer-publication">
      <input type="hidden" id="idSupprimerPublication" name="idSupprimerPublication" value="">
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
      <div class="publication-avatar">
        <img class="publication-img-avatar" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
      </div>
      <div class="publication-content">
        <div class="publication-header">
          <p><strong><?= $this->utilisateur->prenom ?></strong> a commenté</p>
        </div>
        <div class="publication-texte">
          <p><?= $this->texte ?></p>
        </div>
        <div class="publication-actions" data-pubid=<?= $this->id ?>>
          <a href="#" data-vote="1" class="fa fa-thumbs-o-up vote<?= $this->determinerClass('up'); ?>"></a>
          <a href="#" data-vote="-1" class="fa fa-thumbs-o-down vote<?= $this->determinerClass('down'); ?>"></a>
          <?php if($this->utilisateur->equals($utilisateur)) : ?>
          <a href="#" class="fa fa-trash"></a>
          <?php endif; ?>
        </div>
      </div>
    </li>
    <?php
    return ob_get_clean();
  }

}

?>
