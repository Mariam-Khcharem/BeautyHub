<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Contact & Conseils';

$errors = [];

/* ---- Handle contact form ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type'])) {

    $formType = $_POST['form_type'];

    if ($formType === 'contact') {
        $nom     = trim($_POST['nom']     ?? '');
        $email   = trim($_POST['email']   ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($nom === '' || $email === '' || $message === '') {
            $errors['contact'] = 'Veuillez remplir tous les champs.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact'] = "Format d'e-mail invalide.";
        } else {
            if (isset($_SESSION['user'])) {
                $pdo->prepare("INSERT INTO historique (user_id, action) VALUES (?, ?)")
                    ->execute([$_SESSION['user']['id'], "Message contact envoyé"]);
            }
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Message envoyé ! Nous vous répondrons dans les plus brefs délais.'];
            header('Location: contact.php?sent=contact');
            exit;
        }

    } elseif ($formType === 'conseil') {
        $nom      = trim($_POST['nom']      ?? '');
        $email    = trim($_POST['email']    ?? '');
        $question = trim($_POST['question'] ?? '');

        if ($nom === '' || $email === '' || $question === '') {
            $errors['conseil'] = 'Veuillez remplir tous les champs.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['conseil'] = "Format d'e-mail invalide.";
        } else {
            $userId = $_SESSION['user']['id'] ?? null;
            $pdo->prepare(
                "INSERT INTO conseils (utilisateur_id, nom, email, question) VALUES (?, ?, ?, ?)"
            )->execute([$userId, $nom, $email, $question]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Votre question a été envoyée ! Notre équipe vous répondra sous 48h.'];
            header('Location: contact.php?sent=conseil#conseils');
            exit;
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Banner -->
<div class="hero-banner text-white text-center">
  <div class="container">
    <h1 class="display-5 fw-bold mb-2">
      <i class="bi bi-chat-heart me-2"></i>Contact & Conseils
    </h1>
    <p class="lead" style="opacity:.85;">Nous sommes là pour vous aider et vous conseiller</p>
  </div>
</div>

<div class="container py-5">

  <!-- Contact info cards -->
  <div class="row g-3 mb-5">
    <div class="col-md-3">
      <div class="form-card p-3 text-center h-100">
        <div class="info-icon-box mx-auto mb-2"><i class="bi bi-envelope-heart"></i></div>
        <h6 class="fw-bold">E-mail</h6>
        <p class="text-muted small mb-0">contact@beautyhub.tn</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-card p-3 text-center h-100">
        <div class="info-icon-box mx-auto mb-2"><i class="bi bi-telephone-fill"></i></div>
        <h6 class="fw-bold">Téléphone</h6>
        <p class="text-muted small mb-0">+216 71 000 000</p>
        <p class="text-muted small mb-0">Lun–Sam : 9h–18h</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-card p-3 text-center h-100">
        <div class="info-icon-box mx-auto mb-2"><i class="bi bi-geo-alt-fill"></i></div>
        <h6 class="fw-bold">Adresse</h6>
        <p class="text-muted small mb-0">Avenue Habib Bourguiba</p>
        <p class="text-muted small mb-0">Tunis, Tunisie</p>
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-card p-3 text-center h-100">
        <div class="info-icon-box mx-auto mb-2"><i class="bi bi-instagram"></i></div>
        <h6 class="fw-bold">Réseaux sociaux</h6>
        <p class="text-muted small mb-0">@beautyhub_tn</p>
        <p class="text-muted small mb-0">Suivez-nous !</p>
      </div>
    </div>
  </div>

  <div class="row g-5">

    <!-- Contact form -->
    <div class="col-lg-6">
      <div class="form-card p-4">
        <h5 class="fw-bold mb-1">
          <i class="bi bi-envelope me-2" style="color:var(--primary);"></i>Nous contacter
        </h5>
        <p class="text-muted small mb-4">Envoyez-nous un message, nous vous répondons sous 24h.</p>

        <?php if (isset($errors['contact'])): ?>
        <div class="alert alert-danger small"><?= htmlspecialchars($errors['contact']) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="form_type" value="contact">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nom complet</label>
            <input type="text" name="nom" class="form-control" placeholder="Votre nom"
                   value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? ($_POST['nom'] ?? '')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">E-mail</label>
            <input type="email" name="email" class="form-control" placeholder="votre@email.com"
                   value="<?= htmlspecialchars($_SESSION['user']['email'] ?? ($_POST['email'] ?? '')) ?>">
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Votre message</label>
            <textarea name="message" class="form-control" rows="5"
                      placeholder="Comment pouvons-nous vous aider ?"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
            <i class="bi bi-send me-1"></i>Envoyer le message
          </button>
        </form>
      </div>
    </div>

    <!-- Advice form -->
    <div class="col-lg-6" id="conseils">
      <div class="form-card p-4" style="border-left:4px solid var(--accent);">
        <h5 class="fw-bold mb-1">
          <i class="bi bi-stars me-2" style="color:var(--accent);"></i>Demander un conseil beauté
        </h5>
        <p class="text-muted small mb-4">
          Nos expertes en beauté vous conseillent gratuitement sur vos soins, routines et produits.
        </p>

        <?php if (isset($errors['conseil'])): ?>
        <div class="alert alert-danger small"><?= htmlspecialchars($errors['conseil']) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
          <input type="hidden" name="form_type" value="conseil">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nom complet</label>
            <input type="text" name="nom" class="form-control" placeholder="Votre nom"
                   value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? ($_POST['nom'] ?? '')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">E-mail</label>
            <input type="email" name="email" class="form-control" placeholder="votre@email.com"
                   value="<?= htmlspecialchars($_SESSION['user']['email'] ?? ($_POST['email'] ?? '')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Type de peau / cheveux</label>
            <select name="type_peau" class="form-select">
              <option value="">-- Sélectionnez --</option>
              <option value="normale">Peau normale</option>
              <option value="seche">Peau sèche</option>
              <option value="grasse">Peau grasse</option>
              <option value="mixte">Peau mixte</option>
              <option value="sensible">Peau sensible</option>
              <option value="cheveux_secs">Cheveux secs / abîmés</option>
              <option value="cheveux_gras">Cheveux gras</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Votre question</label>
            <textarea name="question" class="form-control" rows="4"
                      placeholder="Décrivez votre préoccupation beauté, votre routine actuelle..."><?= htmlspecialchars($_POST['question'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn w-100 rounded-pill fw-bold text-white"
                  style="background:var(--accent);">
            <i class="bi bi-chat-heart me-1"></i>Envoyer ma question
          </button>
        </form>

        <div class="mt-3 p-3 rounded-3" style="background:var(--primary-light);">
          <small class="text-muted">
            <i class="bi bi-clock me-1"></i>
            Délai de réponse habituel : <strong>24 à 48h</strong> ouvrables.
            Les conseils sont <strong>entièrement gratuits</strong>.
          </small>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
