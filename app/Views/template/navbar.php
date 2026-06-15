<nav>
    <div class="sidebar-logo">
        <p><img src="<?= base_url('img/logo/logo.png') ?>" alt="Logo"></p>
        <span class="logo-text">GEST PRESSING</span>
    </div>
    <hr>

    <?php
    $uri = uri_string();
    
    // 1. Récupération et nettoyage du poste de l'utilisateur
    $user_poste = strtolower(session()->get('nom_poste') ?? 'caissier'); 

    // 2. Matrice des permissions mise à jour
    $permissions = [
        'administrateur' => [
            'dashboard', 'depot', 'client', 'pos', 'production', 
            'retouches', 'incidents', 'stocks', 'finance', 'rapports', 
            'equipe', 'communication', 'prestation', 'livraison'
        ],
        'gérant' => [
            'dashboard', 'depot', 'client', 'pos', 'production', 
            'retouches', 'incidents', 'stocks', 'finance', 'rapports', 
            'equipe', 'communication', 'prestation', 'livraison'
        ],
        'gerant' => [
            'dashboard', 'depot', 'client', 'pos', 'production', 
            'retouches', 'incidents', 'stocks', 'finance', 'rapports', 
            'equipe', 'communication', 'prestation', 'livraison'
        ],
        'caissier' => [
            'depot', 'client', 'pos', 'finance', 'pointages', 'communication', 'livraison'
        ],
        'caissière' => [
            'depot', 'client', 'pos', 'finance', 'pointages', 'communication', 'livraison'
        ],
        'responsable atelier' => [
            'production', 'retouches', 'incidents', 'stocks'
        ],
        'responsable de l\'atelier' => [
            'production', 'retouches', 'incidents', 'stocks'
        ]
    ];

    // 3. Fonction navLink avec contrôle d'accès
    function navLink($url, $icon, $label, $uri, $required_permission, $user_poste, $permissions) {
        if (!isset($permissions[$user_poste]) || !in_array($required_permission, $permissions[$user_poste])) {
            return '';
        }

        $active = (strpos($uri, trim($url, '/')) === 0) ? 'active' : '';
        return '<a href="' . base_url($url) . '" class="' . $active . '">
                    <i class="' . $icon . '"></i>
                    <p>' . $label . '</p>
                </a>';
    }

    // 4. Gestion des langues
    $lang = session()->get('lang') ?? 'fr';
    $labels = [
        'fr' => [
            'dashboard'    => 'Tableau de bord',
            'depot'        => 'Dépôts',
            'clients'      => 'Clients',
            'pos'          => 'Point de vente',
            'production'   => 'Production',
            'stocks'       => 'Stocks',
            'retouches'    => 'Retouches',
            'incidents'    => 'Incidents',
            'personnel'    => 'Personnel',
            'planning'     => 'Planning',
            'pointages'    => 'Pointages',
            'notifications'=> 'Notifications',
            'rapports'     => 'Rapports',
            'campagnes'    => 'Campagnes',
            'abonnements'  => 'Abonnements',
            'livraison'    => 'Livraisons',
            'prestation'   => 'Prestations',
            'position'     => 'Postes',
            'shop'         => 'Boutiques',
            'deconnexion'  => 'Déconnexion',
        ],
        'en' => [
            'dashboard'    => 'Dashboard',
            'depot'        => 'Orders',
            'clients'      => 'Clients',
            'pos'          => 'Point of Sale',
            'production'   => 'Production',
            'stocks'       => 'Inventory',
            'retouches'    => 'Alterations',
            'incidents'    => 'Incidents',
            'personnel'    => 'Staff',
            'planning'     => 'Schedule',
            'pointages'    => 'Time Tracking',
            'notifications'=> 'Notifications',
            'rapports'     => 'Reports',
            'campagnes'    => 'Campaigns',
            'abonnements'  => 'Subscriptions',
            'livraison'    => 'Deliveries',
            'prestation'   => 'Services',
            'position'     => 'Positions',
            'shop'         => 'Shops',
            'deconnexion'  => 'Sign out',
        ],
    ];
    $l = $labels[$lang];
    
    $has_access = function($permission) use ($user_poste, $permissions) {
        return isset($permissions[$user_poste]) && in_array($permission, $permissions[$user_poste]);
    };
    ?>

    <?php if ($has_access('dashboard') || $has_access('depot') || $has_access('client') || $has_access('pos')): ?>
        <div>
            <?= navLink('dashboard',   'bi bi-speedometer2',      $l['dashboard'],    $uri, 'dashboard', $user_poste, $permissions) ?>
            <?= navLink('depot',       'bi bi-minecart-loaded',   $l['depot'],        $uri, 'depot', $user_poste, $permissions) ?>
            <?= navLink('client',      'bi bi-people',            $l['clients'],      $uri, 'client', $user_poste, $permissions) ?>
            <?= navLink('pos',         'bi bi-cash-coin',         $l['pos'],          $uri, 'pos', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access('production') || $has_access('retouches') || $has_access('incidents') || $has_access('stocks')): ?>
        <hr>
        <p class="nav-section-label"><?= $lang === 'fr' ? 'Production' : 'Production' ?></p>
        <div>
            <?= navLink('production',          'bi bi-gear-wide',         $l['production'],   $uri, 'production', $user_poste, $permissions) ?>
            <?= navLink('production/cycles',   'bi bi-arrow-repeat',      $lang === 'fr' ? 'Cycles machine' : 'Machine cycles', $uri, 'production', $user_poste, $permissions) ?>
            <?= navLink('retouches',           'bi bi-scissors',          $l['retouches'],    $uri, 'retouches', $user_poste, $permissions) ?>
            <?= navLink('incidents',           'bi bi-exclamation-triangle', $l['incidents'], $uri, 'incidents', $user_poste, $permissions) ?>
            <?= navLink('stocks',              'bi bi-box-seam',          $l['stocks'],       $uri, 'stocks', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access('finance') || $has_access('rapports')): ?>
        <hr>
        <p class="nav-section-label"><?= $lang === 'fr' ? 'Finance' : 'Finance' ?></p>
        <div>
            <?= navLink('pos/caisse',   'bi bi-cash-stack',   $lang === 'fr' ? 'Caisse' : 'Cash register', $uri, 'finance', $user_poste, $permissions) ?>
            <?= navLink('rapports',     'bi bi-bar-chart',    $l['rapports'],    $uri, 'rapports', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access('equipe') || $has_access('pointages')): ?>
        <hr>
        <p class="nav-section-label"><?= $lang === 'fr' ? 'Équipe' : 'Team' ?></p>
        <div>
            <?= navLink('personnel',          'bi bi-people-fill',   $l['personnel'],  $uri, 'equipe', $user_poste, $permissions) ?>
            <?= navLink('personnel/planning', 'bi bi-calendar',      $l['planning'],   $uri, 'equipe', $user_poste, $permissions) ?>
            <?= navLink('personnel/pointages','bi bi-clock',         $l['pointages'],  $uri, 'pointages', $user_poste, $permissions) ?>
            <?= navLink('position',           'bi bi-briefcase',     $l['position'],   $uri, 'equipe', $user_poste, $permissions) ?>
            <?= navLink('shop',               'bi bi-shop',          $l['shop'],       $uri, 'equipe', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access('communication')): ?>
        <hr>
        <p class="nav-section-label"><?= $lang === 'fr' ? 'Communication' : 'Communication' ?></p>
        <div>
            <?= navLink('notifications', 'bi bi-bell-fill',      $l['notifications'], $uri, 'communication', $user_poste, $permissions) ?>
            <?= navLink('campagnes',     'bi bi-megaphone',      $l['campagnes'],     $uri, 'communication', $user_poste, $permissions) ?>
            <?= navLink('abonnements',   'bi bi-card-checklist', $l['abonnements'],   $uri, 'communication', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <?php if ($has_access('prestation') || $has_access('livraison')): ?>
        <hr>
        <p class="nav-section-label"><?= $lang === 'fr' ? 'Paramètres' : 'Settings' ?></p>
        <div>
            <?= navLink('prestation', 'bi bi-basket2',     $l['prestation'], $uri, 'prestation', $user_poste, $permissions) ?>
            <?= navLink('livraison',  'bi bi-truck',        $l['livraison'],  $uri, 'livraison', $user_poste, $permissions) ?>
        </div>
    <?php endif; ?>

    <hr>
    <div>
        <a href="<?= base_url('logout') ?>">
            <i class="bi bi-box-arrow-right"></i>
            <p><?= $l['deconnexion'] ?></p>
        </a>
    </div>
</nav>

<style>
.nav-section-label {
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    padding: 4px 16px 2px;
    margin: 0;
    font-weight: 600;
}
</style>