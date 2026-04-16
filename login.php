<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim((string)($_POST['user'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($user === '' || $password === '') {
        $error = 'User dan password wajib diisi.';
    } else {
        $loginColumn = db_has_column('users', 'user') ? 'user' : 'email';
        $st = db()->prepare('SELECT * FROM users WHERE `' . $loginColumn . '` = ? LIMIT 1');
        $st->execute([$user]);
        $u = $st->fetch();
        $storedPassword = (string)($u['password'] ?? '');
        $isValidPassword = $storedPassword !== '' && (
            $password === $storedPassword ||
            password_verify($password, $storedPassword)
        );
        if ($u && $isValidPassword) {
            login_user($u);
            $red = isset($_GET['redirect']) ? (string)$_GET['redirect'] : '';
            if ($red !== '' && str_starts_with($red, '/') && !str_starts_with($red, '//')) {
                header('Location: ' . $red);
            } elseif (($u['role'] ?? '') === 'admin') {
                header('Location: ' . url('admin.php'));
            } else {
                header('Location: ' . url('index.php'));
            }
            exit;
        }
        $error = 'User atau password salah.';
    }
}

$pageTitle = 'Masuk';
$metaDescription = 'Masuk ke akun ' . SITE_NAME . '.';
include __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <div class="auth-card" data-aos="zoom-in">
            <h1 class="mt-0">Masuk</h1>
            <p class="muted">Demo: <code>erinsusnita</code> / <code>erin07</code></p>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="">
                <div class="form-group">
                    <label for="user">User</label>
                    <input type="text" id="user" name="user" required autocomplete="username" value="<?= e($_POST['user'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn" style="width:100%">Masuk</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
