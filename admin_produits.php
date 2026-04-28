<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php'); exit;
}

$pageTitle = 'Gestion des Produits';

/* ─── Handle actions ─── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';

    /* DELETE */
    if ($act === 'delete') {
        $id = (int)$_POST['produit_id'];
        /* Supprimer l'image si ce n'est pas default.jpg */
        $imgRow = $pdo->prepare("SELECT image FROM produits WHERE id = ?");
        $imgRow->execute([$id]);
        $oldImg = $imgRow->fetchColumn();
        if ($oldImg && $oldImg !== 'default.jpg') {
            $oldPath = __DIR__ . '/assets/images/products/' . $oldImg;
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $pdo->prepare("DELETE FROM produits WHERE id = ?")->execute([$id]);
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Produit supprimé.'];
        header('Location: admin_produits.php'); exit;
    }

    /* ADD / UPDATE */
    if ($act === 'add' || $act === 'update') {
        $nom   = trim($_POST['nom']           ?? '');
        $desc  = trim($_POST['description']   ?? '');
        $prix  = (float)($_POST['prix']       ?? 0);
        $stock = (int)($_POST['stock']        ?? 0);
        $cat   = (int)($_POST['categorie_id'] ?? 0);
        $id    = (int)($_POST['produit_id']   ?? 0);

        /* Garder l'ancienne image par défaut */
        $image = trim($_POST['image_name'] ?? 'default.jpg');

        /* Upload nouvelle image */
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed)) {
                /* Créer le dossier si inexistant */
                $uploadDir = __DIR__ . '/assets/images/products/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $filename = 'prod_' . time() . '_' . rand(100,999) . '.' . $ext;
                $dest     = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    /* Supprimer l'ancienne image si update */
                    if ($act === 'update' && $image !== 'default.jpg') {
                        $old = __DIR__ . '/assets/images/products/' . $image;
                        if (file_exists($old)) unlink($old);
                    }
                    $image = $filename;
                }
            } else {
                $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Format image non supporté (jpg, png, webp, gif uniquement).'];
                header('Location: admin_produits.php' . ($act==='update' ? '?edit='.$id : '')); exit;
            }
        }

        if ($nom === '' || $prix <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Nom et prix sont obligatoires.'];
        } elseif ($act === 'add') {
            $pdo->prepare(
                "INSERT INTO produits (nom, description, prix, stock, image, categorie_id)
                 VALUES (?,?,?,?,?,?)"
            )->execute([$nom, $desc, $prix, $stock, $image, $cat ?: null]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => "Produit « $nom » ajouté."];
        } else {
            $pdo->prepare(
                "UPDATE produits SET nom=?, description=?, prix=?, stock=?, image=?, categorie_id=?
                 WHERE id=?"
            )->execute([$nom, $desc, $prix, $stock, $image, $cat ?: null, $id]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => "Produit « $nom » mis à jour."];
        }
        header('Location: admin_produits.php'); exit;
    }
}

/* ─── Edit pre-fill ─── */
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editing = $stmt->fetch();
}

/* ─── Products list ─── */
$products = $pdo->query(
    "SELECT p.*, c.nom AS cat_nom FROM produits p
     LEFT JOIN categories c ON c.id = p.categorie_id
     ORDER BY p.categorie_id, p.nom"
)->fetchAll();

/* ─── Categories ─── */
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

require_once 'includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0 section-title">
      <i class="bi bi-box-seam me-2"></i>Gestion des produits
    </h4>
    <a href="admin.php" class="btn btn-outline-secondary rounded-pill btn-sm">
      <i class="bi bi-arrow-left me-1"></i>Dashboard
    </a>
  </div>

  <div class="row g-4">

    <!-- Add / Edit form -->
    <div class="col-lg-4">
      <div class="form-card p-4">
        <h6 class="fw-bold mb-3">
          <?= $editing
            ? '<i class="bi bi-pencil me-1"></i>Modifier le produit'
            : '<i class="bi bi-plus-circle me-1"></i>Ajouter un produit' ?>
        </h6>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action"     value="<?= $editing ? 'update' : 'add' ?>">
          <input type="hidden" name="produit_id" value="<?= $editing['id'] ?? '' ?>">
          <input type="hidden" name="image_name" value="<?= htmlspecialchars($editing['image'] ?? 'default.jpg') ?>">

          <div class="mb-3">
            <label class="form-label fw-semibold small">
              Nom <span class="text-danger">*</span>
            </label>
            <input type="text" name="nom" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($editing['nom'] ?? '') ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Description</label>
            <textarea name="description" class="form-control form-control-sm"
                      rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold small">
                Prix (DT) <span class="text-danger">*</span>
              </label>
              <input type="number" name="prix" class="form-control form-control-sm"
                     step="0.01" min="0"
                     value="<?= $editing['prix'] ?? '' ?>" required>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold small">Stock</label>
              <input type="number" name="stock" class="form-control form-control-sm"
                     min="0" value="<?= $editing['stock'] ?? 0 ?>">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold small">Catégorie</label>
            <select name="categorie_id" class="form-select form-select-sm">
              <option value="">-- Aucune --</option>
              <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"
                <?= isset($editing['categorie_id']) && $editing['categorie_id'] == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nom']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Image upload + preview -->
          <div class="mb-4">
            <label class="form-label fw-semibold small">Image produit</label>

            <?php if ($editing && $editing['image'] !== 'default.jpg'): ?>
            <div class="mb-2 text-center">
              <img src="assets/images/products/<?= htmlspecialchars($editing['image']) ?>"
                   alt="Image actuelle"
                   class="rounded-3 border"
                   style="width:100%;max-height:140px;object-fit:cover;">
              <small class="text-muted d-block mt-1">Image actuelle</small>
            </div>
            <?php endif; ?>

            <input type="file" name="image" id="imageInput"
                   class="form-control form-control-sm" accept="image/*"
                   onchange="previewImage(this)">
            <small class="text-muted">JPG, PNG, WEBP, GIF — max 5 Mo</small>

            <!-- Aperçu avant upload -->
            <div id="imagePreview" class="mt-2 text-center" style="display:none;">
              <img id="previewImg" src="" alt="Aperçu"
                   class="rounded-3 border"
                   style="width:100%;max-height:140px;object-fit:cover;">
              <small class="text-muted d-block mt-1">Aperçu nouvelle image</small>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm rounded-pill flex-grow-1">
              <i class="bi bi-<?= $editing ? 'check-circle' : 'plus-circle' ?> me-1"></i>
              <?= $editing ? 'Enregistrer' : 'Ajouter' ?>
            </button>
            <?php if ($editing): ?>
            <a href="admin_produits.php" class="btn btn-outline-secondary btn-sm rounded-pill">
              Annuler
            </a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- Products list -->
    <div class="col-lg-8">
      <div class="profile-card p-3">
        <h6 class="fw-bold mb-3">
          <i class="bi bi-list me-1" style="color:var(--primary);"></i>
          <?= count($products) ?> produit(s) en catalogue
        </h6>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead style="background:var(--primary-light);">
              <tr>
                <th>#</th>
                <th>Image</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th class="text-end">Prix</th>
                <th class="text-center">Stock</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $catColors   = [1=>'#fce4ec',2=>'#e8f5e9',3=>'#f3e5f5',4=>'#fff3e0',5=>'#e3f2fd'];
              $catIcons    = [1=>'bi-droplet-half',2=>'bi-scissors',3=>'bi-palette',4=>'bi-flower1',5=>'bi-stars'];
              foreach ($products as $p):
                $imgFile  = 'assets/images/products/' . $p['image'];
                $hasImage = $p['image'] !== 'default.jpg' && file_exists(__DIR__ . '/' . $imgFile);
                $color    = $catColors[$p['categorie_id']] ?? '#f5f5f5';
                $icon     = $catIcons[$p['categorie_id']]  ?? 'bi-bag';
              ?>
              <tr class="<?= $p['stock'] == 0 ? 'table-danger' : ($p['stock'] <= 5 ? 'table-warning' : '') ?>">
                <td><small class="text-muted">#<?= $p['id'] ?></small></td>

                <!-- Image miniature -->
                <td>
                  <div class="rounded-2 overflow-hidden d-flex align-items-center justify-content-center"
                       style="width:44px;height:44px;background:<?= $color ?>;flex-shrink:0;">
                    <?php if ($hasImage): ?>
                      <img src="<?= htmlspecialchars($imgFile) ?>"
                           alt="<?= htmlspecialchars($p['nom']) ?>"
                           style="width:44px;height:44px;object-fit:cover;">
                    <?php else: ?>
                      <i class="bi <?= $icon ?>" style="font-size:1.2rem;color:#999;"></i>
                    <?php endif; ?>
                  </div>
                </td>

                <td class="fw-semibold small"><?= htmlspecialchars($p['nom']) ?></td>
                <td><small class="text-muted"><?= htmlspecialchars($p['cat_nom'] ?? '—') ?></small></td>
                <td class="text-end small"><?= number_format($p['prix'], 2) ?> DT</td>
                <td class="text-center">
                  <span class="badge <?= $p['stock']==0 ? 'bg-danger' : ($p['stock']<=5 ? 'bg-warning text-dark' : 'bg-success') ?>">
                    <?= $p['stock'] ?>
                  </span>
                </td>
                <td class="text-center">
                  <div class="d-flex gap-1 justify-content-center">
                    <a href="admin_produits.php?edit=<?= $p['id'] ?>"
                       class="btn btn-outline-primary btn-sm rounded-circle" title="Modifier">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="POST" onsubmit="return confirm('Supprimer ce produit ?')">
                      <input type="hidden" name="action"     value="delete">
                      <input type="hidden" name="produit_id" value="<?= $p['id'] ?>">
                      <button type="submit"
                              class="btn btn-outline-danger btn-sm rounded-circle"
                              title="Supprimer">
                        <i class="bi bi-trash3"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const img     = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>