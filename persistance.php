<?php

final class Persistance {

  private $dbName = 'domlavo';
  private $usernameBD = 'domlavo';
  private $passwordBD = '0839284';
  private $db;

  public static function Instance()
  {
    static $inst = null;
    if ($inst === null) {
      $inst = new Persistance();
    }
    return $inst;
  }

  private function __construct()
  {
    try
    {
      $this->db = new PDO('mysql:host=info10.cegepthetford.ca;dbname='. $dbName, $usernameBD, $passwordBD,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOexception $e)
    {
      echo 'Erreur SQL : ' . $e->getMessage() . '<br />';
    }
  }

  public function test() {
    
  }

}

?>
