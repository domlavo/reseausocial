<?php
session_start();

require_once 'header.php';
require_once 'footer.php';
require_once 'navigation.php';

$utilisateur = verifierConnection();
if(!$utilisateur) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererQuestionsSpecialite($utilisateur);

$autresUtilisateurs = recupererPersistance()->recupereAutreUtilisateur($utilisateur);


echo renderHeader(true);
echo afficherNavigationPrincipale($utilisateur);
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $utilisateur->afficher(); ?>
    <?= afficherNavigationSecondaire('Répondre', $utilisateur, $utilisateur); ?>
    <div class="primary-container">
      <?php if(!empty($publications)) : ?>
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
      <?php else : ?>
        <h3>Aucune question est en attente de réponse</h3>
      <?php endif; ?>
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
