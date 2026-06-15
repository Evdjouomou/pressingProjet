<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fas fa-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fas fa-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?= $this->include('pages/depots/searchclient') ?>
        <?= $this->include('pages/depots/tableaupanier') ?>
    </div>

</div>

<?= $this->include('pages/depots/modalarticle') ?>

<!-- Données PHP → JS (bridge) -->
<script>
    const BASE_URL = "<?= base_url() ?>";

    // Variable caisse — doit être avant depot.js
    const caisseOuverte = <?= $caissePourVue ? 'true' : 'false' ?>;

    // Clients
    const clients = <?= isset($clients) ? json_encode(array_map(function($c) {
        return [
            'id_client'  => $c['id_client'],
            'nomclient'  => $c['nomclient'],
            'telephone'  => $c['telephone'],
        ];
    }, $clients)) : '[]' ?>;

    // Libellés
    const allLibelles = <?= json_encode(array_map(function($l) {
        return [
            'id_libelle'  => (string) $l['id_libelle'],
            'categorie'   => $l['categorie'],
            'nom_libelle' => $l['nom_libelle'],
        ];
    }, $libelles)) ?>;
</script>

<!-- JS externalisé -->
<script src="<?= base_url('js/depot.js') ?>"></script>

<?= $this->endSection() ?>