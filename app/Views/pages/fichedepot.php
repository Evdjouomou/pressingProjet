<?= $this->extend('layout/layoutpage') ?>
<?= $this->section('content') ?>

<div class="depot-container">
    <div class="top-row">
        <div class="card client-info">
            <div class="search-bar">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher un client...">
            </div>
            <div class="client-profile">
                <img src="<?= base_url('img/avatar-marie.jpg') ?>" alt="Marie Dubois" class="avatar-lg">
                <div class="details">
                    <h3>Marie Dubois <span class="badge-regular">Cliente régulière</span></h3>
                    <p><i class="bi bi-telephone"></i> +237 6 99 88 77 66</p>
                    <p><i class="bi bi-envelope"></i> marie.dubois@email.com</p>
                    <p class="loyalty"><i class="bi bi-star"></i> Solde fidélité: <span>1 300 pts</span></p>
                </div>
            </div>
        </div>

        <div class="card articles-list">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Articles ajoutés (3)</h3>
                <button class="btn-icon"><i class="bi bi-upload"></i></button>
            </div>
            <div class="items-container" style="background: beige; border-radius: 15px; padding: 10px; margin-top: 10px; display: flex; flex-direction: column; gap: 10px; overflow-y: auto; ">
                <div class="item">
                    <img src="<?= base_url('img/icons/costume.png') ?>" alt="Costume">
                    <div class="item-details">
                        <h4>Costume Homme (Bleu marine)</h4>
                        <span class="badge-service">Nettoyage à sec</span>
                    </div>
                    <div class="item-price">1 x 6 500 FCFA</div>
                    <button class="btn-delete"><i class="bi bi-trash"></i></button>
                </div>
                <div class="item">
                    <img src="<?= base_url('img/icons/chemise.png') ?>" alt="Chemise">
                    <div class="item-details">
                        <h4>Chemise Blanche (Coton)</h4>
                        <span class="badge-service">Lavage + Repassage</span>
                    </div>
                    <div class="item-price">3 x 4 500 FCFA</div>
                    <button class="btn-delete"><i class="bi bi-trash"></i></button>
                </div>
                <div class="item">
                    <img src="<?= base_url('img/icons/chemise.png') ?>" alt="Chemise">
                    <div class="item-details">
                        <h4>Chemise Blanche (Coton)</h4>
                        <span class="badge-service">Lavage + Repassage</span>
                    </div>
                    <div class="item-price">3 x 4 500 FCFA</div>
                    <button class="btn-delete"><i class="bi bi-trash"></i></button>
                </div>
                <div class="item">
                    <img src="<?= base_url('img/icons/chemise.png') ?>" alt="Chemise">
                    <div class="item-details">
                        <h4>Chemise Blanche (Coton)</h4>
                        <span class="badge-service">Lavage + Repassage</span>
                    </div>
                    <div class="item-price">3 x 4 500 FCFA</div>
                    <button class="btn-delete"><i class="bi bi-trash"></i></button>
                </div>
                <div class="item">
                    <img src="<?= base_url('img/icons/chemise.png') ?>" alt="Chemise">
                    <div class="item-details">
                        <h4>Chemise Blanche (Coton)</h4>
                        <span class="badge-service">Lavage + Repassage</span>
                    </div>
                    <div class="item-price">3 x 4 500 FCFA</div>
                    <button class="btn-delete"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div class="bottom-row" style="position: fixed; bottom: 0; right: 0; background: white; padding: 15px; box-shadow: 0 -2px 8px rgba(0,0,0,0.1); width: 81%">
        <div class="card input-group">
            <label>Date de livraison</label>
            <div class="flex-inputs">
                <input type="date" value="2025-11-20">
                <div class="time-input">
                    <i class="bi bi-clock"></i>
                    <select style="border: none; margin-left: 10px;"><option>17:00</option></select>
                </div>
            </div>
        </div>

        <div class="card input-group">
            <label>Acompte</label>
            <div class="acompte-input">
                <i class="bi bi-wallet2"></i>
                <input type="text" value="5 000 FCFA" style="border: none; margin-left: 10px;">
            </div>
        </div>

        <div class="card total-card">
            <div class="total-info">
                <p>Total Estimé</p>
                <h2 class="total-price">18 000 FCFA</h2>
            </div>
            <button class="btn-next">Suivant <i class="bi bi-arrow-right"></i></button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>