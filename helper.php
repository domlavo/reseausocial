<?php

function isInt($variable) {
  return preg_match('/^\d+$/', $variable);
}

function sanitizeInput($string) {
  $string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
  return trim( strip_tags($string) );
}

function recupererPersistance() {
	return Persistance::Instance();
}

function verifierConnection() {
  if (session_status() == PHP_SESSION_NONE)
      session_start();
	$loginID = $_SESSION['loginID'];
	return recupererPersistance()->recupererUtilisateur($loginID);
}

function ajouterModal($id, $title, $body) {
  ob_start();
  ?>
  <div class="modal fade" id="<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><?= $title ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?= $body ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-primary modalConfirm">Confirmer</button>
        </div>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
}

?>
