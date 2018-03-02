<?php
require 'header.php';
require 'footer.php';

$utilisateur = null;
if(isset($_POST['loginID'])) {
  $persistance = Persistance::Instance();
  $utilisateur = $persistance->recupererUtilisateur($_POST['loginID']);
  if ($utilisateur != null) {
    session_start();
    $_SESSION['loginID'] = $utilisateur->loginID;
    header('Location: profile.php');
  } else {
    if(isset($_POST['nom'])) {
      $nouveauUtilisateur = new Utilisateur($_POST['nom'], $_POST['prenom'], $_POST['nb_session'], $_POST['loginID'], $_POST['specialite']);

      if( $persistance->ajouterUtilisateur($nouveauUtilisateur) ) {
        session_start();
        $_SESSION['loginID'] = $nouveauUtilisateur->loginID;
        header('Location: profile.php');
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
    <div class="g-signin2" data-onsuccess="onSignIn" data-theme="dark"></div>
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
  if ($utilisateur == null) {
    ?>

<div class="card card-container">
  <img id="profile-img" class="profile-img-card" src="//ssl.gstatic.com/accounts/ui/avatar_2x.png" />
  <p id="profile-name" class="profile-name-card"></p>
  <form id="form_creation" action="index.php" method="post" class="needs-validation" novalidate="">
      <div class="mb-3">
        <label for="prenom">Prénom</label>
        <input type="text" class="form-control" id="prenom" name="prenom" placeholder="" value="<?= $_POST['prenom'] ?>" required="">
        <div class="invalid-feedback">
          Valid first name is required.
        </div>
      </div>

      <div class="mb-3">
        <label for="nom">Nom de famille</label>
        <input type="text" class="form-control" id="nom" name="nom" placeholder="" value="<?= $_POST['nom'] ?>" required="">
        <div class="invalid-feedback">
          Valid last name is required.
        </div>
      </div>

      <div class="mb-3">
        <label for="nb_session">Nombre de sessions</label>
        <input type="number" class="form-control" id="nb_session" name="nb_session" placeholder="" value="" required="">
        <div class="invalid-feedback">
          Valid last name is required.
        </div>
      </div>

      <div class="mb-3">
        <label for="specialite">Spécialité</label>
        <input type="number" class="form-control" id="specialite" name="specialite" placeholder="" value="" required="">
        <div class="invalid-feedback">
          Valid last name is required.
        </div>
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
