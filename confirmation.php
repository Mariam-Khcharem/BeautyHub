<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Commande confirmée';
$order = $_SESSION['last_order'];
unset($_SESSION['last_order']);

require_once 'includes/header.php';
?>

<div class="container py-5 text-center" style="max-width:620px;">
  <div class="form-card p-5">

    <!-- Success icon -->
    <div class="mb-4">
      <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center"
           style="width:90px;height:90px;background:linear-gradient(135deg,#28a745,#20c997);">
        <i class="bi bi-check-lg text-white" style="font-size:3rem;"></i>
      </div>
    </div>

    <h2 class="fw-bold text-success mb-2">Commande confirmée !</h2>
    <p class="text-muted mb-4">Merci pour votre achat. Votre commande a bien été enregistrée.</p>

    <!-- Order details -->
    <div class="table-responsive mb-4">
      <table class="table table-bordered table-sm text-start">
        <tr>
          <th style="background:var(--primary-light);">N° de commande</th>
          <td><strong>#<?= $order['id'] ?></strong></td>
        </tr>
        <tr>
          <th style="background:var(--primary-light);">Adresse de livraison</th>
          <td><?= htmlspecialchars($order['adresse']) ?></td>
        </tr>
        <tr>
          <th style="background:var(--primary-light);">Sous-total produits</th>
          <td><?= number_format($order['subtotal'], 2) ?> DT</td>
        </tr>
        <?php if ($order['remise'] > 0): ?>
        <tr class="table-success">
          <th style="background:#e8f5e9;">Remise fidélité</th>
          <td>- <?= number_format($order['remise'], 2) ?> DT</td>
        </tr>
        <?php endif; ?>
        <tr>
          <th style="background:var(--primary-light);">
            <i class="bi bi-truck me-1"></i>Frais de livraison
          </th>
          <td><?= number_format($order['frais'], 2) ?> DT</td>
        </tr>
        <tr class="table-primary">
          <th style="background:var(--primary-light);">Total payé</th>
          <td><strong style="color:var(--primary);"><?= number_format($order['total'], 2) ?> DT</strong></td>
        </tr>
        <tr>
          <th style="background:var(--primary-light);">Points gagnés</th>
          <td>
            <span class="badge" style="background:#f6c90e;color:#3d2700;font-size:.9rem;">
              +<?= $order['points_earned'] ?> pts
            </span>
          </td>
        </tr>
        <tr>
          <th style="background:var(--primary-light);">Solde total points</th>
          <td><strong><?= $order['new_points'] ?> pts</strong></td>
        </tr>
      </table>
    </div>

    <!-- Points info -->
    <?php if ($order['new_points'] >= 100): ?>
    <div class="alert alert-warning mb-4">
      <i class="bi bi-trophy-fill me-1"></i>
      Vous avez <strong><?= $order['new_points'] ?> pts</strong> —
      éligible à une remise de <?= floor($order['new_points'] / 100) * 10 ?> DT !
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-4 small">
      <i class="bi bi-info-circle me-1"></i>
      Il vous faut encore <strong><?= 100 - $order['new_points'] ?> pts</strong>
      pour débloquer votre première remise de 10 DT.
    </div>
    <?php endif; ?>

    <!-- CTA buttons -->
    <div class="d-flex flex-column gap-2">
      <a href="index.php" class="btn btn-primary rounded-pill fw-bold py-2">
        <i class="bi bi-bag me-1"></i>Continuer les achats
      </a>
      <a href="profil.php" class="btn btn-outline-secondary rounded-pill">
        <i class="bi bi-person me-1"></i>Voir mon profil & historique
      </a>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>