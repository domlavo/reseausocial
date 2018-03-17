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

$publications = recupererPersistance()->recupererPublication($profile, $utilisateur, 1);

$autresUtilisateurs = recupererPersistance()->recupereAutreUtilisateur($utilisateur);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $profile->afficher(); ?>
    <?= afficherNavigationSecondaire('Journal', $_GET['utilisateur']); ?>
    <div class="primary-container">
      <?php if( $profile->equals($utilisateur) ) : ?>
        <div class="ajouter-publication-box">
          <form id="ajouter-publication-form">
            <div class="form-group">
              <label for="textePublication">À quoi pensez-vous, <?= $utilisateur->prenom ?>?</label>
              <textarea class="form-control" id="textePublication" name="textePublication" rows="3"></textarea>
            </div>
            <input type="hidden" name="type" value="1"/>
            <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
            <div class="clearfix"></div>
          </form>
        </div>
      <?php endif; ?>
      <ul id="publication-container" class="publication-container">
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
