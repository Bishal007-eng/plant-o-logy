<?php
require_once 'includes/db.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') redirect('products.php');

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ?
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    $page_title = 'Plant Not Found';
    include 'includes/header.php';
    ?>
    <div class="max-w-6xl mx-auto px-6">
        <div class="text-center py-16 px-5 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">
            <h3 class="text-stone-900 font-semibold text-lg mb-2">Plant not found</h3>
            <p>This plant doesn't exist or was removed.</p>
            <p class="mt-4">
                <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Browse all plants</a>
            </p>
        </div>
    </div>
    <?php
    include 'includes/footer.php';
    exit;
}

$page_title = $product['name'];

$related = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.category_id = ? AND p.id <> ?
    ORDER BY RAND()
    LIMIT 4
");
$related->execute([$product['category_id'], $product['id']]);
$related = $related->fetchAll();

$img = plant_image($product['image']);
$out = $product['stock'] <= 0;

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-6">

    <p class="text-stone-500 text-sm mb-6">
        <a href="products.php" class="hover:text-emerald-700">Plants</a>
        <?php if ($product['category_name']): ?>
            / <a href="products.php?category=<?php echo urlencode(strtolower(str_replace(' ', '-', $product['category_name']))); ?>" class="hover:text-emerald-700"><?php echo e($product['category_name']); ?></a>
        <?php endif; ?>
        / <?php echo e($product['name']); ?>
    </p>

    <div class="grid grid-cols-2 gap-12 items-start">

        <div class="aspect-square bg-stone-100 rounded-lg overflow-hidden flex items-center justify-center">
            <?php if ($img): ?>
                <img src="<?php echo e($img); ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-full object-cover">
            <?php else: ?>
                <div class="text-stone-500 text-sm text-center p-5">
                    <div class="text-5xl mb-2 opacity-50">🌿</div>
                    <?php echo e($product['name']); ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="text-stone-500 text-sm"><?php echo e($product['category_name'] ?? 'Plant'); ?></div>
            <h1 class="font-serif text-4xl font-bold mt-1 mb-2"><?php echo e($product['name']); ?></h1>

            <div class="text-emerald-700 text-3xl font-bold my-5"><?php echo price($product['price']); ?></div>

            <?php if ($out): ?>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-red-100 text-red-700">Out of stock</span>
            <?php else: ?>
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700">In stock — <?php echo (int)$product['stock']; ?> available</span>
            <?php endif; ?>

            <?php if ($product['description']): ?>
                <p class="mt-5"><?php echo e($product['description']); ?></p>
            <?php endif; ?>

            <div class="flex flex-wrap gap-6 py-5 border-y border-stone-200 my-5">
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Light</span>
                    <span><?php echo e($product['light'] ?? '—'); ?></span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Water</span>
                    <span><?php echo e($product['water'] ?? '—'); ?></span>
                </div>
                <div class="flex flex-col gap-1">
                    <span class="text-xs font-semibold uppercase tracking-wide text-stone-500">Pot size</span>
                    <span><?php echo e($product['pot_size'] ?? '—'); ?></span>
                </div>
            </div>

            <?php if ($out): ?>

                <p class="text-stone-500">This plant is currently out of stock. Check back soon.</p>

            <?php else: ?>

                <form method="post" action="cart.php" class="flex items-end gap-3 mt-5" data-add-cart>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">

                    <div class="flex flex-col gap-1.5 mb-0">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Quantity</label>
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" required
                            class="w-24 px-3.5 py-2.5 text-center bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                    </div>

                    <button type="submit" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Add to Cart</button>
                </form>

            <?php endif; ?>
        </div>

    </div>

    <?php if ($related): ?>
    <section class="my-16">
        <div class="flex items-baseline justify-between mb-7">
            <h2 class="font-serif text-3xl font-bold">You might also like</h2>
            <a href="products.php?category=<?php echo urlencode(strtolower(str_replace(' ', '-', $product['category_name']))); ?>" class="text-emerald-700 text-sm font-semibold hover:underline">View category →</a>
        </div>

        <div class="grid grid-cols-4 gap-5">
            <?php foreach ($related as $r): ?>
                <?php $rimg = plant_image($r['image']); ?>
                <a href="product.php?slug=<?php echo urlencode($r['slug']); ?>"
                class="ProductCard flex flex-col overflow-hidden bg-white border border-stone-200 rounded-lg shadow-sm transition-all hover:border-emerald-700 hover:-translate-y-0.5 hover:shadow-md">
                    <div class="aspect-square bg-stone-100 flex items-center justify-center overflow-hidden">
                        <?php if ($rimg): ?>
                            <img src="<?php echo e($rimg); ?>" alt="<?php echo e($r['name']); ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="text-stone-500 text-sm text-center p-5"><div class="text-4xl mb-2 opacity-50">🌿</div><?php echo e($r['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col flex-1 p-4">
                        <div class="text-base font-semibold mb-1"><?php echo e($r['name']); ?></div>
                        <div class="text-stone-500 text-xs mb-3"><?php echo e($r['category_name'] ?? 'Plant'); ?></div>
                        <div class="text-emerald-700 text-lg font-bold mt-auto"><?php echo price($r['price']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>