<?php
session_start();

require 'header.php';
require 'footer.php';
require 'navigation.php';

$utilisateur = verifierConnection();
if($utilisateur == null || !isset($_GET['utilisateur'])) {
  header('Location: index.php');
}

$profile = recupererPersistance()->recupererUtilisateur($_GET['utilisateur']);
if($profile == null) {
  header('Location: index.php');
}

echo renderHeader();
echo afficherNavigationPrincipale();
?>

<div class="content hasNav">
  <?= $profile->afficher(); ?>
</div>

<?php
echo renderFooter();
?>
