<?php
require_once 'includes/db.php';
$page_title = 'Plants';

$category_slug = trim($_GET['category'] ?? '');
$search        = trim($_GET['search'] ?? '');

$where  = [];
$params = [];

if ($category_slug !== '') {
    $where[]  = "c.slug = ?";
    $params[] = $category_slug;
}

if ($search !== '') {
    $where[]  = "p.name LIKE ?";
    $like     = "%$search%";
    $params[] = $like;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $conn->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    $where_sql
    ORDER BY p.name ASC
");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $conn->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.name
")->fetchAll();

$active_name = '';
foreach ($categories as $c) {
    if ($c['slug'] === $category_slug) { $active_name = $c['name']; break; }
}

include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-6">

    <div class="flex items-baseline justify-between mb-7">
        <h2 class="font-serif text-3xl font-bold">
            <?php
                if ($active_name) echo e($active_name);
                elseif ($search)  echo 'Search: "' . e($search) . '"';
                else              echo 'All Plants';
            ?>
        </h2>
        <span class="text-stone-500 text-sm"><?php echo count($products); ?> plant<?php echo count($products) === 1 ? '' : 's'; ?></span>
    </div>

    <!-- Filter bar -->
    <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-4 mb-7">
        <form method="get" action="products.php" class="flex items-center justify-between gap-4">

            <div class="flex items-center gap-3 flex-wrap">
                <a href="products.php"
                   class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?php echo $category_slug === '' ? 'bg-emerald-700 text-white' : 'bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700'; ?>">
                    All
                </a>
                <?php foreach ($categories as $c): ?>
                    <a href="products.php?category=<?php echo urlencode($c['slug']); ?>"
                       class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?php echo $category_slug === $c['slug'] ? 'bg-emerald-700 text-white' : 'bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700'; ?>">
                        <?php echo e($c['name']); ?> (<?php echo (int)$c['product_count']; ?>)
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center gap-2">
                <?php if ($category_slug): ?>
                    <input type="hidden" name="category" value="<?php echo e($category_slug); ?>">
                <?php endif; ?>
                <input type="text" name="search"
                       class="w-44 px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors"
                       placeholder="Search plants..." value="<?php echo e($search); ?>">
                <button type="submit" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Search</button>
            </div>

        </form>
    </div>

    <?php if (empty($products)): ?>

        <div class="text-center py-16 px-5 bg-white border border-dashed border-stone-200 rounded-lg text-stone-500">
            <h3 class="text-stone-900 font-semibold text-lg mb-2">No plants found</h3>
            <p>Try a different category or clear the search.</p>
            <p class="mt-4">
                <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Show all plants</a>
            </p>
        </div>

    <?php else: ?>

        <div class="grid grid-cols-4 gap-5">
            <?php foreach ($products as $p): ?>
                <?php $img = plant_image($p['image']); $out = $p['stock'] <= 0; ?>

                <div class="ProductCard flex flex-col overflow-hidden bg-white border border-stone-200 rounded-lg shadow-sm transition-all hover:border-emerald-700 hover:-translate-y-0.5 hover:shadow-md">

                    <a href="product.php?slug=<?php echo urlencode($p['slug']); ?>">
                        <div class="aspect-square bg-stone-100 flex items-center justify-center overflow-hidden">
                            <?php if ($img): ?>
                                <img src="<?php echo e($img); ?>" alt="<?php echo e($p['name']); ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-stone-500 text-sm text-center p-5">
                                    <div class="text-4xl mb-2 opacity-50">🌿</div>
                                    <?php echo e($p['name']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>

                    <div class="flex flex-col flex-1 p-4">
                        <a href="product.php?slug=<?php echo urlencode($p['slug']); ?>">
                            <div class="text-base font-semibold mb-1 hover:text-emerald-700 transition-colors"><?php echo e($p['name']); ?></div>
                        </a>
                        <div class="text-stone-500 text-xs mb-3"><?php echo e($p['category_name'] ?? 'Plant'); ?></div>
                        <div class="text-emerald-700 text-lg font-bold mt-auto"><?php echo price($p['price']); ?></div>

                        <div class="my-2.5">
                            <?php if ($out): ?>
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-red-100 text-red-700">Out of stock</span>
                            <?php else: ?>
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-emerald-100 text-emerald-700">In stock</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($out): ?>
                            <button type="button" disabled class="block w-full px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 opacity-50 cursor-not-allowed">Out of stock</button>
                        <?php else: ?>
                            <form method="post" action="cart.php" data-add-cart>
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="block w-full px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Add to Cart</button>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>