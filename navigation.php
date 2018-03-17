<?php

global $secondaryNav;
$secondaryNav = array(
  'Journal' => array(
    'page' => 'profile.php',
    'get' => true,
    'etreUser' => false,
  ),
  'Questions' => array(
    'page' => 'question.php',
    'get' => true,
    'etreUser' => false,
  ),
  'Répondre' => array(
    'page' => 'repondre.php',
    'get' => false,
    'etreUser' => true,
  ),
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

function afficherNavigationSecondaire( $active, $utilisateur, $profile ) {
  global $secondaryNav;
  ob_start();
  ?>
  <ul class="secondary-nav">
    <?php
      foreach ($secondaryNav as $page => $infos) {
        $class = $active == $page ? ' class="active"' : '';
        $lien = $infos['page'];
        $get = $infos['get'] ? '?utilisateur=' . $utilisateur->loginID : '';
        if( !$infos['etreUser'] || $utilisateur->equals($profile) ) {
    ?>
      <li<?= $class ?>><a href="<?= $lien . $get ?>"><?= $page ?></a></li>
    <?php
        }
      }
    ?>
  </ul>
  <?php
  return ob_get_clean();
}

?>
