<header>
    <div class="position-relative d-inline-block" id="zone_cloche" style="margin-right: 20px;">
        <button class="btn btn-sm btn-light position-relative" onclick="toggleNotifs()">
            <i class="fas fa-bell"></i>
            <span id="badge_cloche"
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                style="font-size:10px;">0</span>
        </button>

        <!-- Dropdown notifications -->
        <div id="dropdown_notifs"
            class="d-none position-absolute bg-white shadow-lg rounded-3"
            style="right:0;top:42px;width:320px;z-index:1050;border:1px solid #e2e8f0;">
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <span class="fw-semibold small">Notifications</span>
                <button class="btn btn-xs btn-link text-muted p-0"
                        onclick="marquerToutLu()" style="font-size:11px;">Tout lire</button>
            </div>
            <div id="liste_notifs" style="max-height:320px;overflow-y:auto;">
                <div class="text-center text-muted py-4 small">Chargement...</div>
            </div>
            <div class="border-top text-center py-2">
                <a href="<?= base_url('notifications') ?>" class="small text-primary">Voir tout</a>
            </div>
        </div>
    </div>
    <div class="topbar-lang"> <a href=""><i class="bi bi-globe"></i> Français ▾</a></div>
    <div class="avatar">AD</div>
</header>


<script>
const icones = {
    depot_confirme:  '📦',
    commande_prete:  '✅',
    rappel_retrait:  '⏰',
    retrait_confirme:'🎉',
    campagne:        '📢',
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
            if (data.items.length === 0) {
                liste.innerHTML = '<div class="text-center text-muted py-4 small">Aucune notification</div>';
                return;
            }

            liste.innerHTML = data.items.map(n => `
                <div class="px-3 py-2 border-bottom notif-item"
                     style="cursor:pointer;background:#fffbeb;"
                     onclick="lireNotif(${n.id_notification}, this)">
                    <div class="d-flex gap-2 align-items-start">
                        <span style="font-size:16px;">${icones[n.type] || '🔔'}</span>
                        <div>
                            <div class="fw-semibold" style="font-size:12px;">${n.sujet || n.type}</div>
                            <div class="text-muted" style="font-size:11px;">${n.nomclient}</div>
                            <div class="text-muted" style="font-size:10px;">${n.created_at}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        });
}

function lireNotif(id, el) {
    fetch(`<?= base_url('notifications/lire/') ?>${id}`, { method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
    });
    el.style.background = '#fff';
    chargerNotifs();
}

function marquerToutLu() {
    fetch('<?= base_url('notifications/lire-tout') ?>', { method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest',
                   'Content-Type': 'application/x-www-form-urlencoded' },
        body: '<?= csrf_token() ?>=<?= csrf_hash() ?>'
    }).then(() => chargerNotifs());
}

// Fermer si clic en dehors
document.addEventListener('click', e => {
    if (!document.getElementById('zone_cloche').contains(e.target)) {
        document.getElementById('dropdown_notifs').classList.add('d-none');
    }
});

// Polling toutes les 60 secondes
chargerNotifs();
setInterval(chargerNotifs, 60000);
</script>