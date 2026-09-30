<?php
require_once 'includes/db.php';
$page_title = 'Home';

$categories = $conn->query("SELECT id, name, slug FROM categories ORDER BY name")->fetchAll();

$featured = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 8
")->fetchAll();

include 'includes/header.php';
?>

<section class="bg-gradient-to-br from-emerald-50 to-emerald-100 border-b border-stone-200 py-20 px-6 text-center">
    <h1 class="font-serif text-5xl font-bold leading-tight text-emerald-700 mb-4">Bring Nature Indoors</h1>
    <p class="text-stone-500 text-lg max-w-xl mx-auto mb-7">Beautiful, easy-care plants delivered to your door. Transform any space into a green retreat.</p>
    <div class="flex justify-center gap-3">
        <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Shop All Plants</a>
        <a href="about.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Learn More</a>
    </div>
</section>

<div class="max-w-6xl mx-auto px-6">

    <!-- Categories -->
    <section class="my-16">
        <div class="flex items-baseline justify-between mb-7">
            <h2 class="font-serif text-3xl font-bold">Shop by Category</h2>
            <a href="products.php" class="text-emerald-700 text-sm font-semibold hover:underline">View all →</a>
        </div>

        <div class="grid grid-cols-4 gap-5">
            <?php foreach ($categories as $c): ?>
                <a href="products.php?category=<?php echo urlencode($c['slug']); ?>"
                class="bg-white border border-stone-200 rounded-lg shadow-sm py-7 px-5 text-center font-semibold transition-all hover:border-emerald-700 hover:-translate-y-0.5 hover:shadow-md">
                    <?php echo e($c['name']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured -->
    <?php if ($featured): ?>
    <section class="my-16">
        <div class="flex items-baseline justify-between mb-7">
            <h2 class="font-serif text-3xl font-bold">Featured Plants</h2>
            <a href="products.php" class="text-emerald-700 text-sm font-semibold hover:underline">View all →</a>
        </div>

        <div class="grid grid-cols-4 gap-5">
            <?php foreach ($featured as $p): ?>
                <?php $img = plant_image($p['image']); ?>
                <a href="product.php?slug=<?php echo urlencode($p['slug']); ?>"
                class="ProductCard flex flex-col overflow-hidden bg-white border border-stone-200 rounded-lg shadow-sm transition-all hover:border-emerald-700 hover:-translate-y-0.5 hover:shadow-md">

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

                    <div class="flex flex-col flex-1 p-4">
                        <div class="text-base font-semibold mb-1 hover:text-emerald-700 transition-colors"><?php echo e($p['name']); ?></div>
                        <div class="text-stone-500 text-xs mb-3"><?php echo e($p['category_name'] ?? 'Plant'); ?></div>
                        <div class="text-emerald-700 text-lg font-bold mt-auto"><?php echo price($p['price']); ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>