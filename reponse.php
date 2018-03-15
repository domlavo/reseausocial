<?php
session_start();

require_once 'header.php';
require_once 'footer.php';
require_once 'navigation.php';
require_once 'helper.php';

$utilisateur = verifierConnection();
if(!$utilisateur) {
  header('Location: index.php');
}

if(!isset($_GET['question'])) {
  header('Location: index.php');
}

$question = recupererPersistance()->recupererQuestion($_GET['question']);
if(!$question) {
  header('Location: index.php');
}

$publications = recupererPersistance()->recupererReponse($utilisateur, $question->id);

echo renderHeader(true);
echo afficherNavigationPrincipale();
?>

<div class="content">
  <div class="primary hasSidebar">
    <?= $utilisateur->afficher(); ?>
    <?= afficherNavigationSecondaire('', $utilisateur->loginID); ?>
    <div class="primary-container">
      <?php if( $utilisateur->equals($utilisateur) ) { ?>
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
      (function($) {
      $(function() {
        $("#ajouter-publication-form").on("submit", function(e) {
          e.preventDefault();
          var datas = $("#ajouter-publication-form").serializeArray();
          datas.push({ name: "type", value: 3 });
          datas.push({ name: "question", value: <?= $question->id ?> });
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

        $(".content").delegate(".ajouter-commentaire-form", "submit", function(e) {
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

        $(".content").delegate(".publication-actions .fa-reply", "click", function(e) {
          e.preventDefault();
          $(this).closest(".publication-content").find(".publication-commenter").removeClass("slideDown");
        });

        $(".content").delegate(".commentaireAnnuler", "click", function(e) {
          e.preventDefault();
          $(this).closest(".publication-commenter").addClass("slideDown");
        });

        $(".content").delegate(".publication-actions .fa-trash", "click", function(e) {
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

        $(".content").delegate(".publication-actions .vote", "click", function(e) {
          e.preventDefault();
          var pubid = $(this).closest(".publication-actions").data("pubid");
          var vote = $(this).data("vote");
          var isActive = $(this).hasClass("active");
          var datas = [{ name: "pubid", value: pubid },
                      { name: "vote", value: vote },
                      { name: "isActive", value: isActive },
                      { name: "action", value: "likePublication" }];
          var icon = $(this);
          var container = $(this).closest(".publication-actions");
          $.ajax({
            type : "post",
            url : "ajax.php",
            data : datas,
          }).done(function (response) {
            var jsonResponse = JSON.parse(response);
            if(jsonResponse.status == "success") {
              $(container).find(".vote.active").removeClass("active");
              if(jsonResponse.vote != 0)
                $(icon).addClass("active");
            }
          });
        });

      });
      })(jQuery);
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
