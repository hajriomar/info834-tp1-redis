<?php
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $dsn = "mysql:host=mysql;dbname=tpredis;charset=utf8mb4";
  $pdo = new PDO($dsn, "app", "apppass", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
  return $pdo;
}
?>