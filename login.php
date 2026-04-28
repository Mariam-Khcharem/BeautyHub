<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Connexion';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        /* Support hashed (new) and plain-text (legacy) passwords */
        $valid = $user && (
            password_verify($password, $user['password']) ||
            $password === $user['password']
        );

        if ($valid) {
            $_SESSION['user'] = [
                'id'     => $user['id'],
                'nom'    => $user['nom'],
                'email'  => $user['email'],
                'role'   => $user['role'],
                'points' => (int)($user['points_fidelite'] ?? 0),
            ];

            /* Log action */
            $pdo->prepare("INSERT INTO historique (user_id, action) VALUES (?, ?)")
                ->execute([$user['id'], 'Connexion']);

            $_SESSION['flash'] = [
                'type' => 'success',
                'text' => 'Bienvenue, ' . $user['nom'] . ' !'
            ];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container py-5" style="max-width:460px;">
  <div class="form-card p-4 p-md-5">

    <div class="text-center mb-4">
      <div class="profile-avatar mx-auto mb-3"><i class="bi bi-person"></i></div>
      <h3 class="fw-bold">Connexion</h3>
      <p class="text-muted small">Accédez à votre espace beauté</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger small">
      <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">Adresse e-mail</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 placeholder="votre@email.com"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-semibold">Mot de passe</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control"
                 placeholder="••••••••" required>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
        <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
      </button>
    </form>

    <hr class="my-4">
    <p class="text-center text-muted small mb-0">
      Pas encore de compte ?
      <a href="register.php" class="fw-semibold" style="color:var(--primary);">Créer un compte</a>
    </p>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
