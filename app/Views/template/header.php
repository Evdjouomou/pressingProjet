<header>

    <?php
    // ── Sélecteur de shop (admin central uniquement) ──────
    $db    = \Config\Database::connect();
    $shops = $db->table('shops')
        ->orderBy('nom_shop')
        ->get()->getResultArray();
    $shopVue = session()->get('shop_id_vue');
    $shopNomVue = '';
    if ($shopVue) {
        $s = $db->table('shops')->where('id_shop', $shopVue)->get()->getRowArray();
        $shopNomVue = $s['nom_shop'] ?? '';
    }
    ?>

    <?php if (function_exists('est_admin_central') && est_admin_central()): ?>
    <div class="position-relative d-inline-block me-3"
         id="zone_shop_switcher">

        <button class="btn btn-sm rounded-2 d-flex align-items-center gap-2"
                style="background:rgba(255,255,255,.15);color:#fff;
                       border:1px solid rgba(255,255,255,.3);"
                onclick="toggleShopMenu()">
            <i class="fas fa-store fa-sm"></i>
            <span style="font-size:12px;">
                <?= $shopVue ? esc($shopNomVue) : 'Tous les shops' ?>
            </span>
            <i class="fas fa-chevron-down fa-xs"></i>
        </button>

        <div id="dropdown_shop"
             class="d-none position-absolute bg-white shadow-lg rounded-3"
             style="right:0;top:42px;min-width:230px;z-index:1060;
                    border:1px solid #e2e8f0;">

            <!-- Vue globale -->
            <a href="<?= base_url('shop/reset-vue') ?>"
               class="d-flex align-items-center gap-2 px-3 py-2
                      text-decoration-none
                      <?= !$shopVue ? 'fw-bold text-primary' : 'text-dark' ?>"
               style="font-size:13px;border-bottom:1px solid #f1f5f9;">
                <i class="fas fa-globe text-primary"></i>
                Tous les établissements
                <?php if (!$shopVue): ?>
                <i class="fas fa-check ms-auto text-primary fa-xs"></i>
                <?php endif; ?>
            </a>

            <!-- Shops -->
            <?php foreach ($shops as $sh): ?>
            <a href="<?= base_url('shop/switcher/' . $sh['id_shop']) ?>"
               class="d-flex align-items-center gap-2 px-3 py-2
                      text-decoration-none
                      <?= $shopVue == $sh['id_shop']
                            ? 'fw-bold text-primary bg-light' : 'text-dark' ?>"
               style="font-size:13px;border-bottom:1px solid #f9fafb;">
                <i class="fas fa-store fa-xs"
                   style="color:<?= $shopVue==$sh['id_shop'] ? '#1d4ed8' : '#9ca3af' ?>;"></i>
                <?= esc($sh['nom_shop']) ?>
                <?php if ($shopVue == $sh['id_shop']): ?>
                <i class="fas fa-check ms-auto text-primary fa-xs"></i>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Cloche notifications ─────────────────────────── -->
    <div class="position-relative d-inline-block"
         id="zone_cloche" style="margin-right:20px;">
        <button class="btn btn-sm btn-light position-relative"
                onclick="toggleNotifs()">
            <i class="fas fa-bell"></i>
            <span id="badge_cloche"
                  class="position-absolute top-0 start-100 translate-middle
                         badge rounded-pill bg-danger d-none"
                  style="font-size:10px;">0</span>
        </button>

        <div id="dropdown_notifs"
             class="d-none position-absolute bg-white shadow-lg rounded-3"
             style="right:0;top:42px;width:320px;z-index:1050;
                    border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center
                        px-3 py-2 border-bottom">
                <span class="fw-semibold small">Notifications</span>
                <button class="btn btn-xs btn-link text-muted p-0"
                        onclick="marquerToutLu()"
                        style="font-size:11px;">Tout lire</button>
            </div>
            <div id="liste_notifs"
                 style="max-height:320px;overflow-y:auto;">
                <div class="text-center text-muted py-4 small">
                    Chargement...
                </div>
            </div>
            <div class="border-top text-center py-2">
                <a href="<?= base_url('notifications') ?>"
                   class="small text-primary">Voir tout</a>
            </div>
        </div>
    </div>

    <!-- ── Langue ─────────────────────────────────────────── -->
    <div class="topbar-lang">
        <a href=""><i class="bi bi-globe"></i> Français ▾</a>
    </div>

    <!-- ── Avatar ────────────────────────────────────────── -->
    <div class="avatar"
         style="width:40px;height:40px;background-color:#4B6BFB;
                color:white;border-radius:50%;display:inline-flex;
                align-items:center;justify-content:center;
                font-weight:bold;font-size:14px;text-transform:uppercase;"
         title="<?= htmlspecialchars(session()->get('nom_complet') ?? 'Utilisateur') ?>">
        <?php
        $nom   = session()->get('nom_complet') ?? 'Utilisateur';
        $mots  = explode(' ', trim($nom));
        $init  = '';
        if (isset($mots[0])) $init .= mb_substr($mots[0], 0, 1, 'UTF-8');
        if (isset($mots[1])) $init .= mb_substr($mots[1], 0, 1, 'UTF-8');
        echo mb_strtoupper($init, 'UTF-8');
        ?>
    </div>

</header>

<script>
// ── Shop switcher ──────────────────────────────────────────
function toggleShopMenu() {
    const m = document.getElementById('dropdown_shop');
    if (m) m.classList.toggle('d-none');
}

document.addEventListener('click', e => {
    // Fermer shop switcher
    const zs = document.getElementById('zone_shop_switcher');
    if (zs && !zs.contains(e.target)) {
        const m = document.getElementById('dropdown_shop');
        if (m) m.classList.add('d-none');
    }
    // Fermer notifications
    const zc = document.getElementById('zone_cloche');
    if (zc && !zc.contains(e.target)) {
        document.getElementById('dropdown_notifs').classList.add('d-none');
    }
});

// ── Notifications ──────────────────────────────────────────
const icones = {
    depot_confirme:   '📦',
    commande_prete:   '✅',
    rappel_retrait:   '⏰',
    retrait_confirme: '🎉',
    campagne:         '📢',
};

function toggleNotifs() {
    document.getElementById('dropdown_notifs').classList.toggle('d-none');
}

function chargerNotifs() {
    fetch('<?= base_url('notifications/api/non-lues') ?>')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('badge_cloche');
            if (data.total > 0) {
                badge.textContent = data.total;
                badge.classList.remove('d-none');
            } else {
                badge.classList.add('d-none');
            }

            const liste = document.getElementById('liste_notifs');
            if (!data.items || data.items.length === 0) {
                liste.innerHTML = `
                    <div class="text-center text-muted py-4 small">
                        Aucune notification
                    </div>`;
                return;
            }

            liste.innerHTML = data.items.map(n => `
                <div class="px-3 py-2 border-bottom notif-item"
                     style="cursor:pointer;background:#fffbeb;"
                     onclick="lireNotif(${n.id_notification}, this)">
                    <div class="d-flex gap-2 align-items-start">
                        <span style="font-size:16px;">
                            ${icones[n.type] || '🔔'}
                        </span>
                        <div>
                            <div class="fw-semibold" style="font-size:12px;">
                                ${n.sujet || n.type}
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                ${n.nomclient}
                            </div>
                            <div class="text-muted" style="font-size:10px;">
                                ${n.created_at}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {});
}

function lireNotif(id, el) {
    fetch(`<?= base_url('notifications/lire/') ?>${id}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>',
    });
    el.style.background = '#fff';
    chargerNotifs();
}

function marquerToutLu() {
    fetch('<?= base_url('notifications/lire-tout') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>',
    }).then(() => chargerNotifs());
}

// Polling 60 secondes
chargerNotifs();
setInterval(chargerNotifs, 60000);
</script>