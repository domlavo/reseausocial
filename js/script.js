(function($) {
  $(function() {

    $("#detail-markdown").markdown({
      iconlibrary:'fa',
      language:'fr',
      onBlur: function(e) {
        $("#detail").val(e.parseContent());
      }
    });

    $("#ajouter-publication-form").on("submit", function(e) {
      e.preventDefault();
      ga('send', 'event','Question','Repondre','Reseau Social',22);
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
          $("#detail-markdown").val("");
          var output = $("<div />").html(jsonResponse.publication).text();
          if(jsonResponse.type == 2) {
            $(output).insertAfter("#question-container .question-header");
            setTimeout(function(){
              $("#question-container").find(".fadeOut").removeClass("fadeOut");
            }, 100);
          } else if(jsonResponse.type == 3) {
            var nbReponse = $("<div />").html(jsonResponse.nbReponse).text();
            $(".question-separateur").html(nbReponse);
            $("#publication-container").append(output);
            setTimeout(function(){
              $("#publication-container").find(".fadeOut").removeClass("fadeOut");
            }, 100);
          } else {
            $("#publication-container").prepend(output);
            setTimeout(function(){
              $("#publication-container").find(".fadeOut").removeClass("fadeOut");
            }, 100);
          }
        }
      });
    });

    $(".content").delegate(".ajouter-commentaire-form", "submit", function(e) {
      e.preventDefault();
      var form = $(this);
      var commentaires = $(form).closest(".publication-content").find(".publication-commentaires");
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
          $(commentaires).append(output);
          $(commentaires).addClass("aCommentaires");
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

    $("#modalSupprimerPublication").on("hidden.bs.modal", function (e) {
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
          $("#"+jsonResponse.publication).addClass("fadeOut");
          setTimeout(function(){
            $("#"+jsonResponse.publication).remove();
          }, 500);
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
          $(container).find(".badge").text(jsonResponse.nbVote);
        }
      });
    });

    $(".content").delegate(".fa-check", "click", function(e) {
      e.preventDefault();
      var repid = $(this).data("pubid");
      var isActive = $(this).hasClass("active");
      var questionid = $("#publication-container").data("questionid");
      var datas = [{ name: "pubid", value: questionid },
                  { name: "repid", value: repid },
                  { name: "isActive", value: isActive },
                  { name: "action", value: "selectionnerReponse" }];
      var icon = $(this);
      $.ajax({
        type : "post",
        url : "ajax.php",
        data : datas,
      }).done(function (response) {
        var jsonResponse = JSON.parse(response);
        if(jsonResponse.status == "success") {
          $(".fa-check").removeClass("active");
          if(!isActive)
            $(icon).addClass("active");
        }
      });
    });

  });
})(jQuery);
