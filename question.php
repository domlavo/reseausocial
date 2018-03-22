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

$specialites = recupererPersistance()->recupererSpecialite();
if(!$specialites) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererQuestions($profile);

$autresUtilisateurs = recupererPersistance()->recupereAutreUtilisateur($utilisateur);

$classQuestion = count($publications) > 0 ? ' aQuestion' : '';

echo renderHeader(true);
echo afficherNavigationPrincipale($utilisateur);
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $profile->afficher(); ?>
    <?= afficherNavigationSecondaire('Mes questions', $profile, $utilisateur); ?>
    <div class="primary-container">
      <?php if( $profile->equals($utilisateur) ) : ?>
        <div class="ajouter-publication-box">
          <form id="ajouter-publication-form">
            <div class="form-group">
              <label for="textePublication">Titre</label>
              <input type="text" class="form-control" id="textePublication" name="textePublication" placeholder="" value="" required="">
              <div class="input-specialite">
                <label for="specialite">Spécialité</label>
                <select class="form-control" id="specialite" name="specialite" required="">
                  <option value="">Choisir...</option>
                  <?php
                  foreach ($specialites as $specialite) {
                    echo $specialite->afficherOption();
                  }
                  ?>
                </select>
              </div>
              <label for="detail">Détail</label>
              <textarea id="detail-markdown" name="detail-markdown" rows="10"></textarea>
            </div>
            <input id="detail" type="hidden" name="detail" value="">
            <input type="hidden" name="type" value="2"/>
            <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
            <div class="clearfix"></div>
          </form>
        </div>
      <?php endif; ?>
      <ul id="question-container" class="question-container<?= $classQuestion ?>">
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
