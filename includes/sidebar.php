<?php
$sidebarExtra ??= '';
?>
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="assets/images/Logo.png" alt="Banas Hardware">
    </div>
    <ul class="sidebar-nav">
        <?php foreach ($navItems as $item): ?>
            <li class="<?= $item['href'] === $activeHref ? 'active' : '' ?>">
                <a href="<?= e($item['href']) ?>"><?= icon($item['icon']) ?><span><?= e($item['label']) ?></span></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="sidebar-footer">
        <?= $sidebarExtra ?>
        <form method="post" action="logout.php">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-block"><?= icon('logout') ?> Logout</button>
        </form>
    </div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
