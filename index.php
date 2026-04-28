<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$pageTitle = 'Nos Produits';

/* ── Categories ── */
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

/* ── Filters ── */
$catFilter    = isset($_GET['cat'])    ? (int)$_GET['cat']    : 0;
$searchFilter = isset($_GET['search']) ? trim($_GET['search']) : '';

/* ── Products query ── */
$sql    = "SELECT p.*, c.nom AS cat_nom
           FROM produits p
           LEFT JOIN categories c ON p.categorie_id = c.id
           WHERE 1=1";
$params = [];

if ($catFilter > 0) {
    $sql    .= " AND p.categorie_id = ?";
    $params[] = $catFilter;
}
if ($searchFilter !== '') {
    $sql    .= " AND p.nom LIKE ?";
    $params[] = "%$searchFilter%";
}
$sql .= " ORDER BY p.categorie_id, p.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

/* ── Icon / color map per category ── */
$catIcons  = [1=>'bi-droplet-half', 2=>'bi-scissors',  3=>'bi-palette', 4=>'bi-flower1', 5=>'bi-stars'];
$catColors = [1=>'#fce4ec',         2=>'#e8f5e9',       3=>'#f3e5f5',   4=>'#fff3e0',    5=>'#e3f2fd'];

require_once 'includes/header.php';
?>

<!-- Hero Banner -->
<div class="hero-banner text-center text-white">
  <div class="container">
    <h1 class="display-5 fw-bold mb-2">Votre Beauté, Notre Passion</h1>
    <p class="lead mb-4" style="opacity:.85;">
      Découvrez notre sélection de soins, maquillage, parfums &amp; bien plus
    </p>
    <form method="GET" class="d-flex justify-content-center gap-2">
      <?php if ($catFilter): ?>
        <input type="hidden" name="cat" value="<?= $catFilter ?>">
      <?php endif; ?>
      <input type="search" name="search" class="form-control rounded-pill shadow-sm"
             style="max-width:400px;"
             placeholder="Rechercher un produit..."
             value="<?= htmlspecialchars($searchFilter) ?>">
      <button type="submit" class="btn btn-light text-primary fw-bold rounded-pill px-4">
        <i class="bi bi-search me-1"></i>Chercher
      </button>
    </form>
  </div>
</div>

<div class="container py-4">

  <!-- Category filter pills -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <a href="index.php<?= $searchFilter ? '?search='.urlencode($searchFilter) : '' ?>"
       class="btn cat-btn <?= !$catFilter ? 'btn-primary' : 'btn-outline-primary' ?>">
      <i class="bi bi-grid me-1"></i>Tous
    </a>
    <?php foreach ($categories as $cat): ?>
    <a href="index.php?cat=<?= $cat['id'] ?><?= $searchFilter ? '&search='.urlencode($searchFilter) : '' ?>"
       class="btn cat-btn <?= $catFilter === (int)$cat['id'] ? 'btn-primary' : 'btn-outline-primary' ?>">
      <i class="bi <?= $catIcons[$cat['id']] ?? 'bi-tag' ?> me-1"></i>
      <?= htmlspecialchars($cat['nom']) ?>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Result count -->
  <p class="text-muted small mb-3">
    <?= count($products) ?> produit(s) trouvé(s)
    <?= $catFilter    ? 'dans cette catégorie'                                   : '' ?>
    <?= $searchFilter ? 'pour « '.htmlspecialchars($searchFilter).' »' : '' ?>
  </p>

  <!-- Products grid -->
  <?php if (empty($products)): ?>
  <div class="text-center py-5">
    <i class="bi bi-search display-1 text-muted"></i>
    <p class="mt-3 text-muted fs-5">Aucun produit trouvé.</p>
    <a href="index.php" class="btn btn-outline-primary rounded-pill">Voir tous les produits</a>
  </div>
  <?php else: ?>
  <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
    <?php
    $catTextColors = [1=>'#b71c1c', 2=>'#1b5e20', 3=>'#4a148c', 4=>'#e65100', 5=>'#01579b'];
    foreach ($products as $p):
      $icon      = $catIcons[$p['categorie_id']]      ?? 'bi-bag';
      $color     = $catColors[$p['categorie_id']]     ?? '#f5f5f5';
      $textColor = $catTextColors[$p['categorie_id']] ?? '#555';
      $imgFile   = 'assets/images/products/' . $p['image'];
      /* Vérifie si une vraie image existe sur le disque */
      $hasImage  = !empty($p['image'])
                   && $p['image'] !== 'default.jpg'
                   && file_exists(__DIR__ . '/' . $imgFile);
      $inCart    = isset($_SESSION['cart'][$p['id']]);
    ?>
    <div class="col">
      <div class="card product-card h-100">

        <!-- Image / placeholder -->
        <div class="product-img-wrap" style="background:<?= $color ?>;">
          <?php if ($hasImage): ?>
            <img src="<?= htmlspecialchars($imgFile) ?>"
                 alt="<?= htmlspecialchars($p['nom']) ?>"
                 class="product-img"
                 style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <i class="bi <?= $icon ?> product-icon" style="color:<?= $textColor ?>;"></i>
          <?php endif; ?>

          <?php if ($p['stock'] == 0): ?>
            <span class="badge bg-danger stock-badge">Rupture</span>
          <?php elseif ($p['stock'] <= 5): ?>
            <span class="badge bg-warning text-dark stock-badge">Limité</span>
          <?php endif; ?>
        </div>

        <div class="card-body d-flex flex-column pb-1">
          <span class="text-muted" style="font-size:.75rem;">
            <i class="bi <?= $icon ?> me-1"></i><?= htmlspecialchars($p['cat_nom'] ?? '') ?>
          </span>
          <h6 class="card-title fw-bold mt-1 mb-1"><?= htmlspecialchars($p['nom']) ?></h6>
          <p class="card-text text-muted flex-grow-1" style="font-size:.82rem;">
            <?= htmlspecialchars(mb_substr($p['description'] ?? '', 0, 70)) ?>…
          </p>
          <div class="mt-2">
            <span class="fw-bold fs-5" style="color:var(--primary);">
              <?= number_format($p['prix'], 2) ?> DT
            </span>
            <small class="text-muted ms-2">Stock : <?= $p['stock'] ?></small>
          </div>
        </div>

        <div class="card-footer bg-transparent border-0 pb-3 px-3">
          <?php if ($p['stock'] > 0): ?>
          <form action="panier_action.php" method="POST" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="action"     value="add">
            <input type="hidden" name="produit_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="redirect"   value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
            <input type="number" name="quantite" value="1" min="1" max="<?= $p['stock'] ?>"
                   class="form-control form-control-sm qty-input">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 rounded-pill">
              <i class="bi bi-cart-plus me-1"></i><?= $inCart ? 'Ajouter' : 'Au panier' ?>
            </button>
          </form>
          <?php else: ?>
          <button class="btn btn-secondary btn-sm w-100 rounded-pill" disabled>
            <i class="bi bi-x-circle me-1"></i>Indisponible
          </button>
          <?php endif; ?>
        </div>

      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>