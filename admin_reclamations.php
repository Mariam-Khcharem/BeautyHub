<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php'); exit;
}

$pageTitle = 'Réclamations & Conseils';

/* ---- Handle actions ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    /* Update reclamation status */
    if ($act === 'update_reclamation') {
        $pdo->prepare("UPDATE reclamations SET statut = ? WHERE id = ?")
            ->execute([$_POST['statut'], (int)$_POST['reclamation_id']]);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Réclamation mise à jour.'];
        header('Location: admin_reclamations.php'); exit;
    }

    /* Respond to conseil */
    if ($act === 'respond_conseil') {
        $reponse = trim($_POST['reponse'] ?? '');
        if ($reponse !== '') {
            $pdo->prepare("UPDATE conseils SET reponse = ? WHERE id = ?")
                ->execute([$reponse, (int)$_POST['conseil_id']]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Réponse enregistrée.'];
        }
        header('Location: admin_reclamations.php#conseils'); exit;
    }
}

/* ---- Fetch data ---- */
$reclamations = $pdo->query(
    "SELECT r.*, u.nom AS user_nom FROM reclamations r
     LEFT JOIN utilisateurs u ON u.id = r.utilisateur_id
     ORDER BY r.created_at DESC"
)->fetchAll();

$conseils = $pdo->query(
    "SELECT c.*, u.nom AS user_nom FROM conseils c
     LEFT JOIN utilisateurs u ON u.id = c.utilisateur_id
     ORDER BY c.created_at DESC"
)->fetchAll();

$statusColors = ['en_attente'=>'warning','en_cours'=>'info','resolue'=>'success','rejetee'=>'danger'];
$statusLabels = ['en_attente'=>'En attente','en_cours'=>'En cours','resolue'=>'Résolue','rejetee'=>'Rejetée'];

require_once 'includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0 section-title">
      <i class="bi bi-exclamation-circle me-2"></i>Réclamations & Conseils
    </h4>
    <a href="admin.php" class="btn btn-outline-secondary rounded-pill btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
  </div>

  <!-- Reclamations -->
  <div class="profile-card p-3 mb-4">
    <h6 class="fw-bold mb-3">
      <i class="bi bi-shield-exclamation me-1" style="color:var(--primary);"></i>
      Réclamations (<?= count($reclamations) ?>)
    </h6>
    <?php if (empty($reclamations)): ?>
    <p class="text-muted small mb-0"><i class="bi bi-check-circle text-success me-1"></i>Aucune réclamation.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-sm table-hover align-middle mb-0">
        <thead style="background:var(--primary-light);">
          <tr>
            <th>#</th><th>Client</th><th>Sujet</th><th>Message</th><th>Date</th><th class="text-center">Statut</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($reclamations as $r): ?>
          <tr>
            <td><small>#<?= $r['id'] ?></small></td>
            <td>
              <div class="small fw-semibold"><?= htmlspecialchars($r['nom']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($r['email']) ?></small>
            </td>
            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['sujet']) ?></span></td>
            <td style="max-width:250px;">
              <small class="text-muted d-block text-truncate" title="<?= htmlspecialchars($r['message']) ?>">
                <?= htmlspecialchars(mb_substr($r['message'], 0, 80)) ?>…
              </small>
            </td>
            <td><small><?= date('d/m/Y', strtotime($r['created_at'])) ?></small></td>
            <td class="text-center">
              <form method="POST" class="d-flex gap-1 justify-content-center">
                <input type="hidden" name="action"        value="update_reclamation">
                <input type="hidden" name="reclamation_id" value="<?= $r['id'] ?>">
                <select name="statut" class="form-select form-select-sm" style="width:auto;">
                  <?php foreach ($statusLabels as $k => $v): ?>
                  <option value="<?= $k ?>" <?= $r['statut']===$k?'selected':'' ?>><?= $v ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm rounded-circle"><i class="bi bi-check"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <!-- Conseils -->
  <div class="profile-card p-3" id="conseils">
    <h6 class="fw-bold mb-3">
      <i class="bi bi-chat-dots me-1" style="color:var(--accent);"></i>
      Demandes de conseils (<?= count($conseils) ?>)
    </h6>
    <?php if (empty($conseils)): ?>
    <p class="text-muted small mb-0"><i class="bi bi-check-circle text-success me-1"></i>Aucune demande.</p>
    <?php else: ?>
    <div class="accordion" id="accordionConseils">
      <?php foreach ($conseils as $i => $c): ?>
      <div class="accordion-item border-0 mb-2 rounded-3 overflow-hidden shadow-sm">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed py-3" type="button"
                  data-bs-toggle="collapse" data-bs-target="#conseil<?= $c['id'] ?>">
            <div class="d-flex align-items-center gap-3 w-100">
              <div>
                <div class="fw-semibold small"><?= htmlspecialchars($c['nom']) ?></div>
                <small class="text-muted"><?= htmlspecialchars($c['email']) ?></small>
              </div>
              <div class="ms-auto me-3">
                <?php if ($c['reponse']): ?>
                <span class="badge bg-success">Répondu</span>
                <?php else: ?>
                <span class="badge bg-warning text-dark">En attente</span>
                <?php endif; ?>
              </div>
              <small class="text-muted"><?= date('d/m/Y', strtotime($c['created_at'])) ?></small>
            </div>
          </button>
        </h2>
        <div id="conseil<?= $c['id'] ?>" class="accordion-collapse collapse"
             data-bs-parent="#accordionConseils">
          <div class="accordion-body pt-2">
            <div class="mb-3 p-3 rounded-3" style="background:var(--primary-light);">
              <strong class="small">Question :</strong>
              <p class="mb-0 small mt-1"><?= nl2br(htmlspecialchars($c['question'])) ?></p>
            </div>

            <?php if ($c['reponse']): ?>
            <div class="mb-3 p-3 rounded-3" style="background:#e8f5e9;">
              <strong class="small text-success">Réponse actuelle :</strong>
              <p class="mb-0 small mt-1"><?= nl2br(htmlspecialchars($c['reponse'])) ?></p>
            </div>
            <?php endif; ?>

            <form method="POST">
              <input type="hidden" name="action"    value="respond_conseil">
              <input type="hidden" name="conseil_id" value="<?= $c['id'] ?>">
              <label class="form-label fw-semibold small">
                <?= $c['reponse'] ? 'Modifier la réponse :' : 'Votre réponse :' ?>
              </label>
              <textarea name="reponse" class="form-control form-control-sm mb-2" rows="4"
                        placeholder="Rédigez votre conseil personnalisé..."><?= htmlspecialchars($c['reponse'] ?? '') ?></textarea>
              <button type="submit" class="btn btn-success btn-sm rounded-pill">
                <i class="bi bi-send me-1"></i><?= $c['reponse'] ? 'Mettre à jour' : 'Envoyer la réponse' ?>
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
