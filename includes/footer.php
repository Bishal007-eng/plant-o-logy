</main>

<footer class="bg-stone-900 text-stone-300 py-12 mt-16">
    <div class="max-w-6xl mx-auto px-6">

        <div class="grid grid-cols-3 gap-10 mb-8">
            <div>
                <h4 class="text-white text-sm font-semibold mb-3">Plant-o-logy</h4>
                <p class="text-stone-400 text-sm mb-1.5">Hand-picked plants delivered to your door. Growing greener homes since 2024.</p>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold mb-3">Shop</h4>
                <a href="products.php" class="block text-stone-400 text-sm mb-1.5 hover:text-white transition-colors">All Plants</a>
                <a href="products.php?category=indoor" class="block text-stone-400 text-sm mb-1.5 hover:text-white transition-colors">Indoor</a>
                <a href="products.php?category=succulents" class="block text-stone-400 text-sm mb-1.5 hover:text-white transition-colors">Succulents</a>
                <a href="products.php?category=flowering" class="block text-stone-400 text-sm mb-1.5 hover:text-white transition-colors">Flowering</a>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold mb-3">Contact</h4>
                <p class="text-stone-400 text-sm mb-1.5">hello@plantology.com</p>
                <p class="text-stone-400 text-sm mb-1.5">+1 (555) 012-3456</p>
                <p class="text-stone-400 text-sm mb-1.5">699 George Street, NSW 2000</p>
            </div>
        </div>

        <div class="border-t border-stone-800 pt-5 text-center text-stone-500 text-sm">
            &copy; <?php echo date('Y'); ?> Plant-o-logy. All rights reserved.
        </div>

    </div>
</footer>

<?php
$current_url = basename($_SERVER['PHP_SELF']);
if (!empty($_SERVER['QUERY_STRING'])) {
    $current_url .= '?' . $_SERVER['QUERY_STRING'];
}
?>

<div class="fixed inset-0 z-[200] bg-black/40 backdrop-blur-sm animate-fadeIn" id="loginModal" hidden>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md mx-4 bg-white rounded-xl p-9 text-center shadow-2xl animate-popIn">

        <button type="button" class="absolute top-3 right-3 w-8 h-8 rounded-full text-stone-500 text-xl leading-none hover:bg-stone-100 hover:text-stone-900 transition-colors" data-modal-close aria-label="Close">&times;</button>

        <div class="text-5xl mb-3">🌿</div>
        <h3 class="font-serif font-bold text-2xl mb-2">Log in to continue</h3>
        <p class="text-stone-500 text-sm">You need to be logged in to add plants to your cart.</p>

        <div class="flex gap-2.5 justify-center mt-6">
            <a href="auth.php?redirect=<?php echo urlencode($current_url); ?>" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Log in</a>
            <button type="button" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-transparent text-stone-900 border border-stone-200 hover:border-emerald-700 hover:text-emerald-700 transition-all" data-modal-close>Not now</button>
        </div>

    </div>
</div>

<script src="assets/main.js"></script>
</body>
</html>