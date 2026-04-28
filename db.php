<?php
/*
 * Connexion PDO — compatible Docker (variables d'env) et XAMPP (valeurs par défaut).
 *
 * Docker  → les valeurs viennent des variables d'environnement du container.
 * XAMPP   → les valeurs par défaut (localhost / root / '') sont utilisées.
 */

$host   = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'beautyhub';
$user   = getenv('DB_USER') ?: 'root';
$pass   = getenv('DB_PASS') ?: 'root';

try {
    $pdo = new PDO(
        "mysql:host={$host};port=8889;dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    $isDocker = (bool) getenv('DB_HOST');
    $hint     = $isDocker
        ? 'Vérifiez que le service <strong>db</strong> est bien démarré (<code>docker compose up -d db</code>).'
        : 'Vérifiez que <strong>XAMPP</strong> (Apache + MySQL) est bien démarré.';

    die('<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
        <title>Erreur DB</title>
        <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;
        min-height:100vh;background:#fff5f7;margin:0}
        .box{background:#fff;border-left:5px solid #d63384;border-radius:12px;padding:2rem 3rem;
        max-width:520px;box-shadow:0 4px 20px rgba(214,51,132,.15)}</style></head><body>
        <div class="box">
          <h2 style="color:#d63384">Erreur de connexion</h2>
          <p>' . htmlspecialchars($e->getMessage()) . '</p>
          <p>' . $hint . '</p>
        </div></body></html>');
}
