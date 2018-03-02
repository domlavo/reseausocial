<?php

$secondaryNav = array(
  'Journal' => 'journal.php',
  'Questions' => 'question.php'
);

function afficherNavigationPrincipale() {
  ob_start();
  ?>
  <ul class="main-nav">
    <li><a href="index.php">Déconnection</a></li>
  </ul>
  <?php
  return ob_get_clean();
}

function afficherNavigationSecondaire( $active ) {
  ob_start();
  ?>
  <ul class="secondary-nav">
    <?php
      foreach ($secondaryNav as $page => $lien) {
        $class = $active == $page ? ' class="active"' : '';
    ?>
      <li><a<?= $class ?> href="<?= $lien ?>"><?= $page ?></a></li>
    <?php } ?>
  </ul>
  <?php
  return ob_get_clean();
}

?>
