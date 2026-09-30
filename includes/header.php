<?php require_once __DIR__ . '/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? e($page_title) . ' — Plant-o-logy' : 'Plant-o-logy — Beautiful plants for every space'; ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    serif: ['Georgia', 'Times New Roman', 'serif']
                }
            }
        }
    }
    </script>

    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="font-sans bg-stone-50 text-stone-900 leading-relaxed min-h-screen flex flex-col"
    data-logged-in="<?php echo is_logged_in() ? '1' : '0'; ?>">

<header class="bg-white border-b border-stone-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-6 flex items-center justify-between gap-8 py-4">

        <a href="index.php" class="font-serif text-2xl font-bold text-emerald-700 tracking-tight">
            Plant-o-logy
        </a>

        <nav class="flex justify-center flex-1 gap-7">
            <a href="index.php" class="text-stone-500 hover:text-emerald-700 text-sm font-medium transition-colors">Home</a>
            <a href="products.php" class="text-stone-500 hover:text-emerald-700 text-sm font-medium transition-colors">Plants</a>
            <a href="about.php" class="text-stone-500 hover:text-emerald-700 text-sm font-medium transition-colors">About</a>
            <?php if (is_admin()): ?>
                <a href="admin.php" class="text-stone-500 hover:text-emerald-700 text-sm font-medium transition-colors">Admin</a>
            <?php endif; ?>
        </nav>

        <div class="flex items-center gap-2.5">
            <?php if (is_logged_in()): ?>
                <span class="text-stone-500 text-sm">Hi, <?php echo e(current_user_name()); ?></span>
                <a href="auth.php?logout=1" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Logout</a>
            <?php else: ?>
                <a href="auth.php" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all">Login</a>
            <?php endif; ?>
            <a href="cart.php" class="inline-block px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-700 text-white hover:bg-emerald-800 transition-all">
                Cart (<span class="CartCount"><?php echo cart_count(); ?></span>)
            </a>
        </div>

    </div>
</header>

<main class="flex-1 py-10"></main>