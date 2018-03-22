<?php
session_start();
session_destroy();
session_start();

require_once 'header.php';
require_once 'footer.php';

$utilisateur = null;
if(isset($_POST['loginID'])) {
  $persistance = recupererPersistance();
  $utilisateur = $persistance->recupererUtilisateur($_POST['loginID']);
  if ($utilisateur) {
    $_SESSION['loginID'] = $utilisateur->loginID;
    header('Location: profile.php?utilisateur='.$utilisateur->loginID);
  } else {
    if(isset($_POST['nb_session'])) {
      $specialite = new Specialite("");
      $specialite->id = $_POST['specialite'];
      $nouveauUtilisateur = new Utilisateur($_POST['nom'], $_POST['prenom'], $_POST['nb_session'], $_POST['loginID'], $specialite);

      $ajouter = $persistance->ajouterUtilisateur($nouveauUtilisateur);
      if( $ajouter === true ) {
        $_SESSION['loginID'] = $nouveauUtilisateur->loginID;
        header('Location: profile.php?utilisateur='.$nouveauUtilisateur->loginID);
      } else {
        echo $ajouter;
      }

    }
  }
}

echo renderHeader();
?>

<div class="top-section"></div>
<div class="bottom-section"></div>

<?php
if(!isset($_POST['loginID'])) {
?>

<div class="card card-container">
  <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
  <p id="profile-name" class="profile-name-card"></p>
  <form id="form_connection" action="index.php" method="post">
    <input id="loginID" type="hidden" name="loginID" value="">
    <input id="nom" type="hidden" name="nom" value="">
    <input id="prenom" type="hidden" name="prenom" value="">
    <div class="g-signin2" data-onsuccess="onSignIn" data-theme="light" data-longtitle="true"></div>
  </form>
</div>

<script>
  function onSignIn(googleUser) {

    var profile = googleUser.getBasicProfile();
    jQuery('#loginID').val(profile.getId());
    jQuery('#nom').val(profile.getFamilyName());
    jQuery('#prenom').val(profile.getGivenName());
    jQuery('#form_connection').submit();

  }
</script>

<?php
} else {
  if (!$utilisateur) {
    $specialites = recupererPersistance()->recupererSpecialite();
    ?>

<div class="card card-container">
  <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
  <p id="profile-name" class="profile-name-card"></p>
  <form id="form_creation" action="index.php" method="post" class="needs-validation" novalidate="">
      <div class="mb-3">
        <label for="prenom">Prénom</label>
        <input type="text" class="form-control" id="prenom" name="prenom" placeholder="" value="<?= $_POST['prenom'] ?>" required="">
      </div>

      <div class="mb-3">
        <label for="nom">Nom de famille</label>
        <input type="text" class="form-control" id="nom" name="nom" placeholder="" value="<?= $_POST['nom'] ?>" required="">
      </div>

      <div class="mb-3">
        <label for="nb_session">Nombre de sessions</label>
        <input type="number" class="form-control" id="nb_session" name="nb_session" placeholder="" value="" required="">
      </div>

      <div class="mb-3">
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

    <input id="loginID" type="hidden" name="loginID" value="<?= $_POST['loginID'] ?>">

    <button class="btn btn-primary btn-lg btn-block" type="submit">Soumettre</button>
    <a href="#" onclick="signOut();">Sign out</a>
    <script>
      function signOut() {
        var auth2 = gapi.auth2.getAuthInstance();
        auth2.signOut().then(function () {
          console.log('User signed out.');
        });
      }
    </script>
  </form>
</div>

    <?php
  }
}
echo renderFooter();
?>
