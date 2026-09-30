<?php
require_once 'includes/db.php';
$page_title = 'About';
include 'includes/header.php';
?>

<section class="bg-gradient-to-br from-emerald-50 to-emerald-100 border-b border-stone-200 py-16 px-6 text-center">
    <h1 class="font-serif text-4xl font-bold text-emerald-700 mb-3">About Plant-o-logy</h1>
    <p class="text-stone-500 text-lg">Plants that thrive, service that cares.</p>
</section>

<div class="max-w-6xl mx-auto px-6">

    <section class="my-16">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="font-serif text-3xl font-bold mb-4">Our Story</h2>
            <p class="text-stone-500 text-lg leading-relaxed">
                Plant-o-logy started with a simple idea — that bringing a little green into your home
                shouldn't be complicated. We hand-pick every plant, ship them with care, and give you
                the guidance you need to keep them thriving.
            </p>
        </div>
    </section>

    <section class="my-16">
        <div class="grid grid-cols-3 gap-6">

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-7 text-center">
                <div class="text-4xl mb-3">🌱</div>
                <h3 class="font-serif font-bold text-lg mb-2">Hand-picked</h3>
                <p class="text-stone-500 text-sm">Every plant is inspected before it leaves our greenhouse. Only the healthy ones make it to your door.</p>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-7 text-center">
                <div class="text-4xl mb-3">📦</div>
                <h3 class="font-serif font-bold text-lg mb-2">Safe Shipping</h3>
                <p class="text-stone-500 text-sm">Specially designed packaging protects roots and leaves in transit. Free shipping on orders over $50.</p>
            </div>

            <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-7 text-center">
                <div class="text-4xl mb-3">💚</div>
                <h3 class="font-serif font-bold text-lg mb-2">Real Support</h3>
                <p class="text-stone-500 text-sm">Not sure how often to water? Send us a message. Real humans reply, not bots.</p>
            </div>

        </div>
    </section>

    <section class="my-16">
        <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-10">
            <h2 class="font-serif text-2xl font-bold text-center mb-6">Get in touch</h2>

            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-1.5">Email</div>
                    <p>hello@plantology.com</p>
                </div>
                <div class="text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-1.5">Phone</div>
                    <p>+1 (555) 012-3456</p>
                </div>
                <div class="text-center">
                    <div class="text-xs font-semibold uppercase tracking-wide text-stone-500 mb-1.5">Address</div>
                    <p>699 George Street, NSW 2000</p>
                </div>
            </div>

            <div class="text-center mt-8">
                <p class="text-stone-500 mb-4">Ready to bring some green home?</p>
                <a href="products.php" class="inline-block px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Shop Plants</a>
            </div>
        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>