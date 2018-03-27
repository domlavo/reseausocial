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
            $("#question-container").addClass("aQuestion");
            $(output).insertAfter("#question-container .question-header");
            setTimeout(function(){
              $("#question-container").find(".fadeOut").removeClass("fadeOut");
            }, 100);
          } else if(jsonResponse.type == 3) {
            var nbReponse = $("<div />").html(jsonResponse.nbReponse).text();
            ga('send', 'event','Question','Repondre','Reseau Social',22);
            ga('send', {
        			hitType: 'pageview',
        			page: '/repondu.php'
        		});
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
        } else {
          var erreur = $("<div />").html(jsonResponse.error).text();
          $("#modalErreur .modal-body").html(erreur);
          $("#modalErreur").modal("show");
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
        } else {
          var erreur = $("<div />").html(jsonResponse.error).text();
          $("#modalErreur .modal-body").html(erreur);
          $("#modalErreur").modal("show");
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
      var estQuestion = $(this).closest(".publication-actions").data("type") == "2";
      $("#idSupprimerPublication").val(pubid);
      $("#typePublication").val(estQuestion);
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
          if(jsonResponse.estQuestion == "true") {
            window.location.reload(true);
          } else {
            setTimeout(function(){
              $("#"+jsonResponse.publication).remove();
            }, 500);
          }
          $("#modalSupprimerPublication").modal("hide");
        } else {
          $("#modalSupprimerPublication").modal("hide");
          var erreur = $("<div />").html(jsonResponse.error).text();
          $("#modalErreur .modal-body").html(erreur);
          $("#modalErreur").modal("show");
        }
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
          ga('send', 'event','Question','Approuver','Reseau Social',21);
          $(container).find(".vote.active").removeClass("active");
          if(jsonResponse.vote != 0){
            $(icon).addClass("active");
          }
          $(container).find(".badge").text(jsonResponse.nbVote);
        } else {
          var erreur = $("<div />").html(jsonResponse.error).text();
          $("#modalErreur .modal-body").html(erreur);
          $("#modalErreur").modal("show");
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
          ga('send', 'event','Question','Approuver','Reseau Social',21);
          $(".fa-check").removeClass("active");
          if(!isActive)
            $(icon).addClass("active");
        } else {
          var erreur = $("<div />").html(jsonResponse.error).text();
          $("#modalErreur .modal-body").html(erreur);
          $("#modalErreur").modal("show");
        }
      });
    });

  });
})(jQuery);
