<?php

global $secondaryNav;
$secondaryNav = array(
  'Journal' => 'profile.php',
  'Questions' => 'question.php'
);

function afficherNavigationPrincipale() {
  ob_start();
  ?>
  <ul class="main-nav">
    <li><a id="signOut" href="index.php">Déconnection</a></li>
  </ul>
  <script>
    $(function() {
      $("#signOut").on("click", function() {
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut();
      });
    });
  </script>
  <?php
  return ob_get_clean();
}

function afficherNavigationSecondaire( $active, $utilisateur ) {
  global $secondaryNav;
  ob_start();
  ?>
  <ul class="secondary-nav">
    <?php
      foreach ($secondaryNav as $page => $lien) {
        $class = $active == $page ? ' class="active"' : '';
    ?>
      <li<?= $class ?>><a href="<?= $lien ?>?utilisateur=<?= $utilisateur ?>"><?= $page ?></a></li>
    <?php } ?>
  </ul>
  <?php
  return ob_get_clean();
}

?>
