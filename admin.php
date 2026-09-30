<?php
require_once 'includes/db.php';

if (!is_admin()) {
    $_SESSION['flash'] = is_logged_in() ? 'Admin access only.' : 'Please log in as an administrator.';
    redirect('auth.php?redirect=admin.php');
}

$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'product_save') {
        $id          = (int)($_POST['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $slug        = trim($_POST['slug'] ?? '');
        $category_id = (int)($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $light       = trim($_POST['light'] ?? '');
        $water       = trim($_POST['water'] ?? '');
        $pot_size    = trim($_POST['pot_size'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $image       = trim($_POST['image'] ?? '');
        $featured    = isset($_POST['featured']) ? 1 : 0;

        $errors = [];
        if ($name === '')      $errors[] = 'Name is required.';
        if ($price <= 0)       $errors[] = 'Price must be greater than 0.';
        if ($category_id <= 0) $errors[] = 'Category is required.';

        if ($slug === '') $slug = slugify($name);

        $check = $conn->prepare("SELECT id FROM products WHERE slug = ? AND id <> ?");
        $check->execute([$slug, $id]);
        if ($check->fetch()) $errors[] = 'That slug is already used by another product.';

        if (empty($errors)) {
            if ($id > 0) {
                $conn->prepare("UPDATE products SET category_id=?, name=?, slug=?, description=?, light=?, water=?, pot_size=?, price=?, stock=?, image=?, featured=? WHERE id=?")
                     ->execute([$category_id, $name, $slug, $description, $light, $water, $pot_size, $price, $stock, $image, $featured, $id]);
                $_SESSION['flash'] = 'Product updated.';
            } else {
                $conn->prepare("INSERT INTO products (category_id, name, slug, description, light, water, pot_size, price, stock, image, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
                     ->execute([$category_id, $name, $slug, $description, $light, $water, $pot_size, $price, $stock, $image, $featured]);
                $_SESSION['flash'] = 'Product created.';
            }
            redirect('admin.php?section=products');
        }

        $_SESSION['admin_errors'] = $errors;
        $_SESSION['admin_old']    = $_POST;
        redirect('admin.php?section=products&action=' . ($id > 0 ? 'edit&id=' . $id : 'new'));
    }

    if ($action === 'product_delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $conn->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $_SESSION['flash'] = 'Product deleted.';
        }
        redirect('admin.php?section=products');
    }

    if ($action === 'order_status') {
        $id     = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if ($id > 0 && in_array($status, ['pending', 'shipped', 'delivered'], true)) {
            $conn->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $id]);
            $_SESSION['flash'] = 'Order status updated.';
        }
        redirect('admin.php?section=orders&action=view&id=' . $id);
    }
}

$admin_errors = $_SESSION['admin_errors'] ?? [];
$admin_old    = $_SESSION['admin_old'] ?? [];
unset($_SESSION['admin_errors'], $_SESSION['admin_old']);

$section = $_GET['section'] ?? 'dashboard';
if (!in_array($section, ['dashboard', 'products', 'orders'], true)) $section = 'dashboard';

$action  = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);

$stats = $products = $categories = $orders = $order_items = [];
$order = $product_edit = null;
$recent = [];

if ($section === 'dashboard') {
    $stats['products'] = (int)$conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['orders']   = (int)$conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats['revenue']  = (float)$conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();

    $recent = $conn->query("SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
}

if ($section === 'products') {
    $categories = $conn->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

    if ($action === 'new' || ($action === 'edit' && $edit_id > 0)) {
        if ($action === 'edit') {
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$edit_id]);
            $product_edit = $stmt->fetch();
            if (!$product_edit) { $_SESSION['flash'] = 'Product not found.'; redirect('admin.php?section=products'); }
        }
    } else {
        $products = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name")->fetchAll();
    }
}

if ($section === 'orders') {
    if ($action === 'view' && $edit_id > 0) {
        $stmt = $conn->prepare("SELECT o.*, u.name AS customer_name, u.email AS customer_email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
        $stmt->execute([$edit_id]);
        $order = $stmt->fetch();
        if (!$order) { $_SESSION['flash'] = 'Order not found.'; redirect('admin.php?section=orders'); }
        $items = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$edit_id]);
        $order_items = $items->fetchAll();
    } else {
        $orders = $conn->query("SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC")->fetchAll();
    }
}

$page_title = 'Admin';
include 'includes/header.php';

// Helper for nav tabs
function navTab($key, $current, $label) {
    $active = $key === $current;
    $cls = $active
        ? 'bg-emerald-700 text-white'
        : 'text-stone-500 hover:bg-stone-100';
    echo '<a href="admin.php?section=' . $key . '" class="px-4 py-2 rounded-md text-sm font-semibold transition-colors ' . $cls . '">' . $label . '</a>';
}
?>

<div class="max-w-6xl mx-auto px-6">

    <?php if ($flash): ?>
        <div class="px-5 py-3 rounded-lg mb-5 text-sm bg-emerald-50 border-l-4 border-emerald-700 text-emerald-900"><?php echo e($flash); ?></div>
    <?php endif; ?>

    <?php if (!empty($admin_errors)): ?>
        <div class="px-5 py-3 rounded-lg mb-5 text-sm bg-red-50 border-l-4 border-red-600 text-red-900">
            <?php foreach ($admin_errors as $err): ?>
                <div>• <?php echo e($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="flex gap-2 bg-white border border-stone-200 rounded-lg shadow-sm p-2 mb-7">
        <?php navTab('dashboard', $section, 'Dashboard'); ?>
        <?php navTab('products', $section, 'Products'); ?>
        <?php navTab('orders', $section, 'Orders'); ?>
    </div>

    <?php if ($section === 'dashboard'): ?>

        <h2 class="font-serif text-2xl font-bold mb-5">Dashboard</h2>

        <div class="grid grid-cols-3 gap-5 mb-8">
            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Products</div>
                <div class="text-3xl font-bold text-emerald-700 mt-1.5"><?php echo $stats['products']; ?></div>
            </div>
            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Orders</div>
                <div class="text-3xl font-bold text-emerald-700 mt-1.5"><?php echo $stats['orders']; ?></div>
            </div>
            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-stone-500">Revenue</div>
                <div class="text-3xl font-bold text-emerald-700 mt-1.5"><?php echo price($stats['revenue']); ?></div>
            </div>
        </div>

        <h3 class="font-serif text-lg font-bold mb-3.5 mt-7">Recent Orders</h3>

        <?php if (empty($recent)): ?>
            <div class="text-center py-16 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">No orders yet.</div>
        <?php else: ?>
            <table class="w-full bg-white border border-stone-200 rounded-lg overflow-hidden border-collapse">
                <thead class="bg-stone-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Customer</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Total</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Date</th>
                        <th class="px-4 py-3 border-b border-stone-200"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $o): ?>
                        <tr>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm">#<?php echo (int)$o['id']; ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo e($o['customer_name'] ?? '—'); ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo price($o['total']); ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700"><?php echo e($o['status']); ?></span></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm text-right">
                                <a href="admin.php?section=orders&action=view&id=<?php echo (int)$o['id']; ?>" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    <?php elseif ($section === 'products'): ?>

        <?php if ($action === 'new' || ($action === 'edit' && $product_edit)):
            $is_edit = ($action === 'edit' && $product_edit);
            $form = $is_edit ? $product_edit : [];
            if (!empty($admin_old)) $form = array_merge($form, $admin_old);
        ?>

            <div class="flex items-center justify-between mb-5">
                <h2 class="font-serif text-2xl font-bold"><?php echo $is_edit ? 'Edit Product' : 'New Product'; ?></h2>
                <a href="admin.php?section=products" class="text-stone-500 hover:text-emerald-700 transition-colors">← Back to products</a>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-7">
                <form method="post" action="admin.php?section=products">
                    <input type="hidden" name="action" value="product_save">
                    <input type="hidden" name="id" value="<?php echo $is_edit ? (int)$form['id'] : 0; ?>">

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Name</label>
                            <input type="text" name="name" value="<?php echo e($form['name'] ?? ''); ?>" required
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Slug (optional)</label>
                            <input type="text" name="slug" value="<?php echo e($form['slug'] ?? ''); ?>"
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Category</label>
                        <select name="category_id" required
                                class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)($form['category_id'] ?? 0) === (int)$c['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($c['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans resize-y focus:outline-none focus:border-emerald-700 transition-colors"><?php echo e($form['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Light</label>
                            <input type="text" name="light" value="<?php echo e($form['light'] ?? ''); ?>"
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Water</label>
                            <input type="text" name="water" value="<?php echo e($form['water'] ?? ''); ?>"
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pot size</label>
                            <input type="text" name="pot_size" value="<?php echo e($form['pot_size'] ?? ''); ?>"
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Image filename</label>
                            <input type="text" name="image" value="<?php echo e($form['image'] ?? ''); ?>" placeholder="monstera.jpeg"
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Price</label>
                            <input type="number" step="0.01" min="0" name="price" value="<?php echo e($form['price'] ?? ''); ?>" required
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                        <div class="flex flex-col gap-1.5 mb-4">
                            <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Stock</label>
                            <input type="number" min="0" name="stock" value="<?php echo e($form['stock'] ?? 0); ?>" required
                                   class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        </div>
                    </div>

                    <div class="flex flex-row items-center gap-2 mb-4">
                        <input type="checkbox" name="featured" value="1" id="featured"
                            <?php echo !empty($form['featured']) ? 'checked' : ''; ?>
                            class="w-4 h-4 accent-emerald-700">
                        <label for="featured" class="text-xs font-semibold uppercase tracking-wide text-stone-500 m-0">Show on home page (featured)</label>
                    </div>

                    <div class="flex items-center gap-3 mt-5">
                        <button type="submit" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">
                            <?php echo $is_edit ? 'Save Changes' : 'Create Product'; ?>
                        </button>
                        <a href="admin.php?section=products" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Cancel</a>
                    </div>
                </form>
            </div>

        <?php else: ?>

            <div class="flex items-center justify-between mb-5">
                <h2 class="font-serif text-2xl font-bold">Products (<?php echo count($products); ?>)</h2>
                <a href="admin.php?section=products&action=new" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition-all">+ New Product</a>
            </div>

            <?php if (empty($products)): ?>
                <div class="text-center py-16 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">No products yet.</div>
            <?php else: ?>
                <table class="w-full bg-white border border-stone-200 rounded-lg overflow-hidden border-collapse">
                    <thead class="bg-stone-100">
                        <tr>
                            <th class="px-4 py-3 border-b border-stone-200"></th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Name</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Category</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Price</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Stock</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Featured</th>
                            <th class="px-4 py-3 border-b border-stone-200"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $p): $thumb = plant_image($p['image']); ?>
                            <tr>
                                <td class="px-4 py-3 border-b border-stone-200 w-16">
                                    <?php if ($thumb): ?>
                                        <div class="w-16 h-16 bg-stone-100 rounded-md overflow-hidden">
                                            <img src="<?php echo e($thumb); ?>" alt="" class="w-full h-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="text-stone-500 text-xs">—</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo e($p['name']); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo e($p['category_name'] ?? '—'); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo price($p['price']); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo (int)$p['stock']; ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm">
                                    <?php if ($p['featured']): ?>
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700">Yes</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm text-right whitespace-nowrap">
                                    <a href="admin.php?section=products&action=edit&id=<?php echo (int)$p['id']; ?>"
                                       class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Edit</a>
                                    <form method="post" action="admin.php?section=products" style="display:inline;"
                                          data-confirm="Delete this product?">
                                        <input type="hidden" name="action" value="product_delete">
                                        <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>">
                                        <button type="submit" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-600 text-white hover:bg-red-700 transition-all">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php endif; ?>

    <?php elseif ($section === 'orders'): ?>

        <?php if ($action === 'view' && $order): ?>

            <div class="flex items-center justify-between mb-5">
                <h2 class="font-serif text-2xl font-bold">Order #<?php echo (int)$order['id']; ?></h2>
                <a href="admin.php?section=orders" class="text-stone-500 hover:text-emerald-700 transition-colors">← Back to orders</a>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2.5">Customer</div>
                    <p>
                        <strong><?php echo e($order['shipping_name']); ?></strong><br>
                        <?php echo e($order['shipping_email']); ?><br>
                        <?php echo e($order['customer_name'] ?? ''); ?>
                    </p>
                </div>
                <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-5">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2.5">Shipping address</div>
                    <p><?php echo nl2br(e($order['shipping_address'])); ?></p>
                </div>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-5 mb-6">
                <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-2.5">Update status</div>
                <form method="post" action="admin.php?section=orders" class="flex items-center gap-3">
                    <input type="hidden" name="action" value="order_status">
                    <input type="hidden" name="id" value="<?php echo (int)$order['id']; ?>">
                    <select name="status" class="w-48 px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php foreach (['pending', 'shipped', 'delivered'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Update</button>
                </form>
            </div>

            <table class="w-full bg-white border border-stone-200 rounded-lg overflow-hidden border-collapse">
                <thead class="bg-stone-100">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Product</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Price</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Qty</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo e($item['product_name']); ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo price($item['price']); ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo (int)$item['quantity']; ?></td>
                            <td class="px-4 py-3 border-b border-stone-200 text-sm font-bold"><?php echo price($item['price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-sm text-right font-bold">Total</td>
                        <td class="px-4 py-3 text-sm font-bold text-emerald-700"><?php echo price($order['total']); ?></td>
                    </tr>
                </tbody>
            </table>

        <?php else: ?>

            <h2 class="font-serif text-2xl font-bold mb-5">Orders (<?php echo count($orders); ?>)</h2>

            <?php if (empty($orders)): ?>
                <div class="text-center py-16 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">No orders yet.</div>
            <?php else: ?>
                <table class="w-full bg-white border border-stone-200 rounded-lg overflow-hidden border-collapse">
                    <thead class="bg-stone-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Customer</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Total</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wide text-stone-500 border-b border-stone-200">Date</th>
                            <th class="px-4 py-3 border-b border-stone-200"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm">#<?php echo (int)$o['id']; ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo e($o['customer_name'] ?? '—'); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo price($o['total']); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700"><?php echo e($o['status']); ?></span></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm"><?php echo date('M j, Y', strtotime($o['created_at'])); ?></td>
                                <td class="px-4 py-3 border-b border-stone-200 text-sm text-right">
                                    <a href="admin.php?section=orders&action=view&id=<?php echo (int)$o['id']; ?>" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>