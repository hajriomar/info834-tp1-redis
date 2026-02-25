<?php
session_start();
if (isset($_SESSION["user_id"])) {
  header("Location: services.php");
  exit;
}
header("Location: login.php");
exit;