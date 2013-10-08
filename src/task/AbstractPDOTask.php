<?php

abstract class AbstractPDOTask implements ITask
{

  const ESCAPE = true;
  const NO_ESCAPE = false;

  /**
   *  Configuration options
   */
  private $username;
  private $password;
  private $dsn;
  private $table;

  /**
   * @var PDO
   */
  private $db;

  public function __construct()
  {
    $settings = Settings::instance();
    $this->dsn = $settings->getOption("dsn");
    $this->username = $settings->getOption("username");
    $this->password = $settings->getOption("password");
    $this->table = $settings->getOption("table");
    $this->db = $this->connect();
  }

  public function __destruct()
  {
    $this->disconnect();
  }

  /**
   * @throws PDOException
   * @link http://php.net/manual/en/pdo.connections.php
   * @return PDO;
   */

  protected function connect()
  {
    $db = new PDO($this->dsn, $this->username, $this->password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }

  /**
   * @return void
   */

  protected function disconnect()
  {
    $this->db = null;
  }

  /**
   * @return boolean
   */

  protected function isConnected()
  {
    return($this->db === null);
  }

  /**
   * 
   * @return PDO
   */

  public function getDb()
  {
    return $this->db;
  }

  /**
   * @return string
   */

  public function getTable()
  {
    return $this->table;
  }

  /**
   *
   * @param mixed $field
   * @return string;
   */

  protected function transformAndEscapeField($field)
  {
    return $this->transform($field, self::ESCAPE);
  }

  /**
   *
   * @param mixed $field
   * @return string;
   */

  protected function transform($field, $escape = false, $date_format = "Y-m-d H:i:s", $utc = false)
  {
    $db = $this->getDb();
    $timezone = new DateTimeZone(date_default_timezone_get());

    if($field instanceof DateTime) {
      if($utc == false) {
        $field->setTimezone($timezone);
      }
      if($escape) {
        $date_format = "'$date_format'";
      }
      $field = $field->format($date_format);
    } elseif($field === null) {
      $field = "NULL";
    } elseif(is_string($field) && $escape == true) {
      $field = $db->quote($field);
    }
    return $field;
  }
}

?>