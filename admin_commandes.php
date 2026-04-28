<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php'); exit;
}

$pageTitle = 'Gestion des Commandes';

/* ── Update order status ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commande_id'], $_POST['statut'])) {
    $validStatuts = ['en_attente','confirmee','expediee','livree','annulee'];
    $statut = $_POST['statut'];
    if (in_array($statut, $validStatuts)) {
        $pdo->prepare("UPDATE commandes SET statut = ? WHERE id = ?")
            ->execute([$statut, (int)$_POST['commande_id']]);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Statut mis à jour.'];
    }
    header('Location: admin_commandes.php'); exit;
}

/* ── Filter ── */
$filterStatut = $_GET['statut'] ?? '';

$sql = "SELECT c.*, u.nom AS user_nom, u.email AS user_email,
               COUNT(lc.id) AS nb_articles
        FROM commandes c
        JOIN utilisateurs u ON u.id = c.utilisateur_id
        LEFT JOIN ligne_commande lc ON lc.commande_id = c.id";
$params = [];
if ($filterStatut) {
    $sql .= " WHERE c.statut = ?";
    $params[] = $filterStatut;
}
$sql .= " GROUP BY c.id ORDER BY c.date_commande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commandes = $stmt->fetchAll();

$statusLabels = ['en_attente'=>'En attente','confirmee'=>'Confirmée',
                 'expediee'=>'Expédiée','livree'=>'Livrée','annulee'=>'Annulée'];
$statusColors = ['en_attente'=>'warning','confirmee'=>'info',
                 'expediee'=>'primary','livree'=>'success','annulee'=>'danger'];

require_once 'includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0 section-title">
      <i class="bi bi-bag-check me-2"></i>Gestion des commandes
    </h4>
    <a href="admin.php" class="btn btn-outline-secondary rounded-pill btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
  </div>

  <!-- Status filter tabs -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a href="admin_commandes.php"
       class="btn btn-sm rounded-pill <?= !$filterStatut ? 'btn-primary' : 'btn-outline-primary' ?>">
      Toutes (<?= count($commandes) ?>)
    </a>
    <?php foreach ($statusLabels as $key => $label):
      $count = $pdo->prepare("SELECT COUNT(*) FROM commandes WHERE statut=?");
      $count->execute([$key]);
      $n = $count->fetchColumn();
    ?>
    <a href="admin_commandes.php?statut=<?= $key ?>"
       class="btn btn-sm rounded-pill btn-outline-<?= $statusColors[$key] ?>
              <?= $filterStatut===$key ? 'active' : '' ?>">
      <?= $label ?> (<?= $n ?>)
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Orders table -->
  <div class="profile-card p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background:var(--primary-light);">
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Date</th>
            <th>Adresse livraison</th>
            <th class="text-center">Articles</th>
            <th class="text-end">Livraison</th>
            <th class="text-end">Total</th>
            <th class="text-center">Statut</th>
            <th class="text-center">Changer statut</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($commandes)): ?>
          <tr>
            <td colspan="9" class="text-center py-4 text-muted">Aucune commande.</td>
          </tr>
          <?php else: ?>
          <?php foreach ($commandes as $c): ?>
          <tr>
            <td><strong>#<?= $c['id'] ?></strong></td>
            <td>
              <div class="fw-semibold small"><?= htmlspecialchars($c['user_nom']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($c['user_email']) ?></small>
            </td>
            <td><small><?= date('d/m/Y H:i', strtotime($c['date_commande'])) ?></small></td>
            <td>
              <small class="text-muted">
                <?= $c['adresse_livraison']
                    ? htmlspecialchars($c['adresse_livraison'])
                    : '<span class="text-danger">—</span>' ?>
              </small>
            </td>
            <td class="text-center"><?= $c['nb_articles'] ?></td>
            <td class="text-end small" style="color:#e65100;">
              <?= number_format($c['frais_livraison'] ?? 8, 2) ?> DT
            </td>
            <td class="text-end fw-bold" style="color:var(--primary);">
              <?= number_format($c['total'], 2) ?> DT
            </td>
            <td class="text-center">
              <span class="badge bg-<?= $statusColors[$c['statut']] ?? 'secondary' ?>">
                <?= $statusLabels[$c['statut']] ?? $c['statut'] ?>
              </span>
            </td>
            <td class="text-center">
              <form method="POST" class="d-flex gap-1 justify-content-center">
                <input type="hidden" name="commande_id" value="<?= $c['id'] ?>">
                <select name="statut" class="form-select form-select-sm" style="width:auto;">
                  <?php foreach ($statusLabels as $key => $label): ?>
                  <option value="<?= $key ?>"
                          <?= $c['statut'] === $key ? 'selected' : '' ?>>
                    <?= $label ?>
                  </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit"
                        class="btn btn-primary btn-sm rounded-circle"
                        title="Enregistrer">
                  <i class="bi bi-check"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($commandes)): ?>
    <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center small text-muted">
      <span><?= count($commandes) ?> commande(s)</span>
      <span>
        Total : <strong>
          <?= number_format(array_sum(array_column($commandes, 'total')), 2) ?> DT
        </strong>
        (dont frais livraison :
        <strong>
          <?= number_format(count($commandes) * 8, 2) ?> DT
        </strong>)
      </span>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>