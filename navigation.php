<?php

global $secondaryNav;
$secondaryNav = array(
  'Journal' => array(
    'page' => 'profile.php',
    'get' => true,
    'etreUser' => false,
  ),
  'Mes questions' => array(
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

function afficherNavigationPrincipale($utilisateur) {
  ob_start();
  ?>
  <ul class="main-nav">
    <ul class="nav navbar-nav navbar-right">
      <li id="fat-menu" class="dropdown">
        <a href="#" class="dropdown-toggle" id="drop3" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
          Menu
          <span class="caret"></span>
        </a>
        <ul class="dropdown-menu" aria-labelledby="drop3">
          <li><a href="profile.php?utilisateur=<?= $utilisateur->loginID ?>">Journal</a></li>
          <li><a href="question.php?utilisateur=<?= $utilisateur->loginID ?>">Mes questions</a></li>
          <li><a href="repondre.php">Répondre</a></li>
          <li role="separator" class="divider"></li>
          <li><a class="dropdown-item" id="signOut" href="index.php">Déconnection</a></li>
        </ul>
      </li>
    </ul>
  </ul>
  <script>
    $(function() {
      $("#signOut").on("click", function() {
        if(!gapi.auth2){
          gapi.load('auth2', function() {
            gapi.auth2.init();
          });
        }
        gapi.auth2.getAuthInstance().signOut().then(function() {
          window.location = window.location.href;
        });
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
