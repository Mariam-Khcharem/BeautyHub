<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Créer un compte';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $email   = trim($_POST['email']   ?? '');
    $pass    = trim($_POST['password'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if ($nom === '')   $errors[] = 'Le nom est obligatoire.';
    if ($email === '') $errors[] = "L'e-mail est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format e-mail invalide.";
    if (strlen($pass) < 6) $errors[] = 'Le mot de passe doit faire au moins 6 caractères.';

    if (empty($errors)) {
        /* Check duplicate email */
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Cet e-mail est déjà utilisé.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pdo->prepare(
                "INSERT INTO utilisateurs (nom, email, password, adresse, role)
                 VALUES (?, ?, ?, ?, 'user')"
            )->execute([$nom, $email, $hash, $adresse]);

            $newId = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO historique (user_id, action) VALUES (?, ?)")
                ->execute([$newId, 'Inscription']);

            $_SESSION['flash'] = [
                'type' => 'success',
                'text' => 'Compte créé avec succès ! Connectez-vous.'
            ];
            header('Location: login.php');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5" style="max-width:500px;">
  <div class="form-card p-4 p-md-5">

    <div class="text-center mb-4">
      <div class="profile-avatar mx-auto mb-3"><i class="bi bi-person-plus"></i></div>
      <h3 class="fw-bold">Créer un compte</h3>
      <p class="text-muted small">Rejoignez la communauté BeautyHub</p>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger small">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
        <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Nom complet</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" name="nom" class="form-control"
                 placeholder="Votre nom"
                 value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Adresse e-mail</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="votre@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Mot de passe</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control"
                 placeholder="Minimum 6 caractères" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Adresse de livraison</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
          <input type="text" name="adresse" class="form-control"
                 placeholder="Ville, Quartier"
                 value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
        <i class="bi bi-person-check me-1"></i>Créer mon compte
      </button>
    </form>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
      Déjà inscrit ?
      <a href="login.php" class="fw-semibold" style="color:var(--primary);">Se connecter</a>
    </p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
