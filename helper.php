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

function elapsedTime($time)
{
    $time = time() - $time;
    $time = ($time<1)? 1 : $time;
    $tokens = array (
        31536000 => 'an',
        2592000 => 'mois',
        604800 => 'semaine',
        86400 => 'jour',
        3600 => 'heure',
        60 => 'minute',
        1 => 'seconde'
    );

    foreach ($tokens as $unit => $text) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return $numberOfUnits.' '.$text.(($numberOfUnits>1 && $text != 'mois')?'s':'');
    }
}

function verifierConnection() {
  if (session_status() == PHP_SESSION_NONE)
      session_start();
	$loginID = $_SESSION['loginID'];
	return recupererPersistance()->recupererUtilisateur($loginID);
}

function ajouterModal($id, $title, $body, $boutonConfirmer = 'Confirmer') {
  ob_start();
  ?>
  <div class="modal" id="<?= $id ?>" tabindex="-1" role="dialog" aria-labelledby="modalCenterTitle" aria-hidden="true">
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
          <button type="button" class="btn btn-primary modalConfirm"><?= $boutonConfirmer ?></button>
        </div>
      </div>
    </div>
  </div>
  <?php
  return ob_get_clean();
}

?>
