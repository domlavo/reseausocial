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

$publications = recupererPersistance()->recupererPublication($profile);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $profile->afficher(); ?>
    <?= afficherNavigationSecondaire('Journal'); ?>
    <div class="primary-container">
      <?php if( $profile->equals($utilisateur) ) { ?>
        <div class="ajouter-publication-box">
          <form id="ajouter-publication-form">
            <div class="form-group">
              <label for="textePublication">À quoi pensez-vous, <?= $utilisateur->prenom ?>?</label>
              <textarea class="form-control" id="textePublication" name="textePublication" rows="3"></textarea>
            </div>
            <button id="submitPublication" type="submit" class="btn btn-primary">Publier</button>
            <div class="clearfix"></div>
          </form>
        </div>
      <?php } ?>
      <script type="text/javascript">
      $(function() {
        $("#ajouter-publication-form").on("submit", function(e) {
          e.preventDefault();
          var datas = $("#ajouter-publication-form").serializeArray();
          datas.push({ name: "action", value: "ajouterPublication" });
          $.ajax({
            type : "post",
            url : "ajax.php",
            data : datas,
          }).done(function (response) {
            var jsonResponse = JSON.parse(response);
            if(jsonResponse.status == "success") {
              $("#textePublication").val("");
              var output = $("<div />").html(jsonResponse.publication).text();
              $("#publication-container").prepend(output);
              setTimeout(function(){
                $("#publication-container").find(".fadeOut").removeClass("fadeOut");
              }, 100);
            }
          });
        });

        $(".ajouter-commentaire-form").on("submit", function(e) {
          e.preventDefault();
          var form = $(this);
          var datas = $(form).serializeArray();
          datas.push({ name: "action", value: "ajouterCommentaire" });
          $.ajax({
            type : "post",
            url : "ajax.php",
            data : datas,
          }).done(function (response) {
            var jsonResponse = JSON.parse(response);
            if(jsonResponse.status == "success") {
              $(form).find(".texteCommentaire").val("");
              var output = $("<div />").html(jsonResponse.publication).text();
              $(form).closest(".publication-commenter").before(output);
              setTimeout(function(){
                $(form).closest(".publication-content").find(".fadeOut").removeClass("fadeOut");
              }, 100);
            }
          });
        });

        $(".publication-actions .fa-reply").on("click", function(e) {
          e.preventDefault();
          $(this).closest(".publication-content").find(".publication-commenter").removeClass("slideDown");
        });

        $(".commentaireAnnuler").on("click", function(e) {
          e.preventDefault();
          $(this).closest(".publication-commenter").addClass("slideDown");
        });

        $(".publication-actions .fa-trash").on("click", function(e) {
          e.preventDefault();
          var pubid = $(this).closest(".publication-actions").data("pubid");
          $("#idSupprimerPublication").val(pubid);
          $("#modalSupprimerPublication").modal("show");
        });

        $('#modalSupprimerPublication').on('hidden.bs.modal', function (e) {
          $("#idSupprimerPublication").val("");
        });

        $("#modalSupprimerPublication .modalConfirm").on("click", function(e) {
          e.preventDefault();
          var datas = $("#form-supprimer-publication").serializeArray();
          datas.push({ name: "action", value: "supprimerPublication" });
          $.ajax({
            type : "post",
            url : "ajax.php",
            data : datas,
          }).done(function (response) {
            var jsonResponse = JSON.parse(response);
            if(jsonResponse.status == "success") {
              $("#"+jsonResponse.publication).remove();
            }
            $("#modalSupprimerPublication").modal("hide");
          });
        });

      });
      </script>
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
  </div>
</div>

<?php
echo renderFooter();
?>
