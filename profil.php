<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Mon Profil';
$userId    = $_SESSION['user']['id'];

/* ---- Fetch fresh user data ---- */
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

/* Sync session points */
$_SESSION['user']['points'] = (int)($user['points_fidelite'] ?? 0);
$userPoints = $_SESSION['user']['points'];

/* ---- Order history ---- */
$stmt = $pdo->prepare(
    "SELECT c.id, c.date_commande, c.total, c.statut,
            COUNT(lc.id) AS nb_articles
     FROM commandes c
     LEFT JOIN ligne_commande lc ON lc.commande_id = c.id
     WHERE c.utilisateur_id = ?
     GROUP BY c.id
     ORDER BY c.date_commande DESC"
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

/* ---- Loyalty progress ---- */
$ptsForDiscount = 100;
$ptsProgress    = min(100, ($userPoints % $ptsForDiscount) / $ptsForDiscount * 100);
$discountsAvailable = (int)floor($userPoints / $ptsForDiscount);

require_once 'includes/header.php';
?>

<div class="container py-4">
  <h2 class="section-title mb-4">Mon Profil</h2>

  <div class="row g-4">

    <!-- User info card -->
    <div class="col-lg-4">
      <div class="profile-card p-4 text-center mb-4">
        <div class="profile-avatar mx-auto mb-3">
          <i class="bi bi-person"></i>
        </div>
        <h5 class="fw-bold"><?= htmlspecialchars($user['nom']) ?></h5>
        <p class="text-muted small mb-1">
          <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
        </p>
        <?php if ($user['adresse']): ?>
        <p class="text-muted small mb-1">
          <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($user['adresse']) ?>
        </p>
        <?php endif; ?>
        <p class="text-muted small mb-3">
          <i class="bi bi-calendar me-1"></i>
          Membre depuis <?= date('M Y', strtotime($user['created_at'])) ?>
        </p>
        <span class="badge rounded-pill px-3 py-2" style="background:var(--primary-light);color:var(--primary-dark);">
          <?= ucfirst($user['role']) ?>
        </span>
      </div>

      <!-- Points card -->
      <div class="points-card p-4 text-center">
        <div class="mb-1" style="font-size:.85rem;opacity:.8;">Points Fidélité</div>
        <div class="points-value"><?= $userPoints ?></div>
        <div class="mb-3" style="font-size:.85rem;opacity:.8;">points cumulés</div>

        <!-- Progress to next discount -->
        <div class="mb-2" style="font-size:.8rem;">
          <?php if ($discountsAvailable > 0): ?>
            <i class="bi bi-trophy-fill me-1"></i>
            <strong><?= $discountsAvailable ?> remise(s)</strong> de 10 DT disponible(s) !
          <?php else: ?>
            Encore <strong><?= $ptsForDiscount - ($userPoints % $ptsForDiscount) ?> pts</strong> pour -10 DT
          <?php endif; ?>
        </div>

        <div class="progress mb-3" style="height:8px;background:rgba(0,0,0,0.15);">
          <div class="progress-bar" style="width:<?= $ptsProgress ?>%;background:rgba(0,0,0,0.4);border-radius:4px;"></div>
        </div>

        <?php if ($discountsAvailable > 0): ?>
        <a href="panier.php?apply_points=1" class="btn btn-dark btn-sm rounded-pill px-3">
          <i class="bi bi-bag-check me-1"></i>Utiliser mes points
        </a>
        <?php endif; ?>

        <div class="mt-3" style="font-size:.75rem;opacity:.75;">
          Règle : 1 point / 10 DT dépensé &mdash; 100 pts = 10 DT de remise
        </div>
      </div>
    </div>

    <!-- Orders -->
    <div class="col-lg-8">
      <div class="profile-card p-4">
        <h5 class="fw-bold mb-3">
          <i class="bi bi-bag-heart me-2" style="color:var(--primary);"></i>Mes commandes
        </h5>

        <?php if (empty($orders)): ?>
        <div class="text-center py-4 text-muted">
          <i class="bi bi-bag-x display-4"></i>
          <p class="mt-3">Aucune commande pour l'instant.</p>
          <a href="index.php" class="btn btn-primary rounded-pill">Découvrir nos produits</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr style="background:var(--primary-light);">
                <th>N° Commande</th>
                <th>Date</th>
                <th class="text-center">Articles</th>
                <th class="text-end">Total</th>
                <th class="text-center">Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o):
                $statusColors = [
                    'en_attente' => 'warning',
                    'confirmee'  => 'info',
                    'expediee'   => 'primary',
                    'livree'     => 'success',
                    'annulee'    => 'danger',
                ];
                $statusLabels = [
                    'en_attente' => 'En attente',
                    'confirmee'  => 'Confirmée',
                    'expediee'   => 'Expédiée',
                    'livree'     => 'Livrée',
                    'annulee'    => 'Annulée',
                ];
                $sc = $statusColors[$o['statut']] ?? 'secondary';
                $sl = $statusLabels[$o['statut']]  ?? ucfirst($o['statut']);
              ?>
              <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td><?= date('d/m/Y H:i', strtotime($o['date_commande'])) ?></td>
                <td class="text-center"><?= $o['nb_articles'] ?> article(s)</td>
                <td class="text-end fw-semibold" style="color:var(--primary);">
                  <?= number_format($o['total'], 2) ?> DT
                </td>
                <td class="text-center">
                  <span class="badge bg-<?= $sc ?>"><?= $sl ?></span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Stats -->
        <div class="row text-center mt-3 g-3">
          <div class="col-4">
            <div class="rounded-3 p-3" style="background:var(--primary-light);">
              <div class="fw-bold fs-4" style="color:var(--primary);"><?= count($orders) ?></div>
              <small class="text-muted">Commande(s)</small>
            </div>
          </div>
          <div class="col-4">
            <div class="rounded-3 p-3" style="background:#e8f5e9;">
              <div class="fw-bold fs-4" style="color:#2e7d32;">
                <?= number_format(array_sum(array_column($orders, 'total')), 2) ?> DT
              </div>
              <small class="text-muted">Total dépensé</small>
            </div>
          </div>
          <div class="col-4">
            <div class="rounded-3 p-3" style="background:#fff3e0;">
              <div class="fw-bold fs-4" style="color:#e65100;"><?= $userPoints ?></div>
              <small class="text-muted">Points fidélité</small>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
