<?php
require_once 'includes/db.php';

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    session_start();
    $_SESSION['flash'] = 'You have been logged out.';
    redirect('index.php');
}

$redirect_to = $_GET['redirect'] ?? 'index.php';
if (strpos($redirect_to, '://') !== false || strpos($redirect_to, '..') !== false) {
    $redirect_to = 'index.php';
}

if (is_logged_in() && !isset($_GET['redirect'])) {
    redirect('index.php');
}

$errors     = [];
$active_tab = 'login';
$old = ['login_email' => '', 'reg_name' => '', 'reg_email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $active_tab         = 'login';
        $old['login_email'] = trim($_POST['email'] ?? '');
        $password           = $_POST['password'] ?? '';

        if ($old['login_email'] === '') $errors['login_email'] = 'Email is required.';
        if ($password === '')           $errors['login_password'] = 'Password is required.';

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
            $stmt->execute([$old['login_email']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password_hash'])) {
                $errors['login_general'] = 'Invalid email or password.';
            } else {
                $_SESSION['user_id']    = (int)$user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['flash']      = 'Welcome back, ' . $user['name'] . '!';
                redirect($redirect_to);
            }
        }
    }

    if ($action === 'register') {
        $active_tab       = 'register';
        $old['reg_name']  = trim($_POST['name'] ?? '');
        $old['reg_email'] = trim($_POST['email'] ?? '');
        $password         = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($old['reg_name'] === '')            $errors['reg_name'] = 'Name is required.';
        elseif (strlen($old['reg_name']) < 2)   $errors['reg_name'] = 'Name is too short.';
        elseif (strlen($old['reg_name']) > 100) $errors['reg_name'] = 'Name is too long.';

        if ($old['reg_email'] === '')           $errors['reg_email'] = 'Email is required.';
        elseif (!filter_var($old['reg_email'], FILTER_VALIDATE_EMAIL)) $errors['reg_email'] = 'Please enter a valid email address.';
        else {
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->execute([$old['reg_email']]);
            if ($check->fetch()) $errors['reg_email'] = 'That email is already registered.';
        }

        if ($password === '')                   $errors['reg_password'] = 'Password is required.';
        elseif (strlen($password) < 6)          $errors['reg_password'] = 'Password must be at least 6 characters.';

        if ($password !== $password_confirm)    $errors['reg_password_confirm'] = 'Passwords do not match.';

        if (empty($errors)) {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'customer')")
                     ->execute([$old['reg_name'], $old['reg_email'], $hash]);

                $_SESSION['user_id']    = (int)$conn->lastInsertId();
                $_SESSION['user_name']  = $old['reg_name'];
                $_SESSION['user_email'] = $old['reg_email'];
                $_SESSION['user_role']  = 'customer';
                $_SESSION['flash']      = 'Welcome to Plant-o-logy, ' . $old['reg_name'] . '!';
                redirect($redirect_to);
            } catch (Exception $ex) {
                $errors['reg_general'] = 'Could not create your account. Please try again.';
            }
        }
    }
}

$flash = get_flash();
$page_title = 'Login / Register';
include 'includes/header.php';
?>

<div class="max-w-6xl mx-auto px-6">

    <?php if ($flash): ?>
        <div class="max-w-md mx-auto mt-5 px-5 py-3 rounded-lg text-sm bg-emerald-50 border-l-4 border-emerald-700 text-emerald-900">
            <?php echo e($flash); ?>
        </div>
    <?php endif; ?>

    <div class="max-w-md mx-auto my-10">
        <div class="bg-white border border-stone-200 rounded-lg shadow-sm p-8">

            <div class="flex gap-1 bg-stone-100 rounded-lg p-1 mb-6">
                <button type="button" class="flex-1 py-2.5 rounded-md text-sm font-semibold transition-colors <?php echo $active_tab === 'login' ? 'bg-white text-emerald-700 shadow-sm' : 'text-stone-500 hover:text-stone-900'; ?>" data-tab="login">Login</button>
                <button type="button" class="flex-1 py-2.5 rounded-md text-sm font-semibold transition-colors <?php echo $active_tab === 'register' ? 'bg-white text-emerald-700 shadow-sm' : 'text-stone-500 hover:text-stone-900'; ?>" data-tab="register">Register</button>
            </div>

            <!-- LOGIN -->
            <div class="<?php echo $active_tab === 'login' ? '' : 'hidden'; ?>" id="panel-login">
                <h2 class="font-serif font-bold text-2xl mb-1">Welcome back</h2>
                <p class="text-stone-500 text-sm mb-5">Sign in to add plants to your cart and place orders.</p>

                <?php if (!empty($errors['login_general'])): ?>
                    <div class="px-4 py-3 rounded-lg mb-4 text-sm bg-red-50 border-l-4 border-red-600 text-red-900"><?php echo e($errors['login_general']); ?></div>
                <?php endif; ?>

                <form method="post" action="auth.php<?php echo $redirect_to !== 'index.php' ? '?redirect=' . urlencode($redirect_to) : ''; ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Email</label>
                        <input type="email" name="email" value="<?php echo e($old['login_email']); ?>" autocomplete="email"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['login_email'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['login_email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Password</label>
                        <input type="password" name="password" autocomplete="current-password"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['login_password'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['login_password']); ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="block w-full px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Sign in</button>
                </form>

                <p class="text-stone-500 text-sm text-center mt-4">
                    Don't have an account?
                    <a href="#" data-switch="register" class="text-emerald-700 font-semibold hover:underline">Create one</a>
                </p>
            </div>

            <!-- REGISTER -->
            <div class="<?php echo $active_tab === 'register' ? '' : 'hidden'; ?>" id="panel-register">
                <h2 class="font-serif font-bold text-2xl mb-1">Create an account</h2>
                <p class="text-stone-500 text-sm mb-5">Register to start shopping and track your orders.</p>

                <?php if (!empty($errors['reg_general'])): ?>
                    <div class="px-4 py-3 rounded-lg mb-4 text-sm bg-red-50 border-l-4 border-red-600 text-red-900"><?php echo e($errors['reg_general']); ?></div>
                <?php endif; ?>

                <form method="post" action="auth.php<?php echo $redirect_to !== 'index.php' ? '?redirect=' . urlencode($redirect_to) : ''; ?>">
                    <input type="hidden" name="action" value="register">

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Full name</label>
                        <input type="text" name="name" value="<?php echo e($old['reg_name']); ?>" maxlength="100"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['reg_name'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['reg_name']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Email</label>
                        <input type="email" name="email" value="<?php echo e($old['reg_email']); ?>"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['reg_email'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['reg_email']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Password</label>
                        <input type="password" name="password" autocomplete="new-password"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['reg_password'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['reg_password']); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-4">
                        <label class="text-xs font-semibold uppercase tracking-wide text-stone-500">Confirm password</label>
                        <input type="password" name="password_confirm" autocomplete="new-password"
                               class="w-full px-3.5 py-2.5 bg-white border border-stone-200 rounded-lg text-sm font-sans focus:outline-none focus:border-emerald-700 transition-colors">
                        <?php if (isset($errors['reg_password_confirm'])): ?>
                            <span class="text-red-600 text-xs"><?php echo e($errors['reg_password_confirm']); ?></span>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="block w-full px-5 py-2.5 rounded-lg font-semibold text-sm bg-emerald-700 text-white hover:bg-emerald-800 transition-all">Create account</button>
                </form>

                <p class="text-stone-500 text-sm text-center mt-4">
                    Already have an account?
                    <a href="#" data-switch="login" class="text-emerald-700 font-semibold hover:underline">Sign in</a>
                </p>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs   = document.querySelectorAll('[data-tab]');
    const panels = {
        login:    document.getElementById('panel-login'),
        register: document.getElementById('panel-register'),
    };

    function activate(name) {
        Object.keys(panels).forEach(function (k) {
            panels[k].classList.toggle('hidden', k !== name);
        });

        document.querySelectorAll('[data-tab]').forEach(function (t) {
            const isActive = t.dataset.tab === name;
            t.classList.toggle('bg-white', isActive);
            t.classList.toggle('text-emerald-700', isActive);
            t.classList.toggle('shadow-sm', isActive);
            t.classList.toggle('text-stone-500', !isActive);
        });
    }

    tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.tab)));
    document.querySelectorAll('[data-switch]').forEach(l => l.addEventListener('click', function (e) {
        e.preventDefault();
        activate(this.dataset.switch);
    }));
});
</script>

<?php include 'includes/footer.php'; ?>