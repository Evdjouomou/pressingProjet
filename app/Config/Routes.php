<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ── Authentification & Connexion ─────────────────────
$routes->get('/', 'AuthController::index');
$routes->post('login', 'AuthController::login');

$routes->get('/personnel/changer-mot-de-passe', 'EmployeController::changementMotDePasse');
$routes->post('/personnel/update-premier-password', 'EmployeController::updatePremierPassword');

// ── Dashboard Principal (Module du Collègue) ──────────
$routes->get('dashboard',                    'DashboardController::index');
$routes->get('dashboard/api/kpis',           'DashboardController::apiKpis');
$routes->get('dashboard/api/graphiques',     'DashboardController::apiGraphiques');

// Ton Dashboard (Conservé au cas où)
$routes->get('/admin/dashboard',             'DashboardAdminController::index');

// ── Rapports & Statistiques ──────────────────────────
$routes->get('rapports',                     'RapportController::index');
$routes->get('rapports/ca',                  'RapportController::chiffreAffaires');
$routes->get('rapports/depots',              'RapportController::depots');
$routes->get('rapports/clients',             'RapportController::clients');
$routes->get('rapports/employes',            'RapportController::employes');
$routes->get('rapports/prestations',         'RapportController::prestations');

// ── Exports Données ──────────────────────────────────
$routes->get('rapports/export/csv/(:segment)',   'RapportController::exportCsv/$1');
$routes->get('rapports/export/excel/(:segment)', 'RapportController::exportExcel/$1');
$routes->get('rapports/export/pdf/(:segment)',   'RapportController::exportPdf/$1');

// ── Clients & Abonnements ────────────────────────────
$routes->get('ficheclient/(:num)',   'ClientController::ficheclient/$1');
$routes->get('client',               'ClientController::index');
$routes->post('/saveclient',         'ClientController::saveclient');
$routes->post('updateclient/(:num)', 'ClientController::updateclient/$1');
$routes->get('deleteclient/(:num)', 'ClientController::deleteclient/$1');

$routes->post('receptionniste/saveabonnement/(:num)', 'DashboardRecepController::saveabonnement/$1');
$routes->get('receptionniste/ficheabonnement',        'DashboardRecepController::ficheabonnement');
$routes->get('receptionniste/fichedepot',             'DashboardRecepController::fichedepot');
$routes->get('receptionniste/fichecommande',          'DashboardRecepController::commande');
$routes->get('receptionniste/detailcommande',         'DashboardRecepController::detailcommande');

// ── Prestations & Grilles Tarifaires ─────────────────
$routes->get('prestation',              'PrestationController::index');
$routes->post('services/save',          'PrestationController::save');
$routes->get('services/delete/(:num)',  'PrestationController::delete/$1');
$routes->post('services/update/(:num)', 'PrestationController::update/$1');

$routes->get('grillepro',                  'GrilleProController::index');
$routes->post('grilles/save',              'GrilleProController::save');
$routes->post('grilles/update/(:num)',      'GrilleProController::update/$1');
$routes->get('grilles/delete/(:num)',      'GrilleProController::delete/$1');
$routes->get('grilles/tarifs/(:num)',      'GrilleProController::tarifs/$1');
$routes->post('grilles/savetarif',         'GrilleProController::save_tarif_specifique');
$routes->get('grilles/delete_tarif/(:num)','GrilleProController::delete_tarif/$1');

$routes->get('libelle',       'LibelleController::index');
$routes->post('libelle/save', 'LibelleController::savelibelle');

// ── Dépôts & Facturation Pressing ─────────────────────
$routes->get('depot',                                   'DepotController::listedepot');
$routes->get('depot/nouveau',                           'DepotController::index');
$routes->post('depot/valider',                          'DepotController::valider');
$routes->get('depot/getPrestationsByArticle/(:num)',     'DepotController::getPrestationsByArticle/$1');
$routes->get('depot/detail/(:num)',                      'DepotController::detail/$1');
$routes->get('depot/imprimer/(:num)',                    'DepotController::imprimerBon/$1');
$routes->get('depot/fiche-prod/(:num)',                  'DepotController::imprimerFiche/$1');
$routes->get('depot/ticket/(:num)/(:num)',               'DepotController::ticket/$1/$2');
$routes->post('depot/payer/(:num)',                     'DepotController::marquerPaye/$1');

// ── Production & Atelier Kanban ───────────────────────
$routes->get ('production',                      'ProductionController::kanban');
$routes->get ('production/scan',                 'ProductionController::scan');
$routes->post('production/avancer',              'ProductionController::avancer');
$routes->get ('production/article/(:num)',        'ProductionController::articleDetail/$1');
$routes->get ('production/alertes',              'ProductionController::alertes');
$routes->get ('production/api/kanban',           'ProductionController::apiKanban');
$routes->get ('production/api/stats',            'ProductionController::apiStats');

// ── Notifications & Campagnes SMS/Mail ────────────────
$routes->get ('notifications',                      'NotificationController::index');
$routes->post('notifications/lire/(:num)',           'NotificationController::marquerLu/$1');
$routes->post('notifications/lire-tout',             'NotificationController::marquerToutLu');
$routes->get ('notifications/api/non-lues',          'NotificationController::apiNonLues');

$routes->get ('campagnes',                           'NotificationController::campagnes');
$routes->get ('campagnes/nouvelle',                  'NotificationController::nouvelleCampagne');
$routes->post('campagnes/sauvegarder',               'NotificationController::sauvegarderCampagne');
$routes->post('campagnes/lancer/(:num)',              'NotificationController::lancerCampagne/$1');

// ── Ressources Humaines, Pointage & Planning ──────────
$routes->get ('personnel',                        'EmployeController::index');
$routes->post('personnel/store',                  'EmployeController::store');
$routes->post('personnel/update/(:num)',          'EmployeController::update/$1');
$routes->get ('personnel/delete/(:num)',          'EmployeController::delete/$1');
$routes->post('personnel/pointer',               'EmployeController::pointer');
$routes->post('personnel/pointer-qr',            'EmployeController::pointerQr');
$routes->get ('personnel/pointages',             'EmployeController::pointages');
$routes->get ('personnel/planning',              'EmployeController::planning');
$routes->post('personnel/planning/sauvegarder',  'EmployeController::sauvegarderPlanning');
$routes->get ('personnel/planning/delete/(:num)','EmployeController::supprimerPlanning/$1');
$routes->get ('personnel/productivite',          'EmployeController::productivite');
$routes->get ('personnel/api/stats',             'EmployeController::apiStats');

// ── Boutiques & Postes de Travail ────────────────────
$routes->get ('poste',                 'PosteController::index');
$routes->post('poste/store',           'PosteController::store');
$routes->post('poste/update/(:num)',   'PosteController::update/$1');
$routes->get ('poste/delete/(:num)',   'PosteController::delete/$1');

$routes->get ('shop',                     'ShopController::index');
$routes->post('shop/store',               'ShopController::store');
$routes->post('shop/update/(:num)',       'ShopController::update/$1');
$routes->get ('shop/delete/(:num)',       'ShopController::delete/$1');

// ── Terminal de Caisse (POS) ─────────────────────────
$routes->get ('pos',                          'PosController::index');
$routes->get ('pos/commande/(:num)',          'PosController::chargerCommande/$1');
$routes->post('pos/encaisser',               'PosController::encaisser');
$routes->post('pos/rembourser',              'PosController::rembourser');
$routes->get ('pos/recu/(:num)',             'PosController::recu/$1');
$routes->get ('pos/facture/(:num)',          'PosController::facture/$1');
$routes->get ('pos/caisse',                  'PosController::caisse');
$routes->post('pos/caisse/ouvrir',           'PosController::ouvrirCaisse');
$routes->post('pos/caisse/cloturer',         'PosController::cloturerCaisse');
$routes->get ('pos/caisse/rapport/(:num)',   'PosController::rapportCaisse/$1');
$routes->get ('pos/produits',                'PosController::produits');
$routes->post('pos/produits/store',          'PosController::storeProduit');
$routes->post('pos/produits/update/(:num)',  'PosController::updateProduit/$1');
$routes->get ('pos/produits/delete/(:num)', 'PosController::deleteProduit/$1');
$routes->get ('pos/api/recherche',           'PosController::apiRecherche');
$routes->get ('pos/api/caisse-courante',     'PosController::apiCaisseCourante');

// ── Gestion des Stocks Inventaires ───────────────────
$routes->get ('stocks',                        'StockController::index');
$routes->get ('stocks/journal',                'StockController::journal');
$routes->get ('stocks/api/alertes',            'StockController::apiAlertes');
$routes->post('stocks/produit/store',          'StockController::storeProduit');
$routes->post('stocks/produit/update/(:num)',  'StockController::updateProduit/$1');
$routes->get ('stocks/produit/delete/(:num)',  'StockController::deleteProduit/$1');
$routes->get ('stocks/produit/(:num)',         'StockController::detail/$1');
$routes->post('stocks/entree',                 'StockController::entree');
$routes->post('stocks/sortie',                 'StockController::sortie');
$routes->post('stocks/ajustement',             'StockController::ajustement');

// Approvisionnements & Bons de commande
$routes->get ('stocks/bons',                   'StockController::bons');
$routes->post('stocks/bons/store',             'StockController::storeBon');
$routes->get ('stocks/bons/imprimer/(:num)',   'StockController::imprimerBon/$1');
$routes->get ('stocks/bons/recevoir/(:num)',   'StockController::recevoirBon/$1');
$routes->get ('stocks/bons/delete/(:num)',     'StockController::deleteBon/$1');
$routes->get ('stocks/bons/(:num)',            'StockController::detailBon/$1'); // Route générique en dernier

// ── Cycles Machines & Lavage ─────────────────────────
$routes->get ('production/cycles',                       'CycleController::index');
$routes->get ('production/cycles/nouveau',               'CycleController::nouveau');
$routes->post('production/cycles/store',                 'CycleController::store');
$routes->get ('production/cycles/(:num)',                'CycleController::detail/$1');
$routes->post('production/cycles/(:num)/article',        'CycleController::ajouterArticle/$1');
$routes->get ('production/cycles/(:num)/retirer/(:num)', 'CycleController::retirerArticle/$1/$2');
$routes->post('production/cycles/(:num)/terminer',       'CycleController::terminer/$1');
$routes->get ('production/cycles/(:num)/annuler',        'CycleController::annuler/$1');
$routes->get ('production/api/article/(:alphanum)',      'CycleController::apiArticleParBarcode/$1');

// Configuration du parc de machines
$routes->get ('production/machines',               'CycleController::machines');
$routes->post('production/machines/store',         'CycleController::storeMachine');
$routes->post('production/machines/update/(:num)', 'CycleController::updateMachine/$1');
$routes->get ('production/machines/delete/(:num)', 'CycleController::deleteMachine/$1');

// ── Service des Retouches ─────────────────────────────
$routes->get ('retouches',                       'RetoucheController::index');
$routes->get ('retouches/nouvelle',              'RetoucheController::nouveau');
$routes->post('retouches/store',                 'RetoucheController::store');
$routes->get ('retouches/(:num)',                'RetoucheController::detail/$1');
$routes->post('retouches/update/(:num)',         'RetoucheController::update/$1');
$routes->post('retouches/statut/(:num)',         'RetoucheController::changerStatut/$1');
$routes->get ('retouches/delete/(:num)',         'RetoucheController::delete/$1');
$routes->get ('retouches/api/depot/(:num)',      'RetoucheController::apiDepot/$1');

// ── SAV, Réclamations & Incidents ─────────────────────
$routes->get ('incidents',                       'IncidentController::index');
$routes->get ('incidents/nouveau',               'IncidentController::nouveau');
$routes->post('incidents/store',                 'IncidentController::store');
$routes->get ('incidents/(:num)',                'IncidentController::detail/$1');
$routes->post('incidents/update/(:num)',         'IncidentController::update/$1');
$routes->post('incidents/cloturer/(:num)',       'IncidentController::cloturer/$1');
$routes->post('incidents/(:num)/photo',         'IncidentController::ajouterPhoto/$1');
$routes->get ('incidents/photo/delete/(:num)',   'IncidentController::supprimerPhoto/$1');

// Route pour les livraisons
$routes->get('livraison', 'LivraisonController::index');

// Route pour les abonnements (celle qui bloquait juste avant)
$routes->get('abonnements', 'AbonnementController::index');