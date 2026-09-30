<?php

// Escape output to prevent XSS
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Format a price like $12.50
function price($value) {
    return '$' . number_format((float)$value, 2);
}

// Redirect and stop
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Convert a name into a URL-friendly slug
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: 'item';
}

// Is anyone logged in?
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Is the current user an admin?
function is_admin() {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

// Current user's display name
function current_user_name() {
    return $_SESSION['user_name'] ?? '';
}

// Total number of items in the cart
function cart_count() {
    return empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);
}

// Read the one-time flash message and clear it
function get_flash() {
    $msg = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    return $msg;
}

// Return the path to a plant image, or null if not found
function plant_image($filename) {
    if (empty($filename)) return null;
    $path = 'images/' . $filename;
    return file_exists($path) ? $path : null;
}

// Fetch products in the session cart with totals
function get_cart_items($conn) {
    if (empty($_SESSION['cart'])) {
        return ['items' => [], 'subtotal' => 0];
    }

    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $items = [];
    $subtotal = 0;

    foreach ($stmt->fetchAll() as $p) {
        $qty      = (int)$_SESSION['cart'][$p['id']];
        $line     = $p['price'] * $qty;
        $subtotal += $line;

        $items[] = ['product' => $p, 'qty' => $qty, 'line' => $line];
    }

    return ['items' => $items, 'subtotal' => $subtotal];
}