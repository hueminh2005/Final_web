<?php
$theme = $_COOKIE['theme'] ?? 'light';
?>
<script>
(function() {
    var t = document.cookie.match(/theme=([^;]+)/);
    document.body && (document.body.className = 'theme-' + (t ? t[1] : 'light'));
})();
</script>
<script>
function toggleTheme() {
    var isDark = document.body.classList.contains('theme-dark');
    document.body.classList.toggle('theme-dark', !isDark);
    document.body.classList.toggle('theme-light', isDark);
    document.getElementById('themeIcon').textContent = isDark ? '🌙' : '☀️';
    document.cookie = 'theme=' + (isDark ? 'light' : 'dark') + ';path=/;max-age=31536000';
}
</script>
<header class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <h1><a href="<?= APP_URL ?>/index.php">📝 NoteCraft</a></h1>
        </div>
        <nav class="navbar-nav">
            <a href="<?= APP_URL ?>/index.php" class="nav-link">My Notes</a>
            <a href="<?= APP_URL ?>/labels.php" class="nav-link">Labels</a>
            <a href="<?= APP_URL ?>/shared-notes.php" class="nav-link">Shared</a>
        </nav>
        <div class="navbar-actions">
            <button id="themeToggle" class="btn-icon" title="Toggle Theme" onclick="toggleTheme()">
                <span id="themeIcon"><?= $theme === 'dark' ? '☀️' : '🌙' ?></span>
            </button>
            <a href="<?= APP_URL ?>/profile.php" class="btn-icon" title="Profile">👤</a>
            <a href="<?= APP_URL ?>/logout.php" class="btn-icon btn-logout" title="Logout" id="logoutBtn">
                <span class="logout-icon">🚪</span>
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </div>
</header>