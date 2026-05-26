// ─────────────────────────────────────────
// ÉTAT GLOBAL
// ─────────────────────────────────────────
let panier = [];
let idClientSelectionne = null;

// ─────────────────────────────────────────
// INIT
// ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initModal();
    initFormulaire();
});

// ─────────────────────────────────────────
// RECHERCHE CLIENT
// ─────────────────────────────────────────
function initSearch() {
    const input = document.getElementById('search_input');
    const box = document.getElementById('suggestion_box');
    if (!input || !box) return;

    input.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        box.innerHTML = '';

        if (!query) { box.classList.add('d-none'); return; }

        const matches = clients.filter(c =>
            (c.nomclient || '').toLowerCase().includes(query) ||
            (c.telephone || '').toString().includes(query)
        );

        if (matches.length > 0) {
            box.classList.remove('d-none');
            matches.forEach(c => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                btn.innerHTML = `
                    <div>
                        <i class="fas fa-user-circle me-2 text-secondary"></i>
                        <strong>${c.nomclient}</strong>
                    </div>
                    <span class="badge bg-primary rounded-pill">${c.telephone}</span>`;
                btn.onclick = () => selectClient(c);
                box.appendChild(btn);
            });
        } else {
            box.classList.remove('d-none');
            box.innerHTML = '<div class="p-3 text-muted">Aucun client trouvé</div>';
        }
    });
}

function selectClient(c) {
    idClientSelectionne = c.id_client;
    document.getElementById('form_id_client').value = c.id_client; // ← ajout
    document.getElementById('client_nom_trouve').innerText = c.nomclient;
    document.getElementById('client_tel_trouve').innerText = 'Contact : ' + c.telephone;
    document.getElementById('result_client').classList.remove('d-none');
    document.getElementById('search_input').closest('.position-relative').style.display = 'none';
    document.getElementById('suggestion_box').classList.add('d-none');
}

function resetClient() {
    idClientSelectionne = null;
    document.getElementById('form_id_client').value = ''; // ← ajout
    document.getElementById('result_client').classList.add('d-none');
    document.getElementById('search_input').closest('.position-relative').style.display = '';
    document.getElementById('search_input').value = '';
}

// ─────────────────────────────────────────
// MODAL : CATÉGORIE → LIBELLÉS → PRESTATIONS
// ─────────────────────────────────────────
function initModal() {
    document.getElementById('sel_categorie').addEventListener('change', onCategorieChange);
    document.getElementById('sel_libelle').addEventListener('change', onLibelleChange);
    document.getElementById('sel_prestation').addEventListener('change', calculerPrixFinal);
    document.getElementById('options_express').addEventListener('change', calculerPrixFinal);
    document.getElementById('btn_confirmer_article').addEventListener('click', confirmerArticle);
}

function onCategorieChange() {
    const catChoisie = this.value;
    const selLibelle = document.getElementById('sel_libelle');
    document.getElementById('zone_prestation').classList.add('d-none');
    resetPrixAffichage();
    selLibelle.innerHTML = '<option value="">-- Choisir Article --</option>';

    if (!catChoisie) { selLibelle.disabled = true; return; }

    allLibelles.filter(l => l.categorie === catChoisie).forEach(l => {
        const opt = document.createElement('option');
        opt.value = l.id_libelle;
        opt.textContent = l.nom_libelle;
        selLibelle.appendChild(opt);
    });
    selLibelle.disabled = false;
}

function onLibelleChange() {
    const idLibelle = this.value;
    const zonePresta = document.getElementById('zone_prestation');
    const selPresta = document.getElementById('sel_prestation');
    zonePresta.classList.add('d-none');
    resetPrixAffichage();

    if (!idLibelle) return;

    fetch(`${BASE_URL}depot/getPrestationsByArticle/${idLibelle}`)
        .then(res => res.json())
        .then(data => {
            selPresta.innerHTML = '<option value="" data-prix="0" data-points="0" data-majoration="0">-- Choisir Prestation --</option>';
            data.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id_service;
                opt.dataset.prix = s.prix_unitaire_base;
                opt.dataset.points = s.points_fidelite;
                opt.dataset.majoration = s.majoration_express;
                opt.textContent = s.type_prestation;
                selPresta.appendChild(opt);
            });
            zonePresta.classList.remove('d-none');
        });
}

function calculerPrixFinal() {
    const selPresta = document.getElementById('sel_prestation');
    const option = selPresta.options[selPresta.selectedIndex];
    if (!option || option.value === '') { resetPrixAffichage(); return; }

    const prixBase = parseFloat(option.dataset.prix) || 0;
    const points = parseInt(option.dataset.points) || 0;
    const tauxMajoration = parseFloat(option.dataset.majoration) || 0;
    const isExpress = document.getElementById('options_express').checked;

    const prixFinal = isExpress ? prixBase + (prixBase * tauxMajoration / 100) : prixBase;

    document.getElementById('prix_affiche').innerText = prixFinal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('input_prix').value = prixFinal;
    document.getElementById('points_valeur').innerText = points;
    document.getElementById('input_points').value = points;
}

function resetPrixAffichage() {
    document.getElementById('prix_affiche').innerText = '0 FCFA';
    document.getElementById('points_valeur').innerText = '0';
    document.getElementById('input_prix').value = '';
    document.getElementById('input_points').value = '';
}

// ─────────────────────────────────────────
// PANIER & AFFICHAGE
// ─────────────────────────────────────────
function confirmerArticle() {
    const selLibelle = document.getElementById('sel_libelle');
    const selPresta = document.getElementById('sel_prestation');
    const prix = document.getElementById('input_prix').value;

    if (!selLibelle.value || !selPresta.value || !prix) {
        alert('Veuillez remplir tous les champs obligatoires.');
        return;
    }

    const article = {
        id_libelle: selLibelle.value,
        nom_art: selLibelle.options[selLibelle.selectedIndex].text,
        id_service: selPresta.value,
        presta_nom: selPresta.options[selPresta.selectedIndex].text,
        prix: parseFloat(prix),
        points: parseInt(document.getElementById('input_points').value) || 0,
        couleur: document.getElementById('art_couleur').value,
        marque: document.getElementById('art_marque').value,
        matiere: document.getElementById('art_matiere').value,
        observations: document.getElementById('art_obs').value,
        express: document.getElementById('options_express').checked ? 1 : 0,
    };

    panier.push(article);
    renderTableau();
    resetModal();
    bootstrap.Modal.getInstance(document.getElementById('modalArticle')).hide();
}

function supprimerArticle(index) {
    panier.splice(index, 1);
    renderTableau();
}

function renderTableau() {
    const tbody = document.getElementById('tbody_panier');
    tbody.innerHTML = '';

    if (panier.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Panier vide</td></tr>';
        document.getElementById('total_facture').innerText = '0 FCFA';
        return;
    }

    let totalPrix = 0, totalPoints = 0;

    panier.forEach((art, index) => {
        totalPrix += art.prix;
        totalPoints += art.points;
        tbody.innerHTML += `
            <tr>
                <td class="ps-3"><strong>${art.nom_art}</strong> ${art.express ? '🚀' : ''}</td>
                <td>${art.couleur} / ${art.marque}</td>
                <td>${art.presta_nom}</td>
                <td class="text-end fw-bold">${art.prix.toLocaleString('fr-FR')} FCFA</td>
                <td class="text-end">${art.points} pts</td>
                <td class="text-center"><button class="btn btn-sm btn-outline-danger" onclick="supprimerArticle(${index})"><i class="fas fa-trash"></i></button></td>
            </tr>`;
    });

    document.getElementById('total_facture').innerText = totalPrix.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('total_points').innerHTML = `<i class="fas fa-star"></i> ${totalPoints} pts`;
    
    genererInputsCaches(totalPoints);
    calculerReste();
    mettreAJourCompteur();
}

function genererInputsCaches(totalPoints) {
    const container = document.getElementById('hidden_articles_inputs');
    container.innerHTML = '';
    panier.forEach((art, i) => {
        const fields = {
            'articles_libelle_id':  art.id_libelle,
            'articles_prix':        art.prix,
            'articles_presta_id':   art.id_service,
            'articles_express':     art.express,
            'articles_couleur':     art.couleur,
            'articles_marque':      art.marque,
            'articles_matiere':     art.matiere,
            'articles_obs':         art.observations,
        };
        for (let key in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `${key}[]`;
            input.value = fields[key] ?? '';
            container.appendChild(input);
        }
    });
    document.getElementById('form_total_points').value = totalPoints;
}

// ─────────────────────────────────────────
// SOUMISSION & CALCULS FINAUX
// ─────────────────────────────────────────
function initFormulaire() {
    document.getElementById('formFinal').addEventListener('submit', function (e) {
        if (!idClientSelectionne || panier.length === 0) {
            e.preventDefault();
            alert('Client ou Panier vide !');
            return;
        }

        const acompte = parseFloat(document.getElementById('champ_acompte').value) || 0;
        const caisseOuverte = <?= $caissePourVue ? 'true' : 'false' ?>;

        if (acompte > 0 && !caisseOuverte) {
            e.preventDefault();
            alert('Impossible d\'encaisser un acompte : aucune caisse n\'est ouverte.\n\nOuvrez la caisse dans le module POS ou enregistrez le dépôt sans acompte.');
            return;
        }

        document.getElementById('form_id_client').value     = idClientSelectionne;
        document.getElementById('form_acompte').value       = acompte;
        document.getElementById('form_mode_paiement').value = document.getElementById('champ_mode_paiement').value;
        document.getElementById('form_date_retrait').value  = document.getElementById('champ_date_retrait').value;
        document.getElementById('form_numero_bon').value    = document.getElementById('champ_numero_bon').value;
    });
}

function calculerReste() {
    const total = panier.reduce((sum, art) => sum + art.prix, 0);
    const acompte = parseFloat(document.getElementById('champ_acompte').value) || 0;
    const reste = total - acompte;
    document.getElementById('montant_reste').innerText = (reste > 0 ? reste : 0).toLocaleString('fr-FR') + ' FCFA';
}

function mettreAJourCompteur() {
    document.getElementById('label_nb_articles').innerText = panier.length + " article(s)";
}

function resetModal() {
    document.getElementById('sel_categorie').value = '';
    document.getElementById('sel_libelle').innerHTML = '<option value="">-- Choisir Article --</option>';
    document.getElementById('art_couleur').value = '';
    document.getElementById('art_marque').value = '';
    document.getElementById('art_matiere').value = '';
    document.getElementById('art_obs').value = '';
    document.getElementById('options_express').checked = false;
    resetPrixAffichage();
}