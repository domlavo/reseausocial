<?php
session_start();

require_once 'header.php';
require_once 'footer.php';
require_once 'navigation.php';
require_once 'helper.php';

$utilisateur = verifierConnection();
if(!$utilisateur) {
  header('Location: index.php');
}

if(!isset($_GET['question'])) {
  header('Location: index.php');
}

$question = recupererPersistance()->recupererQuestion($_GET['question']);
if(!$question) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererReponse($utilisateur, $question);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $utilisateur->afficher(); ?>
    <?= afficherNavigationSecondaire('', $utilisateur->loginID); ?>
    <div class="primary-container">
      <?php if( $utilisateur->equals($utilisateur) ) : ?>
        <div class="ajouter-publication-box">
          <form id="ajouter-publication-form">
            <div class="form-group">
              <label for="textePublication">Votre réponse</label>
              <textarea class="form-control" id="textePublication" name="textePublication" rows="3"></textarea>
            </div>
            <input type="hidden" name="type" value="3"/>
            <input type="hidden" name="question" value="<?= $question->id ?>"/>
            <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
            <div class="clearfix"></div>
          </form>
        </div>
      <?php endif; ?>
      <ul id="publication-container" data-questionid="<?= $question->id ?>" class="publication-container">
      <?php
      foreach ($publications as $publication) {
        echo $publication->afficher($utilisateur);
      }
      ?>
      </ul>
      <?= ajouterModal(
              "modalSupprimerPublication",
              "Supprimer la réponse",
              "Êtes-vous sûr de vouloir supprimer la réponse ?" .
              Publication::formulaireSupprimerPublication()) ?>
    </div>
  </div>
  <div class="sidebar">
  </div>
</div>

<?php
echo renderFooter();
?>
