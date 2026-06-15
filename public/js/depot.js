// ─────────────────────────────────────────────────────────────
// ÉTAT GLOBAL
// ─────────────────────────────────────────────────────────────
let panier = [];
let idClientSelectionne = null;

// ─────────────────────────────────────────────────────────────
// INIT AU CHARGEMENT
// ─────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    initSearch();
    initModal();
    initFormulaire();
});

// ─────────────────────────────────────────────────────────────
// RECHERCHE CLIENT
// ─────────────────────────────────────────────────────────────
function initSearch() {
    const input = document.getElementById('search_input');
    const box   = document.getElementById('suggestion_box');

    if (!input || !box) {
        console.warn('[depot.js] Champ search_input ou suggestion_box introuvable.');
        return;
    }

    // Vérifier que la variable clients est disponible
    if (typeof clients === 'undefined') {
        console.error('[depot.js] Variable "clients" non définie. Vérifiez le bridge PHP→JS.');
        return;
    }

    input.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        box.innerHTML = '';

        if (!query) {
            box.classList.add('d-none');
            return;
        }

        const matches = clients.filter(c =>
            (c.nomclient  || '').toLowerCase().includes(query) ||
            (c.telephone  || '').toString().includes(query)
        );

        box.classList.remove('d-none');

        if (matches.length > 0) {
            matches.forEach(c => {
                const btn       = document.createElement('button');
                btn.type        = 'button';
                btn.className   = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                btn.innerHTML   = `
                    <div>
                        <i class="fas fa-user-circle me-2 text-secondary"></i>
                        <strong>${c.nomclient}</strong>
                    </div>
                    <span class="badge bg-primary rounded-pill">${c.telephone}</span>`;
                btn.addEventListener('click', () => selectClient(c));
                box.appendChild(btn);
            });
        } else {
            box.innerHTML = '<div class="p-3 text-muted small">Aucun client trouvé</div>';
        }
    });

    // Fermer la liste si clic ailleurs
    document.addEventListener('click', function (e) {
        if (!input.contains(e.target) && !box.contains(e.target)) {
            box.classList.add('d-none');
        }
    });
}

function selectClient(c) {
    idClientSelectionne = c.id_client;

    // Remplir les champs visibles
    const nomEl = document.getElementById('client_nom_trouve');
    const telEl = document.getElementById('client_tel_trouve');
    const avEl  = document.getElementById('client_avatar');

    if (nomEl) nomEl.innerText = c.nomclient;
    if (telEl) telEl.innerText = 'Contact : ' + c.telephone;
    if (avEl)  avEl.innerText  = c.nomclient.substring(0, 2).toUpperCase();

    // Afficher le bloc client sélectionné
    const result = document.getElementById('result_client');
    if (result) result.classList.remove('d-none');

    // Masquer la zone de recherche
    const zone = document.getElementById('search_input');
    if (zone && zone.closest('.position-relative')) {
        zone.closest('.position-relative').style.display = 'none';
    }

    // Masquer la liste de suggestions
    const box = document.getElementById('suggestion_box');
    if (box) box.classList.add('d-none');
}

function resetClient() {
    idClientSelectionne = null;

    // Vider champs
    const nomEl = document.getElementById('client_nom_trouve');
    const telEl = document.getElementById('client_tel_trouve');
    const avEl  = document.getElementById('client_avatar');
    const input = document.getElementById('search_input');

    if (nomEl) nomEl.innerText = '';
    if (telEl) telEl.innerText = '';
    if (avEl)  avEl.innerText  = '--';
    if (input) input.value     = '';

    // Masquer le bloc client sélectionné
    const result = document.getElementById('result_client');
    if (result) result.classList.add('d-none');

    // Réafficher la zone de recherche
    const zone = document.getElementById('search_input');
    if (zone && zone.closest('.position-relative')) {
        zone.closest('.position-relative').style.display = '';
    }
}

// ─────────────────────────────────────────────────────────────
// MODAL ARTICLE : CATÉGORIE → LIBELLÉS → PRESTATIONS
// ─────────────────────────────────────────────────────────────
function initModal() {
    const selCat    = document.getElementById('sel_categorie');
    const selLib    = document.getElementById('sel_libelle');
    const selPresta = document.getElementById('sel_prestation');
    const express   = document.getElementById('options_express');
    const btnConf   = document.getElementById('btn_confirmer_article');

    // Vérification que tous les éléments existent
    if (!selCat || !selLib || !selPresta || !express || !btnConf) {
        console.warn('[depot.js] Un ou plusieurs éléments du modal sont introuvables.');
        return;
    }

    // Vérifier que la variable allLibelles est disponible
    if (typeof allLibelles === 'undefined') {
        console.error('[depot.js] Variable "allLibelles" non définie. Vérifiez le bridge PHP→JS.');
        return;
    }

    selCat.addEventListener('change',    onCategorieChange);
    selLib.addEventListener('change',    onLibelleChange);
    selPresta.addEventListener('change', calculerPrixFinal);
    express.addEventListener('change',   calculerPrixFinal);
    btnConf.addEventListener('click',    confirmerArticle);
}

function onCategorieChange() {
    const catChoisie = document.getElementById('sel_categorie').value.trim();
    const selLibelle = document.getElementById('sel_libelle');

    // Reset prestation et prix
    document.getElementById('zone_prestation').classList.add('d-none');
    resetPrixAffichage();

    // Reset libellé
    selLibelle.innerHTML = '<option value="">-- Choisir Article --</option>';

    if (!catChoisie) {
        selLibelle.disabled = true;
        return;
    }

    // Filtrer les libellés de cette catégorie
    const libFiltrés = allLibelles.filter(l =>
        l.categorie &&
        l.categorie.trim().toLowerCase() === catChoisie.trim().toLowerCase()
    );

    if (libFiltrés.length === 0) {
        selLibelle.innerHTML = '<option value="">Aucun article dans cette catégorie</option>';
        selLibelle.disabled  = true;
        return;
    }

    libFiltrés.forEach(l => {
        const opt       = document.createElement('option');
        opt.value       = l.id_libelle;
        opt.textContent = l.nom_libelle;
        selLibelle.appendChild(opt);
    });

    selLibelle.disabled = false;
}

function onLibelleChange() {
    const idLibelle  = document.getElementById('sel_libelle').value;
    const zonePresta = document.getElementById('zone_prestation');
    const selPresta  = document.getElementById('sel_prestation');

    zonePresta.classList.add('d-none');
    resetPrixAffichage();

    if (!idLibelle) return;

    // Appel API pour récupérer les prestations
    fetch(`${BASE_URL}/depot/getPrestationsByArticle/${idLibelle}`)
        .then(res => {
            if (!res.ok) throw new Error('Réponse réseau non OK : ' + res.status);
            return res.json();
        })
        .then(data => {
            selPresta.innerHTML = '<option value="" data-prix="0" data-points="0" data-majoration="0">-- Choisir Prestation --</option>';

            if (data.length === 0) {
                selPresta.innerHTML += '<option disabled>Aucune prestation disponible</option>';
                zonePresta.classList.remove('d-none');
                return;
            }

            data.forEach(s => {
                const opt             = document.createElement('option');
                opt.value             = s.id_service;
                opt.dataset.prix      = s.prix_unitaire_base;
                opt.dataset.points    = s.points_fidelite;
                opt.dataset.majoration= s.majoration_express;
                opt.textContent       = s.type_prestation;
                selPresta.appendChild(opt);
            });

            zonePresta.classList.remove('d-none');
        })
        .catch(err => {
            console.error('[depot.js] Erreur fetch prestations :', err);
            alert('Impossible de charger les prestations. Vérifiez la connexion.');
        });
}

function calculerPrixFinal() {
    const selPresta = document.getElementById('sel_prestation');
    const option    = selPresta.options[selPresta.selectedIndex];

    if (!option || !option.value) {
        resetPrixAffichage();
        return;
    }

    const prixBase      = parseFloat(option.dataset.prix)       || 0;
    const points        = parseInt(option.dataset.points)        || 0;
    const majoration    = parseFloat(option.dataset.majoration)  || 0;
    const isExpress     = document.getElementById('options_express').checked;

    const prixFinal = isExpress
        ? prixBase + (prixBase * majoration / 100)
        : prixBase;

    document.getElementById('prix_affiche').innerText   = prixFinal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('input_prix').value         = prixFinal;
    document.getElementById('points_valeur').innerText  = points;
    document.getElementById('input_points').value       = points;
}

function resetPrixAffichage() {
    const prixAff = document.getElementById('prix_affiche');
    const ptsVal  = document.getElementById('points_valeur');
    const inPrix  = document.getElementById('input_prix');
    const inPts   = document.getElementById('input_points');

    if (prixAff) prixAff.innerText  = '0 FCFA';
    if (ptsVal)  ptsVal.innerText   = '0';
    if (inPrix)  inPrix.value       = '';
    if (inPts)   inPts.value        = '';
}

// ─────────────────────────────────────────────────────────────
// PANIER
// ─────────────────────────────────────────────────────────────
function confirmerArticle() {
    const selLibelle = document.getElementById('sel_libelle');
    const selPresta  = document.getElementById('sel_prestation');
    const prix       = document.getElementById('input_prix').value;

    // Validations
    if (!selLibelle.value) {
        alert('Veuillez choisir un article.');
        return;
    }
    if (!selPresta.value) {
        alert('Veuillez choisir une prestation.');
        return;
    }
    if (!prix || parseFloat(prix) <= 0) {
        alert('Le prix est invalide.');
        return;
    }

    const article = {
        id_libelle   : selLibelle.value,
        nom_art      : selLibelle.options[selLibelle.selectedIndex].text,
        id_service   : selPresta.value,
        presta_nom   : selPresta.options[selPresta.selectedIndex].text,
        prix         : parseFloat(prix),
        points       : parseInt(document.getElementById('input_points').value) || 0,
        couleur      : document.getElementById('art_couleur').value,
        marque       : document.getElementById('art_marque').value,
        matiere      : document.getElementById('art_matiere').value,
        observations : document.getElementById('art_obs').value,
        express      : document.getElementById('options_express').checked ? 1 : 0,
    };

    panier.push(article);
    renderTableau();
    resetModal();

    // Fermer le modal Bootstrap
    const modalEl = document.getElementById('modalArticle');
    if (modalEl) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

function supprimerArticle(index) {
    panier.splice(index, 1);
    renderTableau();
}

function renderTableau() {
    const tbody = document.getElementById('tbody_panier');
    if (!tbody) return;

    tbody.innerHTML = '';

    const rowVide = document.getElementById('row_vide');

    if (panier.length === 0) {
        if (rowVide) rowVide.style.display = '';
        document.getElementById('total_facture').innerText = '0 FCFA';
        document.getElementById('total_points').innerHTML  =
            '<i class="fas fa-star fa-xs"></i> 0 pts';
        mettreAJourCompteur();
        return;
    }

    if (rowVide) rowVide.style.display = 'none';

    let totalPrix   = 0;
    let totalPoints = 0;

    panier.forEach((art, index) => {
        totalPrix   += art.prix;
        totalPoints += art.points;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="ps-3">
                <strong>${art.nom_art}</strong>
                ${art.express ? '<span class="badge bg-danger ms-1" style="font-size:10px;">🚀 Express</span>' : ''}
                <div class="text-muted" style="font-size:11px;">${art.couleur || ''} ${art.marque ? '· ' + art.marque : ''}</div>
            </td>
            <td class="text-muted" style="font-size:12px;">
                ${art.couleur || '—'} / ${art.marque || '—'}
            </td>
            <td>${art.presta_nom}</td>
            <td class="text-end fw-bold text-success">
                ${art.prix.toLocaleString('fr-FR')} FCFA
            </td>
            <td class="text-end text-warning">
                <i class="fas fa-star fa-xs"></i> ${art.points} pts
            </td>
            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger rounded-2"
                        onclick="supprimerArticle(${index})">
                    <i class="fas fa-trash fa-sm"></i>
                </button>
            </td>`;
        tbody.appendChild(tr);
    });

    document.getElementById('total_facture').innerText =
        totalPrix.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('total_points').innerHTML  =
        `<i class="fas fa-star fa-xs"></i> ${totalPoints} pts`;

    genererInputsCaches(totalPoints);
    calculerReste();
    mettreAJourCompteur();
}

function genererInputsCaches(totalPoints) {
    const container = document.getElementById('hidden_articles_inputs');
    if (!container) return;

    container.innerHTML = '';

    panier.forEach((art) => {
        const champs = {
            'articles_libelle_id' : art.id_libelle,
            'articles_prix'       : art.prix,
            'articles_presta_id'  : art.id_service,
            'articles_express'    : art.express,
            'articles_couleur'    : art.couleur,
            'articles_marque'     : art.marque,
            'articles_matiere'    : art.matiere,
            'articles_obs'        : art.observations,
        };

        Object.entries(champs).forEach(([key, val]) => {
            const input   = document.createElement('input');
            input.type    = 'hidden';
            input.name    = `${key}[]`;
            input.value   = val ?? '';
            container.appendChild(input);
        });
    });

    const inputPoints = document.getElementById('form_total_points');
    if (inputPoints) inputPoints.value = totalPoints;
}

// ─────────────────────────────────────────────────────────────
// FORMULAIRE FINAL (soumission)
// ─────────────────────────────────────────────────────────────
function initFormulaire() {
    const form = document.getElementById('formFinal');
    if (!form) return;

    form.addEventListener('submit', function (e) {

        // Validation client
        if (!idClientSelectionne) {
            e.preventDefault();
            alert('Veuillez sélectionner un client avant de continuer.');
            return;
        }

        // Validation panier
        if (panier.length === 0) {
            e.preventDefault();
            alert('Le panier est vide. Ajoutez au moins un article.');
            return;
        }

        const acompte      = parseFloat(document.getElementById('champ_acompte')?.value) || 0;
        
        // APRÈS — lit correctement la variable globale du bridge
        const caisse = (typeof caisseOuverte !== 'undefined') ? caisseOuverte : false;

        if (acompte > 0 && !caisse) {
            e.preventDefault();
            alert(
                'Impossible d\'encaisser un acompte : aucune caisse n\'est ouverte.\n\n' +
                'Ouvrez la caisse dans le module POS ou enregistrez le dépôt sans acompte.'
            );
            return;
        }

        // Validation caisse
        if (acompte > 0 && !caisseOuverte) {
            e.preventDefault();
            alert(
                'Impossible d\'encaisser un acompte : aucune caisse n\'est ouverte.\n\n' +
                'Ouvrez la caisse dans le module POS ou enregistrez le dépôt sans acompte.'
            );
            return;
        }

        // Remplir les champs cachés
        const idClientInput = document.getElementById('form_id_client');
        const acompteInput  = document.getElementById('form_acompte');
        const modeInput     = document.getElementById('form_mode_paiement');
        const dateInput     = document.getElementById('form_date_retrait');
        const bonInput      = document.getElementById('form_numero_bon');

        if (idClientInput) idClientInput.value = idClientSelectionne;
        if (acompteInput)  acompteInput.value  = acompte;
        if (modeInput) {
            const modeEl = document.getElementById('champ_mode_paiement');
            modeInput.value = modeEl ? modeEl.value : 'especes';
        }
        if (dateInput) {
            const dateEl = document.getElementById('champ_date_retrait');
            dateInput.value = dateEl ? dateEl.value : '';
        }
        if (bonInput) {
            const bonEl = document.getElementById('champ_numero_bon');
            bonInput.value = bonEl ? bonEl.value : '';
        }
    });
}

// ─────────────────────────────────────────────────────────────
// CALCULS
// ─────────────────────────────────────────────────────────────
function calculerReste() {
    const total   = panier.reduce((sum, art) => sum + art.prix, 0);
    const champ   = document.getElementById('champ_acompte');
    const acompte = champ ? (parseFloat(champ.value) || 0) : 0;
    const reste   = Math.max(0, total - acompte);

    const resteEl = document.getElementById('montant_reste');
    if (resteEl) resteEl.innerText = reste.toLocaleString('fr-FR') + ' FCFA';
}

function mettreAJourCompteur() {
    const label = document.getElementById('label_nb_articles');
    if (label) label.innerText = panier.length + ' article(s) ajouté(s)';
}

// ─────────────────────────────────────────────────────────────
// RESET MODAL
// ─────────────────────────────────────────────────────────────
function resetModal() {
    const selCat  = document.getElementById('sel_categorie');
    const selLib  = document.getElementById('sel_libelle');
    const couleur = document.getElementById('art_couleur');
    const marque  = document.getElementById('art_marque');
    const matiere = document.getElementById('art_matiere');
    const obs     = document.getElementById('art_obs');
    const express = document.getElementById('options_express');
    const zone    = document.getElementById('zone_prestation');

    if (selCat)  selCat.value          = '';
    if (selLib)  {
        selLib.innerHTML = '<option value="">-- Choisir Article --</option>';
        selLib.disabled  = true;
    }
    if (couleur) couleur.value         = '';
    if (marque)  marque.value          = '';
    if (matiere) matiere.value         = '';
    if (obs)     obs.value             = '';
    if (express) express.checked       = false;
    if (zone)    zone.classList.add('d-none');

    resetPrixAffichage();
}