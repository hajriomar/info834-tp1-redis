<?php
session_start();
require_once __DIR__ . "/src/redis_client.php";

$r1 = redis_db1();

$last = $r1->lRange(rk("last_logins"), 0, 9);
$top = $r1->zRevRange(rk("total_conn"), 0, 2, true);
$totals = $r1->hGetAll(rk("svc:total"));

$vente = (int)($totals["vente"] ?? 0);
$achat = (int)($totals["achat"] ?? 0);
$most = null;
if (!($vente === 0 && $achat === 0)) $most = ($vente >= $achat) ? "vente" : "achat";

$userStats = null;
$qUserId = isset($_GET["user_id"]) ? (int)$_GET["user_id"] : 0;
if ($qUserId > 0) {
  $userStats = $r1->hGetAll(rk("svc:user:" . $qUserId));
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Stats</title></head>
<body>
  <h2>Statistiques Redis</h2>

  <h3>10 derniers connectés (user_id)</h3>
  <pre><?= htmlspecialchars(json_encode($last, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>

  <h3>Top 3 connexions (user_id => total)</h3>
  <pre><?= htmlspecialchars(json_encode($top, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>

  <h3>Service le plus utilisé</h3>
  <pre><?= htmlspecialchars(json_encode(["most_used"=>$most, "totals"=>["vente"=>$vente,"achat"=>$achat]], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>

  <h3>Stats services par utilisateur</h3>
  <form method="GET">
    <input name="user_id" type="number" min="1" placeholder="user_id">
    <button type="submit">Afficher</button>
  </form>

  <?php if ($qUserId > 0): ?>
    <h4>User <?= htmlspecialchars((string)$qUserId) ?></h4>
    <pre><?= htmlspecialchars(json_encode($userStats, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
  <?php endif; ?>

  <p>
    <a href="accueil.php">Accueil</a>
    <?php if (isset($_SESSION["user_id"])): ?>
      | <a href="services.php">Services</a> | <a href="logout.php">Déconnexion</a>
    <?php else: ?>
      | <a href="login.php">Login</a>
    <?php endif; ?>
  </p>
</body>
</html>