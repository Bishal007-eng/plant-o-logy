<?php
require_once 'includes/db.php';

if (!is_logged_in()) {
    $_SESSION['flash'] = 'Please log in to continue to checkout.';
    redirect('auth.php?redirect=checkout.php');
}

if (empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$data       = get_cart_items($conn);
$cart_items = $data['items'];
$subtotal   = $data['subtotal'];

$shipping = ($subtotal > 0 && $subtotal < 50) ? 8.00 : 0;
$total    = $subtotal + $shipping;

if (isset($_GET['order_id'])) {
    $oid = (int)$_GET['order_id'];

    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$oid, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        $_SESSION['flash'] = 'Order not found.';
        redirect('index.php');
    }

    $items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$oid]);
    $order_items = $items->fetchAll();

    $page_title = 'Order Confirmed';
    include 'includes/header.php';
    ?>

    <div class="max-w-6xl mx-auto px-6">
        <div class="max-w-xl mx-auto my-10 text-center">

            <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-4xl mb-5">✓</div>

            <h1 class="font-serif text-3xl font-bold mb-3">Order Confirmed</h1>

            <p class="text-stone-500">
                Thank you, <?php echo e($_SESSION['user_name']); ?>.<br>
                Your order <strong class="text-stone-900">#<?php echo (int)$order['id']; ?></strong> has been placed.
            </p>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-6 text-left my-7">
                <h3 class="font-serif font-bold text-base mb-4">Order Summary</h3>
                <?php foreach ($order_items as $item): ?>
                    <div class="flex justify-between py-2 text-sm text-stone-500">
                        <span>
                            <?php echo e($item['product_name']); ?>
                            <span class="text-stone-400">× <?php echo (int)$item['quantity']; ?></span>
                        </span>
                        <span><?php echo price($item['price'] * $item['quantity']); ?></span>
                    </div>
                <?php endforeach; ?>
                <div class="flex justify-between border-t border-stone-200 mt-3 pt-4 text-lg font-bold">
                    <span>Total paid</span>
                    <span class="text-emerald-700"><?php echo price($order['total']); ?></span>
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-6 text-left">
                <h3 class="font-serif font-bold text-base mb-3">Shipping to</h3>
                <p>
                    <strong><?php echo e($order['shipping_name']); ?></strong><br>
                    <?php echo e($order['shipping_address']); ?><br>
                    <?php echo e($order['shipping_email']); ?>
                </p>
            </div>

            <div class="mt-7">
                <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Continue Shopping</a>
                <a href="index.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Back to Home</a>
            </div>

        </div>
    </div>

    <?php
    include 'includes/footer.php';
    exit;
}

$errors = [];
$old = [
    'shipping_name'    => $_SESSION['user_name']  ?? '',
    'shipping_email'   => $_SESSION['user_email'] ?? '',
    'shipping_address' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) $old[$k] = trim($_POST[$k] ?? '');

    if ($old['shipping_name'] === '') $errors['shipping_name'] = 'Name is required.';
    if ($old['shipping_email'] === '') $errors['shipping_email'] = 'Email is required.';
    elseif (!filter_var($old['shipping_email'], FILTER_VALIDATE_EMAIL)) $errors['shipping_email'] = 'Please enter a valid email address.';
    if (strlen($old['shipping_address']) < 10) $errors['shipping_address'] = 'Please enter a complete shipping address.';

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            $conn->prepare("
                INSERT INTO orders (user_id, total, status, shipping_name, shipping_email, shipping_address)
                VALUES (?, ?, 'pending', ?, ?, ?)
            ")->execute([$_SESSION['user_id'], $total, $old['shipping_name'], $old['shipping_email'], $old['shipping_address']]);
            $order_id = (int)$conn->lastInsertId();

            $insert_item = $conn->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
                VALUES (?, ?, ?, ?, ?)
            ");
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($cart_items as $item) {
                $p = $item['product'];
                $insert_item->execute([$order_id, $p['id'], $p['name'], $item['qty'], $p['price']]);
                $update_stock->execute([$item['qty'], $p['id']]);
            }

            $conn->commit();
            $_SESSION['cart'] = [];
            redirect('checkout.php?order_id=' . $order_id);

        } catch (Exception $ex) {
            $conn->rollBack();
            $errors['general'] = 'Could not place order. Please try again.';
        }
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-6">

    <div class="flex items-baseline justify-between mb-7">
        <h2 class="font-serif text-3xl font-bold">Checkout</h2>
        <a href="cart.php" class="text-emerald-700 text-sm font-semibold hover:underline">← Back to cart</a>
    </div>

    <?php if (!empty($errors['general'])): ?>
        <div class="px-5 py-3 rounded-lg mb-5 text-sm bg-red-50 border-l-4 border-red-600 text-red-900"><?php echo e($errors['general']); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-[1fr_320px] gap-8 items-start">

        <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-7">
            <h3 class="font-serif font-bold text-xl mb-5">Shipping Information</h3>

            <form method="post" action="checkout.php">

                <div class="flex flex-col gap-1.5 mb-4">
                    <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Full name</label>
                    <input type="text" name="shipping_name" value="<?php echo e($old['shipping_name']); ?>" maxlength="100"
                           class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                    <?php if (isset($errors['shipping_name'])): ?>
                        <span class="text-red-600 text-xs"><?php echo e($errors['shipping_name']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-1.5 mb-4">
                    <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Email</label>
                    <input type="email" name="shipping_email" value="<?php echo e($old['shipping_email']); ?>"
                           class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                    <?php if (isset($errors['shipping_email'])): ?>
                        <span class="text-red-600 text-xs"><?php echo e($errors['shipping_email']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col gap-1.5 mb-4">
                    <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Shipping address</label>
                    <textarea name="shipping_address" placeholder="Street, city, postal code, country" rows="4"
                              class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans resize-y min-h-[100px] focus:outline-none focus:border-emerald-700 transition-colors"><?php echo e($old['shipping_address']); ?></textarea>
                    <?php if (isset($errors['shipping_address'])): ?>
                        <span class="text-red-600 text-xs"><?php echo e($errors['shipping_address']); ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="block w-full px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Place Order</button>

            </form>
        </div>

        <aside class="bg-white border border-stone-200 rounded-lg shadow-sm p-6">
            <h3 class="font-serif font-bold text-lg mb-4">Your Order</h3>

            <?php foreach ($cart_items as $item): ?>
                <div class="flex justify-between py-2 text-sm text-stone-500">
                    <span>
                        <?php echo e($item['product']['name']); ?>
                        <span class="text-stone-400">× <?php echo (int)$item['qty']; ?></span>
                    </span>
                    <span><?php echo price($item['line']); ?></span>
                </div>
            <?php endforeach; ?>

            <div class="flex justify-between py-2 text-sm text-stone-500 border-t border-stone-200 mt-2 pt-4">
                <span>Subtotal</span>
                <span class="text-stone-900 font-semibold"><?php echo price($subtotal); ?></span>
            </div>

            <div class="flex justify-between py-2 text-sm text-stone-500">
                <span>Shipping</span>
                <span class="text-stone-900 font-semibold"><?php echo $shipping > 0 ? price($shipping) : 'Free'; ?></span>
            </div>

            <div class="flex justify-between border-t border-stone-200 mt-3 pt-4 text-lg font-bold">
                <span>Total</span>
                <span class="text-emerald-700"><?php echo price($total); ?></span>
            </div>
        </aside>

    </div>

</div>

<?php include 'includes/footer.php'; ?>