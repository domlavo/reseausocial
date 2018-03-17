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

$question = recupererPersistance()->recupererQuestion($_GET['question'], $utilisateur);
if(!$question) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererReponse($utilisateur, $question);

$autresUtilisateurs = recupererPersistance()->recupereAutreUtilisateur($utilisateur);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <div class="primary-container reponse">
      <ul class="publication-container">
        <?= $question->afficherDetail($utilisateur); ?>
      </ul>
        <div class="question-separateur">
          <?= $question->formatterNbReponse(); ?>
        </div>

      <ul id="publication-container" data-questionid="<?= $question->id ?>" class="publication-container">
      <?php
      foreach ($publications as $publication) {
        echo $publication->afficher($utilisateur);
      }
      ?>
      </ul>
      <div class="ajouter-publication-box question">
        <form id="ajouter-publication-form">
          <div class="form-group">
            <label for="textePublication">Votre réponse</label>
            <textarea id="detail-markdown" name="detail-markdown" rows="10"></textarea>
            <input id="detail" type="hidden" name="textePublication" value="">
            <input type="hidden" name="type" value="3"/>
            <input type="hidden" name="question" value="<?= $question->id ?>"/>
          </div>
          <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
          <div class="clearfix"></div>
        </form>
      </div>
      <?= ajouterModal(
              "modalSupprimerPublication",
              "Supprimer la réponse",
              "Êtes-vous sûr de vouloir supprimer la réponse ?" .
              Publication::formulaireSupprimerPublication()) ?>
    </div>
  </div>
  <div class="sidebar">
    <ul class="sidebar-utilisateur-container">
    <?php
    foreach ($autresUtilisateurs as $autreUtilisateur) {
      echo $autreUtilisateur->afficherListe($utilisateur);
    }
    ?>
    </ul>
  </div>
</div>

<?php
echo renderFooter();
?>
