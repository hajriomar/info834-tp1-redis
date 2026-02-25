<?php
session_start();
require_once __DIR__ . "/src/db.php";
require_once __DIR__ . "/src/redis_client.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email = trim($_POST["email"] ?? "");
  $pass  = (string)($_POST["password"] ?? "");

  $pdo = db();
  $stmt = $pdo->prepare("SELECT id, nom, prenom, email, password FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $u = $stmt->fetch();

  if (!$u || $u["password"] !== $pass) {
    $error = "Email ou mot de passe incorrect.";
  } else {
    $userId = (int)$u["id"];

    // ---- Redis rate limit (DB0) ----
    $r0 = redis_db0();
    $key = rk("conn:" . $userId);

    $count = (int)$r0->incr($key);
    if ($count === 1) {
      $r0->expire($key, 600); // 10 min
    }
    $ttl = (int)$r0->ttl($key);

    if ($count > 10) {
      $error = "Limite atteinte: 10 connexions / 10 minutes. Réessayez dans ~{$ttl}s.";
    } else {
      // ---- Stats DB1 ----
      $r1 = redis_db1();
      $r1->lPush(rk("last_logins"), (string)$userId);
      $r1->lTrim(rk("last_logins"), 0, 9);
      $r1->zIncrBy(rk("total_conn"), 1, (string)$userId);

    
      $_SESSION["user_id"] = $userId;
      $_SESSION["nom"] = (string)$u["nom"];
      $_SESSION["prenom"] = (string)$u["prenom"];
      $_SESSION["email"] = (string)$u["email"];

      header("Location: services.php");
      exit;
    }
  }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Login</title></head>
<body>
  <h2>EtuServices - Connexion</h2>

  <?php if ($error): ?>
    <p style="color:red;"><b><?= htmlspecialchars($error) ?></b></p>
  <?php endif; ?>

  <form method="POST">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    <label>Mot de passe</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Se connecter</button>
  </form>

  <p><a href="stats.php">Voir stats</a></p>
</body>
</html>