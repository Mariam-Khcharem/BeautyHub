<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Routines Beauté';

/* ══════════════════════════════════════════════════════════════
   STRATÉGIE : 3 requêtes plates, zéro requête dans une boucle
   ══════════════════════════════════════════════════════════════ */

/* 1. Toutes les routines */
$routinesRaw = $pdo->query(
    "SELECT * FROM routines ORDER BY ordre ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* 2. Toutes les étapes d'un coup */
$etapesRaw = $pdo->query(
    "SELECT routine_id, description FROM routine_etapes ORDER BY routine_id, ordre ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* 3. Tous les produits liés d'un coup */
$produitsRaw = $pdo->query(
    "SELECT rp.routine_id, p.*
     FROM produits p
     JOIN routine_produits rp ON rp.produit_id = p.id
     ORDER BY rp.routine_id, rp.ordre ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* ── Indexer étapes par routine_id ── */
$etapesMap = [];
foreach ($etapesRaw as $e) {
    $etapesMap[(int)$e['routine_id']][] = $e['description'];
}

/* ── Indexer produits par routine_id ── */
$produitsMap = [];
foreach ($produitsRaw as $p) {
    $rid = (int)$p['routine_id'];
    unset($p['routine_id']); // on retire la colonne pivot
    $produitsMap[$rid][] = $p;
}

/* ── Assembler ── */
$routines = [];
foreach ($routinesRaw as $r) {
    $rid = (int)$r['id'];
    $r['etapes']   = $etapesMap[$rid]   ?? [];
    $r['produits'] = $produitsMap[$rid] ?? [];
    $routines[] = $r;
}

/* ── Maps icônes / couleurs par catégorie ── */
$catIcons = [
    1 => 'bi-droplet-half', 2 => 'bi-scissors',
    3 => 'bi-palette',      4 => 'bi-flower1',
    5 => 'bi-stars',        6 => 'bi-heart-pulse',
    7 => 'bi-brush',        8 => 'bi-emoji-smile',
];
$catColors = [
    1 => '#fce4ec', 2 => '#e8f5e9',
    3 => '#f3e5f5', 4 => '#fff3e0',
    5 => '#e3f2fd', 6 => '#e8f5e9',
    7 => '#fff3e0', 8 => '#e3f2fd',
];

require_once 'includes/header.php';
?>

<!-- Banner -->
<div class="hero-banner text-white text-center">
  <div class="container">
    <h1 class="display-5 fw-bold mb-2">
      <i class="bi bi-stars me-2"></i>Routines Beauté
    </h1>
    <p class="lead" style="opacity:.85;">
      Des routines personnalisées avec nos meilleurs produits, pour prendre soin de vous chaque jour.
    </p>
  </div>
</div>

<div class="container py-5">

  <?php if (empty($routines)): ?>
  <div class="text-center py-5 text-muted">
    <i class="bi bi-stars display-1"></i>
    <p class="mt-3">Aucune routine disponible pour le moment.</p>
  </div>
  <?php endif; ?>

  <?php foreach ($routines as $routine): ?>
  <div class="routine-card mb-5 shadow-sm">

    <!-- En-tête -->
    <div class="routine-header d-flex align-items-center gap-3">
      <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
           style="width:56px;height:56px;background:rgba(255,255,255,0.2);">
        <i class="bi <?= htmlspecialchars($routine['icone']) ?> fs-3"></i>
      </div>
      <div>
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($routine['titre']) ?></h4>
        <p class="mb-0 small" style="opacity:.85;">
          <?= htmlspecialchars($routine['description']) ?>
        </p>
      </div>
    </div>

    <div class="p-4">
      <div class="row g-4">

        <!-- Étapes -->
        <div class="col-md-5">
          <h6 class="fw-bold mb-3" style="color:var(--primary);">
            <i class="bi bi-list-ol me-1"></i>Étapes de la routine
          </h6>

          <?php if (empty($routine['etapes'])): ?>
            <p class="text-muted small">Aucune étape définie.</p>
          <?php else: ?>
            <?php foreach ($routine['etapes'] as $idx => $etape): ?>
            <div class="routine-step">
              <div class="step-num"><?= $idx + 1 ?></div>
              <span class="small"><?= htmlspecialchars($etape) ?></span>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

          <!-- Conseil -->
          <?php if (!empty($routine['conseil'])): ?>
          <div class="mt-3 p-3 rounded-3"
               style="background:<?= htmlspecialchars($routine['couleur']) ?>;">
            <i class="bi bi-lightbulb-fill me-1"
               style="color:<?= htmlspecialchars($routine['accent']) ?>;"></i>
            <strong style="color:<?= htmlspecialchars($routine['accent']) ?>;">Conseil :</strong>
            <small class="text-muted ms-1">
              <?= htmlspecialchars($routine['conseil']) ?>
            </small>
          </div>
          <?php endif; ?>
        </div>

        <!-- Produits -->
        <div class="col-md-7">
          <h6 class="fw-bold mb-3" style="color:var(--primary);">
            <i class="bi bi-bag-heart me-1"></i>Produits suggérés
          </h6>

          <?php if (empty($routine['produits'])): ?>
            <p class="text-muted small">Aucun produit associé.</p>
          <?php else: ?>
          <div class="row row-cols-1 row-cols-sm-2 g-3">
            <?php foreach ($routine['produits'] as $p):
              $icon     = $catIcons[$p['categorie_id']]  ?? 'bi-bag';
              $color    = $catColors[$p['categorie_id']] ?? '#f5f5f5';
              $imgFile  = 'assets/images/products/' . ($p['image'] ?? 'default.jpg');
              $hasImage = !empty($p['image'])
                          && $p['image'] !== 'default.jpg'
                          && file_exists(__DIR__ . '/' . $imgFile);
            ?>
            <div class="col">
              <div class="d-flex gap-2 align-items-center p-2 rounded-3 bg-white shadow-sm h-100">

                <!-- Image ou icône -->
                <div class="rounded-2 overflow-hidden d-flex align-items-center
                            justify-content-center flex-shrink-0"
                     style="width:48px;height:48px;background:<?= $color ?>;">
                  <?php if ($hasImage): ?>
                    <img src="<?= htmlspecialchars($imgFile) ?>"
                         alt="<?= htmlspecialchars($p['nom']) ?>"
                         style="width:48px;height:48px;object-fit:cover;">
                  <?php else: ?>
                    <i class="bi <?= $icon ?>"
                       style="font-size:1.4rem;
                              color:<?= htmlspecialchars($routine['accent']) ?>;"></i>
                  <?php endif; ?>
                </div>

                <!-- Nom + prix -->
                <div class="flex-grow-1 min-w-0">
                  <div class="fw-semibold small text-truncate">
                    <?= htmlspecialchars($p['nom']) ?>
                  </div>
                  <div class="text-muted" style="font-size:.75rem;">
                    <?= number_format($p['prix'], 2) ?> DT
                  </div>
                </div>

                <!-- Bouton panier -->
                <?php if ($p['stock'] > 0): ?>
                <form action="panier_action.php" method="POST">
                  <input type="hidden" name="action"     value="add">
                  <input type="hidden" name="produit_id" value="<?= $p['id'] ?>">
                  <input type="hidden" name="quantite"   value="1">
                  <input type="hidden" name="redirect"   value="routines.php">
                  <button type="submit"
                          class="btn btn-primary btn-sm rounded-circle flex-shrink-0"
                          title="Ajouter au panier">
                    <i class="bi bi-cart-plus"></i>
                  </button>
                </form>
                <?php else: ?>
                <button class="btn btn-secondary btn-sm rounded-circle flex-shrink-0"
                        disabled title="Rupture de stock">
                  <i class="bi bi-x"></i>
                </button>
                <?php endif; ?>

              </div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- Ajouter toute la routine -->
          <?php
          $dispo = [];
          foreach ($routine['produits'] as $p) {
              if ($p['stock'] > 0) $dispo[] = $p['id'];
          }
          ?>
          <?php if (!empty($dispo)): ?>
          <button type="button"
                  class="btn btn-primary mt-3 rounded-pill w-100 fw-bold"
                  onclick="addRoutine(this)"
                  data-ids="<?= htmlspecialchars(json_encode($dispo)) ?>">
            <i class="bi bi-cart-plus me-1"></i>Ajouter toute la routine au panier
          </button>
          <?php endif; ?>

          <?php endif; ?>
        </div>

      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- CTA -->
  <div class="text-center mt-3">
    <p class="text-muted mb-3">Des questions sur les produits adaptés à votre peau ?</p>
    <a href="contact.php#conseils" class="btn btn-accent rounded-pill px-4 me-2">
      <i class="bi bi-chat-heart me-1"></i>Demander un conseil
    </a>
    <a href="index.php" class="btn btn-outline-primary rounded-pill px-4">
      <i class="bi bi-bag me-1"></i>Voir tous les produits
    </a>
  </div>

</div>

<script>
function addRoutine(btn) {
    const ids = JSON.parse(btn.dataset.ids);
    if (!ids.length) return;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Ajout en cours...';

    let index = 0;
    function next() {
        if (index >= ids.length) {
            window.location.href = 'panier.php';
            return;
        }
        const data = new FormData();
        data.append('action',     'add');
        data.append('produit_id', ids[index]);
        data.append('quantite',   1);
        data.append('redirect',   'routines.php');
        index++;
        fetch('panier_action.php', { method: 'POST', body: data, redirect: 'manual' })
            .then(() => next())
            .catch(() => next());
    }
    next();
}
</script>

<?php require_once 'includes/footer.php'; ?>