<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* Must be logged in */
if (!isset($_SESSION['user'])) {
    $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Connectez-vous pour passer une commande.'];
    header('Location: login.php');
    exit;
}

/* Must have items in cart */
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: panier.php');
    exit;
}

$pageTitle  = 'Validation de commande';
$userId     = $_SESSION['user']['id'];
$userPoints = (int)($_SESSION['user']['points'] ?? 0);

/* ── Constante frais de livraison ── */
define('FRAIS_LIVRAISON', 8.00);

/* ── Totals ── */
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}

$usePoints      = (bool)($_SESSION['apply_points'] ?? false);
$pointsDiscount = ($usePoints && $userPoints >= 100) ? 10.00 : 0;
$total          = max(0, $subtotal - $pointsDiscount + FRAIS_LIVRAISON);
$pointsEarned   = (int)floor($total / 10);

/* ── Handle POST (place order) ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');

    if ($adresse === '') {
        $error = "L'adresse de livraison est obligatoire.";
    } else {
        $pdo->beginTransaction();
        try {
            /* Insert commande avec adresse et frais livraison */
            $stmt = $pdo->prepare(
                "INSERT INTO commandes (utilisateur_id, total, statut, adresse_livraison, frais_livraison)
                 VALUES (?, ?, 'en_attente', ?, ?)"
            );
            $stmt->execute([$userId, $total, $adresse, FRAIS_LIVRAISON]);
            $commandeId = $pdo->lastInsertId();

            /* Insert ligne_commande & update stock */
            $stmtLigne = $pdo->prepare(
                "INSERT INTO ligne_commande (commande_id, produit_id, quantite, prix_unitaire)
                 VALUES (?, ?, ?, ?)"
            );
            $stmtStock = $pdo->prepare(
                "UPDATE produits SET stock = stock - ? WHERE id = ? AND stock >= ?"
            );

            foreach ($cart as $pid => $item) {
              if (!isset($item['prix'], $item['quantite']) || $item['quantite'] <= 0) {
              continue;
                }
              
                $stmtLigne->execute([$commandeId, $pid, $item['quantite'], $item['prix']]);
                if ($item['quantite'] > 0) {
                  $stmtStock->execute([$item['quantite'], $pid, $item['quantite']]);
                  }
                }

            /* Update loyalty points */
            $newPoints = $userPoints - ($usePoints ? 100 : 0) + $pointsEarned;
            $pdo->prepare("UPDATE utilisateurs SET points_fidelite = ? WHERE id = ?")
                ->execute([$newPoints, $userId]);

            /* Update session points */
            $_SESSION['user']['points'] = $newPoints;

            /* Log */
          $message = "Commande #$commandeId passée — " . number_format($total, 2) . " DT (dont 8 DT livraison)";

          $stmt = $pdo->prepare("INSERT INTO historique (user_id, action) VALUES (?, ?)");
          $stmt->execute([$userId, $message]);
          $pdo->commit();

            /* Clear cart */
            $_SESSION['cart']         = [];
            $_SESSION['apply_points'] = false;

            /* Pass data to confirmation */
            $_SESSION['last_order'] = [
                'id'            => $commandeId,
                'subtotal'      => $subtotal,
                'frais'         => FRAIS_LIVRAISON,
                'remise'        => $pointsDiscount,
                'total'         => $total,
                'points_earned' => $pointsEarned,
                'new_points'    => $newPoints,
                'adresse'       => $adresse,
            ];

            header('Location: confirmation.php');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Une erreur est survenue : ' . $e->getMessage();
        }
    }
}

/* ── Pre-fill address ── */
$stmt = $pdo->prepare("SELECT adresse FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$savedAddress = $stmt->fetchColumn();

require_once 'includes/header.php';
?>

<div class="container py-4">
  <h2 class="section-title mb-4">Validation de la commande</h2>

  <?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="row g-4">

    <!-- Order form -->
    <div class="col-lg-7">
      <div class="form-card p-4">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-truck me-2" style="color:var(--primary);"></i>Informations de livraison
        </h5>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nom complet</label>
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['user']['nom']) ?>" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">E-mail</label>
            <input type="text" class="form-control"
                   value="<?= htmlspecialchars($_SESSION['user']['email']) ?>" disabled>
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">
              Adresse de livraison <span class="text-danger">*</span>
            </label>
            <input type="text" name="adresse" class="form-control" required
                   placeholder="Ville, rue, code postal..."
                   value="<?= htmlspecialchars($_POST['adresse'] ?? $savedAddress ?? '') ?>">
          </div>

          <!-- Points -->
          <?php if ($usePoints && $pointsDiscount > 0): ?>
          <div class="alert alert-success small">
            <i class="bi bi-trophy-fill me-1"></i>
            Remise fidélité de <strong><?= number_format($pointsDiscount, 2) ?> DT</strong> appliquée (-100 pts).
          </div>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-pill">
            <i class="bi bi-bag-check me-1"></i>Confirmer la commande
          </button>
          <a href="panier.php" class="btn btn-outline-secondary w-100 mt-2 rounded-pill">
            <i class="bi bi-arrow-left me-1"></i>Retour au panier
          </a>
        </form>
      </div>
    </div>

    <!-- Summary -->
    <div class="col-lg-5">
      <div class="cart-summary-box">
        <h5 class="fw-bold mb-3">Récapitulatif</h5>

        <?php foreach ($cart as $pid => $item): ?>
        <div class="d-flex justify-content-between align-items-center mb-2 small">
          <span><?= htmlspecialchars($item['nom']) ?> × <?= $item['quantite'] ?></span>
          <span class="fw-semibold"><?= number_format($item['prix'] * $item['quantite'], 2) ?> DT</span>
        </div>
        <?php endforeach; ?>

        <hr>
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">Sous-total</span>
          <span><?= number_format($subtotal, 2) ?> DT</span>
        </div>
        <?php if ($pointsDiscount > 0): ?>
        <div class="d-flex justify-content-between mb-1 text-success">
          <span><i class="bi bi-trophy-fill me-1"></i>Remise fidélité</span>
          <span>- <?= number_format($pointsDiscount, 2) ?> DT</span>
        </div>
        <?php endif; ?>

        <!-- Frais de livraison -->
        <div class="d-flex justify-content-between mb-1">
          <span class="text-muted">
            <i class="bi bi-truck me-1"></i>Frais de livraison
          </span>
          <span class="fw-semibold" style="color:#e65100;">
            <?= number_format(FRAIS_LIVRAISON, 2) ?> DT
          </span>
        </div>

        <hr>
        <div class="d-flex justify-content-between fw-bold fs-5">
          <span>Total</span>
          <span style="color:var(--primary);"><?= number_format($total, 2) ?> DT</span>
        </div>

        <!-- Points to earn -->
        <div class="mt-3 p-3 rounded-3" style="background:var(--primary-light);">
          <small class="text-muted">
            <i class="bi bi-star-fill me-1" style="color:#f6c90e;"></i>
            Cette commande vous rapportera <strong><?= $pointsEarned ?> point(s)</strong>.<br>
            Votre solde après commande :
            <strong><?= $userPoints - ($usePoints ? 100 : 0) + $pointsEarned ?> pts</strong>
          </small>
        </div>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>