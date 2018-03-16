<?php
session_start();

require_once 'header.php';
require_once 'footer.php';
require_once 'navigation.php';

$utilisateur = verifierConnection();
if(!$utilisateur || !isset($_GET['utilisateur'])) {
  header('Location: index.php');
}

$profile = recupererPersistance()->recupererUtilisateur($_GET['utilisateur']);
if(!$profile) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererQuestions($profile);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $profile->afficher(); ?>
    <?= afficherNavigationSecondaire('Questions', $_GET['utilisateur']); ?>
    <div class="primary-container">
      <?php if( $profile->equals($utilisateur) ) : ?>
        <div class="ajouter-publication-box">
          <form id="ajouter-publication-form">
            <div class="form-group">
              <label for="textePublication">Quelle est votre question?</label>
              <textarea class="form-control" id="textePublication" name="textePublication" rows="3"></textarea>
            </div>
            <input type="hidden" name="type" value="2"/>
            <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
            <div class="clearfix"></div>
          </form>
        </div>
      <?php endif; ?>
      </script>
      <ul id="question-container" class="question-container">
        <li class="question-header">
          <div class="question-header-title">Questions</div>
          <div class="question-header-vote">Réponses</div>
          <div class="question-header-user">Auteurs</div>
        </li>
        <?php
        foreach ($publications as $publication) {
          echo $publication->afficher($utilisateur);
        }
        ?>
      </ul>
      <?= ajouterModal(
              "modalSupprimerPublication",
              "Supprimer la publication",
              "Êtes-vous sûr de vouloir supprimer la publication ?" .
              Publication::formulaireSupprimerPublication()) ?>
    </div>
  </div>
  <div class="sidebar">
  </div>
</div>

<?php
echo renderFooter();
?>
