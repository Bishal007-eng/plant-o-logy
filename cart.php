<?php
require_once 'includes/db.php';

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 1);

        $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
        $stmt->execute([$pid]);
        $product = $stmt->fetch();

        $success = false;
        $message = 'Sorry, that plant is not available.';

        if ($product && $qty > 0 && $product['stock'] > 0) {
            $existing = $_SESSION['cart'][$pid] ?? 0;
            $_SESSION['cart'][$pid] = min($existing + $qty, (int)$product['stock']);
            $success = true;
            $message = 'Added to cart.';
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'    => $success,
                'message'    => $message,
                'cart_count' => cart_count(),
            ]);
            exit;
        }

        $_SESSION['flash'] = $message;
        redirect('cart.php');
    }

    if ($action === 'update') {
        $pid = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);
        if ($pid > 0 && isset($_SESSION['cart'][$pid])) {
            if ($qty <= 0) unset($_SESSION['cart'][$pid]);
            else $_SESSION['cart'][$pid] = $qty;
        }
        redirect('cart.php');
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][(int)($_POST['product_id'] ?? 0)]);
        redirect('cart.php');
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        redirect('cart.php');
    }
}

$flash = get_flash();

$data       = get_cart_items($conn);
$cart_items = $data['items'];
$subtotal   = $data['subtotal'];

$shipping = ($subtotal > 0 && $subtotal < 50) ? 8.00 : 0;
$total    = $subtotal + $shipping;

$page_title = 'Cart';
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-6">

    <div class="flex items-baseline justify-between mb-7">
        <h2 class="font-serif text-3xl font-bold">Your Cart</h2>
        <span class="text-stone-500 text-sm"><?php echo cart_count(); ?> item<?php echo cart_count() === 1 ? '' : 's'; ?></span>
    </div>

    <?php if ($flash): ?>
        <div class="px-5 py-3 rounded-lg mb-5 text-sm border-l-4 <?php echo str_contains($flash, 'Sorry') ? 'bg-red-50 border-red-600 text-red-900' : 'bg-emerald-50 border-emerald-700 text-emerald-900'; ?>">
            <?php echo e($flash); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>

        <div class="text-center py-16 px-5 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">
            <h3 class="text-stone-900 font-semibold text-lg mb-2">Your cart is empty</h3>
            <p>Browse our plants and find something you love.</p>
            <p class="mt-4">
                <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Shop Plants</a>
            </p>
        </div>

    <?php else: ?>

        <div class="grid grid-cols-[1fr_320px] gap-8 items-start">

            <div>
                <table class="w-full bg-white border border-stone-200 rounded-lg overflow-hidden border-collapse">
                    <thead class="bg-stone-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Plant</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200 w-40">Quantity</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200 w-24">Total</th>
                            <th class="text-left px-4 py-3 border-b border-stone-200 w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): $p = $item['product']; ?>
                            <tr>
                                <td class="px-4 py-3.5 border-b border-stone-200 align-middle">
                                    <div class="flex items-center gap-3">
                                        <div class="w-16 h-16 bg-stone-100 rounded-md overflow-hidden flex-shrink-0">
                                            <?php $img = plant_image($p['image']); ?>
                                            <?php if ($img): ?>
                                                <img src="<?php echo e($img); ?>" alt="" class="w-full h-full object-cover">
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="product.php?slug=<?php echo urlencode($p['slug']); ?>" class="font-semibold hover:text-emerald-700 transition-colors">
                                                <?php echo e($p['name']); ?>
                                            </a>
                                            <div class="text-stone-500 text-xs mt-0.5">
                                                <?php echo price($p['price']); ?> each
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 border-b border-stone-200 align-middle">
                                    <form method="post" action="cart.php" class="flex items-center gap-3">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo (int)$item['qty']; ?>" min="0" max="<?php echo (int)$p['stock']; ?>"
                                               class="w-16 text-center px-2 py-1.5 bg-white border border-stone-200 rounded-md text-sm font-sans">
                                        <button type="submit" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-3.5 border-b border-stone-200 align-middle font-bold">
                                    <?php echo price($item['line']); ?>
                                </td>
                                <td class="px-4 py-3.5 border-b border-stone-200 align-middle">
                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                        <button type="submit" title="Remove" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">✕</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="flex items-center justify-between mt-4 gap-4">
                    <a href="products.php" class="text-stone-500 text-sm hover:text-emerald-700 transition-colors">← Continue shopping</a>
                    <form method="post" action="cart.php">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all" data-confirm="Clear your entire cart?">
                            Clear cart
                        </button>
                    </form>
                </div>
            </div>

            <aside class="bg-white border border-stone-200 rounded-lg shadow-sm p-6">
                <h3 class="font-serif font-bold text-lg mb-4">Order Summary</h3>

                <div class="flex justify-between py-2 text-sm text-stone-500">
                    <span>Subtotal</span>
                    <span class="text-stone-900 font-semibold"><?php echo price($subtotal); ?></span>
                </div>

                <div class="flex justify-between py-2 text-sm text-stone-500">
                    <span>Shipping</span>
                    <span class="text-stone-900 font-semibold"><?php echo $shipping > 0 ? price($shipping) : 'Free'; ?></span>
                </div>

                <?php if ($shipping > 0): ?>
                    <p class="text-stone-500 text-xs mt-2">Add <?php echo price(50 - $subtotal); ?> more for free shipping.</p>
                <?php else: ?>
                    <p class="text-stone-500 text-xs mt-2">You qualify for free shipping.</p>
                <?php endif; ?>

                <div class="flex justify-between border-t border-stone-200 mt-3 pt-4 text-lg font-bold text-stone-900">
                    <span>Total</span>
                    <span class="text-emerald-700"><?php echo price($total); ?></span>
                </div>

                <a href="checkout.php" class="block w-full text-center px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all mt-5">
                    Proceed to Checkout
                </a>
            </aside>

        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>