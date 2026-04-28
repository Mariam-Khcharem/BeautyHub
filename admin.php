<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Accès réservé aux administrateurs.'];
    header('Location: index.php');
    exit;
}

$pageTitle = 'Administration';

/* ---- Stats ---- */
$totalProduits   = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$totalUsers      = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
$totalCommandes  = $pdo->query("SELECT COUNT(*) FROM commandes")->fetchColumn();
$totalCA         = $pdo->query("SELECT IFNULL(SUM(total),0) FROM commandes WHERE statut != 'annulee'")->fetchColumn();
$commandesAttente= $pdo->query("SELECT COUNT(*) FROM commandes WHERE statut='en_attente'")->fetchColumn();
$reclamAttente   = $pdo->query("SELECT COUNT(*) FROM reclamations WHERE statut='en_attente'")->fetchColumn();
$conseilsAttente = $pdo->query("SELECT COUNT(*) FROM conseils WHERE reponse IS NULL")->fetchColumn();

/* ---- Recent orders ---- */
$recentOrders = $pdo->query(
    "SELECT c.*, u.nom AS user_nom
     FROM commandes c JOIN utilisateurs u ON u.id = c.utilisateur_id
     ORDER BY c.date_commande DESC LIMIT 5"
)->fetchAll();

/* ---- Low stock ---- */
$lowStock = $pdo->query(
    "SELECT * FROM produits WHERE stock <= 5 ORDER BY stock ASC LIMIT 8"
)->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center gap-2 mb-4">
    <div class="profile-avatar" style="width:48px;height:48px;font-size:1.5rem;"><i class="bi bi-gear"></i></div>
    <div>
      <h4 class="fw-bold mb-0">Tableau de bord Admin</h4>
      <small class="text-muted">Bienvenue, <?= htmlspecialchars($_SESSION['user']['nom']) ?></small>
    </div>
  </div>

  <!-- Stats cards -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid var(--primary)!important;">
        <div class="card-body">
          <div class="text-muted small">Produits</div>
          <div class="fw-bold fs-3" style="color:var(--primary);"><?= $totalProduits ?></div>
          <a href="admin_produits.php" class="small" style="color:var(--primary);">Gérer →</a>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0277bd!important;">
        <div class="card-body">
          <div class="text-muted small">Clients</div>
          <div class="fw-bold fs-3 text-primary"><?= $totalUsers ?></div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #2e7d32!important;">
        <div class="card-body">
          <div class="text-muted small">Commandes</div>
          <div class="fw-bold fs-3" style="color:#2e7d32;"><?= $totalCommandes ?></div>
          <a href="admin_commandes.php" class="small text-success">Gérer →</a>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f6c90e!important;">
        <div class="card-body">
          <div class="text-muted small">Chiffre d'affaires</div>
          <div class="fw-bold fs-3" style="color:#e65100;"><?= number_format($totalCA, 2) ?> DT</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Alert badges -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <?php if ($commandesAttente > 0): ?>
    <a href="admin_commandes.php" class="badge bg-warning text-dark fs-6 text-decoration-none p-2">
      <i class="bi bi-clock me-1"></i><?= $commandesAttente ?> commande(s) en attente
    </a>
    <?php endif; ?>
    <?php if ($reclamAttente > 0): ?>
    <a href="admin_reclamations.php" class="badge bg-danger fs-6 text-decoration-none p-2">
      <i class="bi bi-exclamation-circle me-1"></i><?= $reclamAttente ?> réclamation(s) à traiter
    </a>
    <?php endif; ?>
    <?php if ($conseilsAttente > 0): ?>
    <a href="admin_reclamations.php#conseils" class="badge bg-info fs-6 text-decoration-none p-2">
      <i class="bi bi-chat-dots me-1"></i><?= $conseilsAttente ?> conseil(s) sans réponse
    </a>
    <?php endif; ?>
  </div>

  <div class="row g-4">

    <!-- Recent orders -->
    <div class="col-lg-7">
      <div class="profile-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-bag me-1" style="color:var(--primary);"></i>Dernières commandes</h6>
          <a href="admin_commandes.php" class="btn btn-outline-primary btn-sm rounded-pill">Tout voir</a>
        </div>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead style="background:var(--primary-light);">
              <tr>
                <th>#</th><th>Client</th><th>Date</th><th>Total</th><th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentOrders)): ?>
              <tr><td colspan="5" class="text-center text-muted py-3">Aucune commande</td></tr>
              <?php else: ?>
              <?php foreach ($recentOrders as $o):
                $sc = ['en_attente'=>'warning','confirmee'=>'info','expediee'=>'primary','livree'=>'success','annulee'=>'danger'];
                $sl = ['en_attente'=>'En attente','confirmee'=>'Confirmée','expediee'=>'Expédiée','livree'=>'Livrée','annulee'=>'Annulée'];
              ?>
              <tr>
                <td><strong>#<?= $o['id'] ?></strong></td>
                <td><?= htmlspecialchars($o['user_nom']) ?></td>
                <td><small><?= date('d/m/Y', strtotime($o['date_commande'])) ?></small></td>
                <td class="fw-semibold"><?= number_format($o['total'], 2) ?> DT</td>
                <td><span class="badge bg-<?= $sc[$o['statut']] ?? 'secondary' ?>">
                  <?= $sl[$o['statut']] ?? $o['statut'] ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Low stock -->
    <div class="col-lg-5">
      <div class="profile-card p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle me-1 text-warning"></i>Stock faible</h6>
          <a href="admin_produits.php" class="btn btn-outline-warning btn-sm rounded-pill">Gérer</a>
        </div>
        <?php if (empty($lowStock)): ?>
        <p class="text-success small mb-0"><i class="bi bi-check-circle me-1"></i>Tous les stocks sont OK.</p>
        <?php else: ?>
        <ul class="list-unstyled mb-0">
          <?php foreach ($lowStock as $p): ?>
          <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="small fw-semibold"><?= htmlspecialchars($p['nom']) ?></span>
            <span class="badge <?= $p['stock'] == 0 ? 'bg-danger' : 'bg-warning text-dark' ?>">
              <?= $p['stock'] == 0 ? 'Rupture' : $p['stock'].' restant(s)' ?>
            </span>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Quick links -->
  <div class="row g-3 mt-2">
    <div class="col-md-3">
      <a href="admin_produits.php" class="btn btn-outline-primary w-100 rounded-pill py-2">
        <i class="bi bi-box-seam me-1"></i>Gérer les produits
      </a>
    </div>
    <div class="col-md-3">
      <a href="admin_commandes.php" class="btn btn-outline-success w-100 rounded-pill py-2">
        <i class="bi bi-bag-check me-1"></i>Gérer les commandes
      </a>
    </div>
    <div class="col-md-3">
      <a href="admin_reclamations.php" class="btn btn-outline-danger w-100 rounded-pill py-2">
        <i class="bi bi-exclamation-circle me-1"></i>Réclamations & Conseils
      </a>
    </div>
    <div class="col-md-3">
      <a href="index.php" class="btn btn-outline-secondary w-100 rounded-pill py-2">
        <i class="bi bi-shop me-1"></i>Voir la boutique
      </a>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
