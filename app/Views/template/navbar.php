<nav>
    <div class="sidebar-logo">
        <p><img src="<?= base_url('img/logo/logo.png') ?>" alt="Logo"></p>
        <span class="logo-text">GEST PRESSING</span>
    </div>
    <hr>
    <div>
        <a href="<?= base_url('dashboard') ?>" class="<?= (uri_string() == 'dashboard' || uri_string() == '') ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i>
            <p>Tableau de bord</p>
        </a>
        <a href="<?= base_url('personnel') ?>" class="<?= (uri_string() == 'personnel') ? 'active' : '' ?>">
            <i class="bi bi-people-fill"></i>
            <p>Employés</p>
        </a>
        <a href="<?= base_url('prestation') ?>" class="<?= (uri_string() == 'prestation') ? 'active' : '' ?>">
            <i class="bi bi-basket2"></i>
            <p>Prestation</p>
        </a>
        <a href="<?= base_url('depot'); ?>" class="<?= (uri_string() == 'depot') ? 'active' : '' ?>">
            <i class="bi bi-minecart-loaded"></i>
            <p>Depots</p>
        </a>
        <a href="<?= base_url('client') ?>" class="<?= (uri_string() == 'client') ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <p>Clients</p>
        </a>
        <a href="<?= base_url('livraison') ?>" class="<?= (uri_string() == 'livraison') ? 'active' : '' ?>">
            <i class="bi bi-truck"></i>
            <p>Livraison</p>
        </a>
        <a href="<?= base_url('notifications') ?>" class="<?= (uri_string() == 'notifications') ? 'active' : '' ?>">
            <i class="bi bi-bell-fill"></i>
            <p>Notification</p>
        </a>
        <a href="<?= base_url('production') ?>" class="<?= (uri_string() == 'production') ? 'active' : '' ?>">
            <i class="bi bi-telephone-fill"></i>
            <p>Atelier</p>
        </a>
        <a href="<?= base_url('abonnements') ?>" class="<?= (uri_string() == 'abonnements') ? 'active' : '' ?>">
            <i class="bi bi-card-checklist"></i>
            <p>Abonnements</p>
        </a>
        <a href="<?= base_url('personnel/planning') ?>" class="<?= (uri_string() == 'personnel/planning') ? 'active' : '' ?>">
            <i class="bi bi-calendar"></i>
            <p>Plannifications</p>
        </a>
        <a href="<?= base_url('pos') ?>" class="<?= (uri_string() == 'pos') ? 'active' : '' ?>">
            <i class="bi bi-cash-coin"></i>
            <p>P.O.S</p>
        </a>
    </div>
    <hr>
    <div>
        <a href="<?= base_url('logout') ?>">
            <i class="bi bi-box-arrow-right"></i>
            <p>Déconnexion</p>
        </a>
    </div>
</nav>