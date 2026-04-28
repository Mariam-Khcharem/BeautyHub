<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Réclamation';
$errors    = [];
$sent      = isset($_GET['sent']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST['nom']     ?? '');
    $email   = trim($_POST['email']   ?? '');
    $sujet   = trim($_POST['sujet']   ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($nom === '')     $errors[] = 'Le nom est obligatoire.';
    if ($email === '')   $errors[] = "L'e-mail est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format e-mail invalide.";
    if ($sujet === '')   $errors[] = 'Veuillez choisir un sujet.';
    if ($message === '') $errors[] = 'Le message est obligatoire.';

    if (empty($errors)) {
        $userId = $_SESSION['user']['id'] ?? null;
        $recId  = null;
        $pdo->prepare(
            "INSERT INTO reclamations (utilisateur_id, nom, email, sujet, message)
             VALUES (?, ?, ?, ?, ?)"
        )->execute([$userId, $nom, $email, $sujet, $message]);
        $recId = $pdo->lastInsertId();

        if ($userId) {
            $pdo->prepare("INSERT INTO historique (user_id, action) VALUES (?, ?)")
                ->execute([$userId, "Réclamation soumise : $sujet"]);
        }
        /* PRG — redirect so refresh doesn't re-submit */
        header('Location: reclamation.php?sent=' . $recId);
        exit;
    }
}

require_once 'includes/header.php';
?>

<!-- Banner -->
<div class="hero-banner text-white text-center">
  <div class="container">
    <h1 class="display-5 fw-bold mb-2">
      <i class="bi bi-exclamation-circle me-2"></i>Zone de Réclamation
    </h1>
    <p class="lead" style="opacity:.85;">Votre satisfaction est notre priorité — signalez tout problème</p>
  </div>
</div>

<div class="container py-5">
  <div class="row g-4 justify-content-center">

    <!-- Reclamation form -->
    <div class="col-lg-7">

      <?php if ($sent): ?>
      <!-- Success -->
      <div class="form-card p-5 text-center">
        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-4"
             style="width:80px;height:80px;background:linear-gradient(135deg,#28a745,#20c997);">
          <i class="bi bi-check-lg text-white" style="font-size:2.5rem;"></i>
        </div>
        <h4 class="fw-bold mb-2">Réclamation envoyée !</h4>
        <p class="text-muted mb-4">
          Votre réclamation a bien été enregistrée. Notre équipe vous contactera sous <strong>48–72h</strong> ouvrables.
        </p>
        <p class="text-muted small mb-4">
          <i class="bi bi-shield-check me-1"></i>
          Référence : <strong>#REC<?= str_pad((int)$_GET['sent'], 5, '0', STR_PAD_LEFT) ?></strong>
        </p>
        <a href="index.php" class="btn btn-primary rounded-pill px-4">
          <i class="bi bi-house me-1"></i>Retour à l'accueil
        </a>
      </div>

      <?php else: ?>
      <!-- Form -->
      <div class="form-card p-4">
        <h5 class="fw-bold mb-1">
          <i class="bi bi-pencil-square me-2" style="color:var(--primary);"></i>Soumettre une réclamation
        </h5>
        <p class="text-muted small mb-4">
          Décrivez votre problème en détail. Nous traitons toutes les réclamations sérieusement.
        </p>

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
          <div class="row g-3 mb-3">
            <div class="col-sm-6">
              <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
              <input type="text" name="nom" class="form-control"
                     placeholder="Votre nom"
                     value="<?= htmlspecialchars($_SESSION['user']['nom'] ?? ($_POST['nom'] ?? '')) ?>">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-semibold">E-mail <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control"
                     placeholder="votre@email.com"
                     value="<?= htmlspecialchars($_SESSION['user']['email'] ?? ($_POST['email'] ?? '')) ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Sujet de la réclamation <span class="text-danger">*</span></label>
            <select name="sujet" class="form-select" required>
              <option value="">-- Choisissez un sujet --</option>
              <option value="Produit défectueux"
                <?= ($_POST['sujet'] ?? '') === 'Produit défectueux' ? 'selected' : '' ?>>
                Produit défectueux ou endommagé
              </option>
              <option value="Livraison en retard"
                <?= ($_POST['sujet'] ?? '') === 'Livraison en retard' ? 'selected' : '' ?>>
                Livraison en retard ou non reçue
              </option>
              <option value="Produit manquant"
                <?= ($_POST['sujet'] ?? '') === 'Produit manquant' ? 'selected' : '' ?>>
                Produit manquant dans la commande
              </option>
              <option value="Produit non conforme"
                <?= ($_POST['sujet'] ?? '') === 'Produit non conforme' ? 'selected' : '' ?>>
                Produit non conforme à la description
              </option>
              <option value="Problème paiement"
                <?= ($_POST['sujet'] ?? '') === 'Problème paiement' ? 'selected' : '' ?>>
                Problème de paiement ou facturation
              </option>
              <option value="Points fidélité"
                <?= ($_POST['sujet'] ?? '') === 'Points fidélité' ? 'selected' : '' ?>>
                Points fidélité incorrects
              </option>
              <option value="Autre"
                <?= ($_POST['sujet'] ?? '') === 'Autre' ? 'selected' : '' ?>>
                Autre problème
              </option>
            </select>
          </div>

          <?php if (isset($_SESSION['user'])): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">N° de commande concernée (optionnel)</label>
            <input type="text" name="commande_ref" class="form-control"
                   placeholder="Ex: #15"
                   value="<?= htmlspecialchars($_POST['commande_ref'] ?? '') ?>">
          </div>
          <?php endif; ?>

          <div class="mb-4">
            <label class="form-label fw-semibold">Description détaillée <span class="text-danger">*</span></label>
            <textarea name="message" class="form-control" rows="6"
                      placeholder="Décrivez précisément le problème : date d'achat, produit concerné, ce qui s'est passé..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
            <i class="bi bi-send me-1"></i>Envoyer la réclamation
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info panel -->
    <div class="col-lg-4">
      <!-- Process info -->
      <div class="form-card p-4 mb-3">
        <h6 class="fw-bold mb-3" style="color:var(--primary);">
          <i class="bi bi-info-circle me-1"></i>Notre processus
        </h6>
        <div class="routine-step">
          <div class="step-num">1</div>
          <div class="small">
            <strong>Réception</strong><br>
            <span class="text-muted">Votre réclamation est enregistrée immédiatement.</span>
          </div>
        </div>
        <div class="routine-step">
          <div class="step-num">2</div>
          <div class="small">
            <strong>Analyse</strong><br>
            <span class="text-muted">Notre équipe examine votre demande sous 24h.</span>
          </div>
        </div>
        <div class="routine-step">
          <div class="step-num">3</div>
          <div class="small">
            <strong>Contact</strong><br>
            <span class="text-muted">Vous recevez une réponse par e-mail sous 48–72h.</span>
          </div>
        </div>
        <div class="routine-step">
          <div class="step-num">4</div>
          <div class="small">
            <strong>Résolution</strong><br>
            <span class="text-muted">Remboursement, échange ou compensation selon le cas.</span>
          </div>
        </div>
      </div>

      <!-- Contact direct -->
      <div class="form-card p-4" style="border-left:4px solid #28a745;">
        <h6 class="fw-bold mb-3" style="color:#2e7d32;">
          <i class="bi bi-telephone me-1"></i>Contact urgent
        </h6>
        <p class="text-muted small mb-2">Pour les cas urgents, contactez-nous directement :</p>
        <p class="mb-1 small">
          <i class="bi bi-telephone-fill me-2" style="color:var(--primary);"></i>
          <strong>+216 71 000 000</strong>
        </p>
        <p class="mb-1 small">
          <i class="bi bi-envelope-fill me-2" style="color:var(--primary);"></i>
          reclamation@beautyhub.tn
        </p>
        <p class="text-muted small mt-2 mb-0">
          <i class="bi bi-clock me-1"></i>Lun–Sam : 9h–18h
        </p>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
