<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* Block direct GET access */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action    = $_POST['action']     ?? '';
$produitId = (int)($_POST['produit_id'] ?? 0);
$redirect  = $_POST['redirect']   ?? 'panier.php';
$isAjax    = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
             || (isset($_SERVER['HTTP_ACCEPT'])
                 && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

/* Whitelist redirect */
$allowed = ['index.php', 'panier.php', 'routines.php'];
$redirectBase = basename(parse_url($redirect, PHP_URL_PATH));
if (!in_array($redirectBase, $allowed)) {
    $redirect = 'index.php';
}

switch ($action) {

    /* ── Add to cart ── */
    case 'add':
        $qty = max(1, (int)($_POST['quantite'] ?? 1));

        $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
        $stmt->execute([$produitId]);
        $product = $stmt->fetch();

        if (!$product || $product['stock'] <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'text' => 'Produit indisponible.'];
            break;
        }

        if (isset($_SESSION['cart'][$produitId])) {
            $newQty = $_SESSION['cart'][$produitId]['quantite'] + $qty;
            $newQty = min($newQty, $product['stock']);
            $_SESSION['cart'][$produitId]['quantite'] = $newQty;
            /* Mettre à jour l'image au cas où elle aurait changé */
            $_SESSION['cart'][$produitId]['image'] = $product['image'];
        } else {
            $_SESSION['cart'][$produitId] = [
                'nom'      => $product['nom'],
                'prix'     => (float)$product['prix'],
                'quantite' => min($qty, $product['stock']),
                'image'    => $product['image'],       // ← image depuis la DB
                'cat_id'   => $product['categorie_id'],
                'stock'    => $product['stock'],
            ];
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => '« ' . $product['nom'] . ' » ajouté au panier !'
        ];
        break;

    /* ── Update quantity ── */
    case 'update':
        $qty = (int)($_POST['quantite'] ?? 1);

        if ($qty <= 0) {
            unset($_SESSION['cart'][$produitId]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Produit retiré du panier.'];
        } elseif (isset($_SESSION['cart'][$produitId])) {
            $maxStock = $_SESSION['cart'][$produitId]['stock'];
            $_SESSION['cart'][$produitId]['quantite'] = min($qty, $maxStock);
            $_SESSION['flash'] = ['type' => 'success', 'text' => 'Quantité mise à jour.'];
        }
        $redirect = 'panier.php';
        break;

    /* ── Remove from cart ── */
    case 'remove':
        if (isset($_SESSION['cart'][$produitId])) {
            $name = $_SESSION['cart'][$produitId]['nom'];
            unset($_SESSION['cart'][$produitId]);
            $_SESSION['flash'] = ['type' => 'success', 'text' => '« ' . $name . ' » retiré du panier.'];
        }
        $redirect = 'panier.php';
        break;

    /* ── Clear cart ── */
    case 'clear':
        $_SESSION['cart'] = [];
        $_SESSION['flash'] = ['type' => 'success', 'text' => 'Panier vidé.'];
        $redirect = 'panier.php';
        break;
}

if ($isAjax) {
    http_response_code(200);
    exit;
}

header("Location: $redirect");
exit;