<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Mon Panier';

$cart = $_SESSION['cart'] ?? [];

/* ── Category icons ── */
$catIcons = [1=>'bi-droplet-half', 2=>'bi-scissors', 3=>'bi-palette', 4=>'bi-flower1', 5=>'bi-stars'];

/* ── Constante frais de livraison ── */
define('FRAIS_LIVRAISON', 8.00);

/* ── Subtotal ── */
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['prix'] * $item['quantite'];
}

/* ── Loyalty points ── */
$userPoints      = isset($_SESSION['user']) ? (int)($_SESSION['user']['points'] ?? 0) : 0;
$pointsValue     = 10.00;
$pointsThreshold = 100;

if (isset($_GET['apply_points'])) {
    $_SESSION['apply_points'] = ($_GET['apply_points'] === '1');
}

$usePoints = ($_SESSION['apply_points'] ?? false)
             && isset($_SESSION['user'])
             && ($userPoints >= $pointsThreshold);

$pointsDiscount = $usePoints ? $pointsValue : 0;

/* ── Total avec frais livraison ── */
$total = max(0, $subtotal - $pointsDiscount + (empty($cart) ? 0 : FRAIS_LIVRAISON));

require_once 'includes/header.php';
?>

<div class="container py-4">
  <h2 class="section-title mb-4">Mon Panier</h2>

  <?php if (empty($cart)): ?>
  <!-- Empty cart -->
  <div class="text-center py-5">
    <i class="bi bi-cart-x display-1 text-muted"></i>
    <p class="mt-3 fs-5 text-muted">Votre panier est vide.</p>
    <a href="index.php" class="btn btn-primary rounded-pill px-4">
      <i class="bi bi-bag me-1"></i>Continuer les achats
    </a>
  </div>

  <?php else: ?>
  <div class="row g-4">

    <!-- Cart items -->
    <div class="col-lg-8">
      <div class="table-responsive rounded-4 shadow-sm bg-white">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th class="cart-table ps-3" style="border-radius:18px 0 0 0;">Produit</th>
              <th class="cart-table text-center">Prix</th>
              <th class="cart-table text-center">Quantité</th>
              <th class="cart-table text-center">Sous-total</th>
              <th class="cart-table text-center pe-3" style="border-radius:0 18px 0 0;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($cart as $pid => $item):
              $catColors = [1=>'#fce4ec', 2=>'#e8f5e9', 3=>'#f3e5f5', 4=>'#fff3e0', 5=>'#e3f2fd'];
              $color    = $catColors[$item['cat_id']] ?? '#f5f5f5';
              $icon     = $catIcons[$item['cat_id']]  ?? 'bi-bag';
              $imgFile  = 'assets/images/products/' . ($item['image'] ?? 'default.jpg');
              $hasImage = !empty($item['image'])
                          && $item['image'] !== 'default.jpg'
                          && file_exists(__DIR__ . '/' . $imgFile);
            ?>
            <tr>
              <!-- Product -->
              <td class="ps-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden"
                       style="width:56px;height:56px;background:<?= $color ?>;">
                    <?php if ($hasImage): ?>
                      <img src="<?= htmlspecialchars($imgFile) ?>"
                           alt="<?= htmlspecialchars($item['nom']) ?>"
                           style="width:56px;height:56px;object-fit:cover;">
                    <?php else: ?>
                      <i class="bi <?= $icon ?> fs-4" style="color:var(--primary);"></i>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="fw-semibold"><?= htmlspecialchars($item['nom']) ?></div>
                    <small class="text-muted">Stock : <?= $item['stock'] ?></small>
                  </div>
                </div>
              </td>

              <!-- Price -->
              <td class="text-center fw-semibold" style="color:var(--primary);">
                <?= number_format($item['prix'], 2) ?> DT
              </td>

              <!-- Quantity update -->
              <td class="text-center">
                <form action="panier_action.php" method="POST"
                      class="d-flex align-items-center justify-content-center gap-1">
                  <input type="hidden" name="action"     value="update">
                  <input type="hidden" name="produit_id" value="<?= $pid ?>">
                  <input type="number" name="quantite" value="<?= $item['quantite'] ?>"
                         min="0" max="<?= $item['stock'] ?>"
                         class="form-control form-control-sm qty-input">
                  <button type="submit"
                          class="btn btn-sm btn-outline-secondary rounded-circle"
                          title="Mettre à jour">
                    <i class="bi bi-check"></i>
                  </button>
                </form>
              </td>

              <!-- Subtotal -->
              <td class="text-center fw-bold">
                <?= number_format($item['prix'] * $item['quantite'], 2) ?> DT
              </td>

              <!-- Remove -->
              <td class="text-center pe-3">
                <form action="panier_action.php" method="POST">
                  <input type="hidden" name="action"     value="remove">
                  <input type="hidden" name="produit_id" value="<?= $pid ?>">
                  <button type="submit"
                          class="btn btn-sm btn-outline-danger rounded-circle"
                          title="Supprimer">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Cart actions -->
      <div class="d-flex gap-2 mt-3">
        <a href="index.php" class="btn btn-outline-primary rounded-pill">
          <i class="bi bi-arrow-left me-1"></i>Continuer les achats
        </a>
        <form action="panier_action.php" method="POST">
          <input type="hidden" name="action" value="clear">
          <button type="submit" class="btn btn-outline-danger rounded-pill">
            <i class="bi bi-trash me-1"></i>Vider le panier
          </button>
        </form>
      </div>
    </div>

    <!-- Summary panel -->
    <div class="col-lg-4">

      <!-- Loyalty points box -->
      <?php if (isset($_SESSION['user']) && $userPoints >= $pointsThreshold): ?>
      <div class="points-card p-3 mb-3 d-flex align-items-center gap-3">
        <i class="bi bi-trophy-fill fs-2"></i>
        <div class="flex-grow-1">
          <div class="fw-bold">Vous avez <span class="points-value"><?= $userPoints ?></span> pts</div>
          <small>Utilisez 100 pts pour -10 DT de remise</small>
        </div>
        <?php if (!$usePoints): ?>
        <a href="panier.php?apply_points=1" class="btn btn-dark btn-sm rounded-pill">Appliquer</a>
        <?php else: ?>
        <a href="panier.php?apply_points=0" class="btn btn-outline-dark btn-sm rounded-pill">Annuler</a>
        <?php endif; ?>
      </div>
      <?php elseif (isset($_SESSION['user']) && $userPoints > 0): ?>
      <div class="alert alert-warning small mb-3">
        <i class="bi bi-star me-1"></i>
        Vous avez <strong><?= $userPoints ?> pts</strong>.
        Encore <?= $pointsThreshold - $userPoints ?> pts pour une remise de 10 DT !
      </div>
      <?php elseif (!isset($_SESSION['user'])): ?>
      <div class="alert alert-info small mb-3">
        <i class="bi bi-info-circle me-1"></i>
        <a href="login.php">Connectez-vous</a> pour cumuler des points fidélité !
      </div>
      <?php endif; ?>

      <!-- Order summary -->
      <div class="cart-summary-box">
        <h5 class="fw-bold mb-3">Récapitulatif</h5>

        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">Sous-total</span>
          <span class="fw-semibold"><?= number_format($subtotal, 2) ?> DT</span>
        </div>

        <?php if ($usePoints): ?>
        <div class="d-flex justify-content-between mb-2 text-success">
          <span><i class="bi bi-trophy-fill me-1"></i>Remise fidélité</span>
          <span>- <?= number_format($pointsDiscount, 2) ?> DT</span>
        </div>
        <?php endif; ?>

        <!-- Frais de livraison -->
        <div class="d-flex justify-content-between mb-2">
          <span class="text-muted">
            <i class="bi bi-truck me-1"></i>Frais de livraison
          </span>
          <span class="fw-semibold" style="color:#e65100;">
            <?= number_format(FRAIS_LIVRAISON, 2) ?> DT
          </span>
        </div>

        <hr>
        <div class="d-flex justify-content-between mb-4">
          <span class="fw-bold fs-5">Total</span>
          <span class="fw-bold fs-5" style="color:var(--primary);">
            <?= number_format($total, 2) ?> DT
          </span>
        </div>

        <?php if (isset($_SESSION['user'])): ?>
        <a href="commande.php" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
          <i class="bi bi-bag-check me-1"></i>Valider la commande
        </a>
        <?php else: ?>
        <a href="login.php" class="btn btn-primary w-100 rounded-pill py-2 fw-bold">
          <i class="bi bi-person me-1"></i>Connexion pour commander
        </a>
        <?php endif; ?>

        <!-- Points to earn -->
        <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem;">
          <i class="bi bi-star-fill" style="color:#f6c90e;"></i>
          Vous gagnerez <strong><?= floor($total / 10) ?> point(s)</strong> avec cette commande
        </p>
      </div>
    </div>

  </div>
  <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>