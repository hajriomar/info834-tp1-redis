<?php
session_start();
require_once __DIR__ . "/src/redis_client.php";

if (!isset($_SESSION["user_id"])) {
  header("Location: login.php");
  exit;
}

$msg = "";
$err = "";

define("SERVICE_LIMIT", 10);
define("WINDOW_SEC", 600);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $service = $_POST["service"] ?? "";

  if ($service !== "vente" && $service !== "achat") {
    $err = "Service invalide.";
  } else {
    $uid = (string)$_SESSION["user_id"];
    $r1 = redis_db1();

    $limitKey = rk("svc_limit:" . $service . ":" . $uid);

    $count = (int)$r1->incr($limitKey);
    if ($count === 1) {
      $r1->expire($limitKey, WINDOW_SEC); 
    }
    $ttl = (int)$r1->ttl($limitKey);

    if ($count > SERVICE_LIMIT) {
      $err = "Limite atteinte pour le service '$service' : "
           . SERVICE_LIMIT . " appels / 10 minutes. Réessayez dans ~{$ttl}s.";
    } else {

      $r1->hIncrBy(rk("svc:user:" . $uid), $service, 1);
      $r1->hIncrBy(rk("svc:total"), $service, 1);

      $msg = "Appel service '$service' enregistré ("
           . $count . "/" . SERVICE_LIMIT . " dans la fenêtre, TTL ~{$ttl}s).";
    }
  }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Services</title></head>
<body>
  <h2>Bienvenue <?= htmlspecialchars($_SESSION["nom"] ?? ("user_id=" . $_SESSION["user_id"])) ?></h2>

  <?php if ($msg): ?><p style="color:green;"><b><?= htmlspecialchars($msg) ?></b></p><?php endif; ?>
  <?php if ($err): ?><p style="color:red;"><b><?= htmlspecialchars($err) ?></b></p><?php endif; ?>

  <h3>Services</h3>
  <form method="POST" style="display:inline;">
    <input type="hidden" name="service" value="vente">
    <button type="submit">Vente</button>
  </form>

  <form method="POST" style="display:inline; margin-left:10px;">
    <input type="hidden" name="service" value="achat">
    <button type="submit">Achat</button>
  </form>

  <p style="margin-top:20px;">
    <a href="stats.php">Stats</a> | <a href="logout.php">Déconnexion</a>
  </p>
</body>
</html>