<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'AuthController::index');


$routes->get('/admin/dashboard', 'DasboardAdminController::index');


$routes->get('/ficheclient/(:num)', 'ClientController::ficheclient/$1');
$routes->get('/client', 'ClientController::index');
$routes->post('/saveclient', 'ClientController::saveclient');
$routes->post('/updateclient/(:num)', 'ClientController::updateclient/$1');
$routes->get('/deleteclient/(:num)', 'ClientController::deleteclient/$1');


$routes->post('/receptionniste/saveabonnement/(:num)', 'DashboardRecepController::saveabonnement/$1');
$routes->get('/receptionniste/ficheabonnement', 'DashboardRecepController::ficheabonnement');

$routes->get('/receptionniste/fichedepot', 'DashboardRecepController::fichedepot');
$routes->get('/receptionniste/fichecommande', 'DashboardRecepController::commande');
$routes->get('/receptionniste/detailcommande', 'DashboardRecepController::detailcommande');

$routes->get('/prestation', 'PrestationController::index');
$routes->post('/services/save', 'PrestationController::save');
$routes->get('/services/delete/(:num)', 'PrestationController::delete/$1');
$routes->post('/services/update/(:num)', 'PrestationController::update/$1');

$routes->get('/grillepro', 'GrilleProController::index');
$routes->post('/grilles/save', 'GrilleProController::save');
$routes->post('/grilles/update/(:num)', 'GrilleProController::update/$1');
$routes->get('/grilles/delete/(:num)', 'GrilleProController::delete/$1');
$routes->get('/grilles/tarifs/(:num)', 'GrilleProController::tarifs/$1');
$routes->post('grilles/savetarif', 'GrilleProController::save_tarif_specifique');
$routes->get('grilles/delete_tarif/(:num)', 'GrilleProController::delete_tarif/$1');

$routes->get('/libelle', 'LibelleController::index');
$routes->post('/libelle/save', 'LibelleController::savelibelle');

$routes->get('/depot', 'DepotController::listedepot');
$routes->get('/depot/nouveau', 'DepotController::index');
$routes->post('depot/valider',                           'DepotController::valider');
$routes->get('depot/getPrestationsByArticle/(:num)',     'DepotController::getPrestationsByArticle/$1');
$routes->get('depot/detail/(:num)',        'DepotController::detail/$1');
$routes->get('depot/imprimer/(:num)',      'DepotController::imprimerBon/$1');
$routes->get('depot/fiche-prod/(:num)',    'DepotController::imprimerFiche/$1');
$routes->get('depot/ticket/(:num)/(:num)', 'DepotController::ticket/$1/$2');
$routes->post('depot/payer/(:num)', 'DepotController::marquerPaye/$1');

// ── Production ──────────────────────────────────────
$routes->get ('production',                      'ProductionController::kanban');
$routes->get ('production/scan',                 'ProductionController::scan');
$routes->post('production/avancer',              'ProductionController::avancer');
$routes->get ('production/article/(:num)',        'ProductionController::articleDetail/$1');
$routes->get ('production/alertes',              'ProductionController::alertes');

// API temps réel pour le Kanban (polling AJAX)
$routes->get ('production/api/kanban',           'ProductionController::apiKanban');
$routes->get ('production/api/stats',            'ProductionController::apiStats');

// __Notification ________________________________________
$routes->get ('notifications',                      'NotificationController::index');
$routes->post('notifications/lire/(:num)',           'NotificationController::marquerLu/$1');
$routes->post('notifications/lire-tout',             'NotificationController::marquerToutLu');
$routes->get ('notifications/api/non-lues',          'NotificationController::apiNonLues');

$routes->get ('campagnes',                           'NotificationController::campagnes');
$routes->get ('campagnes/nouvelle',                  'NotificationController::nouvelleCampagne');
$routes->post('campagnes/sauvegarder',               'NotificationController::sauvegarderCampagne');
$routes->post('campagnes/lancer/(:num)',              'NotificationController::lancerCampagne/$1');

// ── Personnel ────────────────────────────────────────
$routes->get ('personnel',                        'EmployeController::index');
$routes->post('personnel/store',                  'EmployeController::store');
$routes->post('personnel/update/(:num)',          'EmployeController::update/$1');
$routes->get ('personnel/delete/(:num)',          'EmployeController::delete/$1');

// ── Pointage ─────────────────────────────────────────
$routes->post('personnel/pointer',               'EmployeController::pointer');
$routes->post('personnel/pointer-qr',            'EmployeController::pointerQr');
$routes->get ('personnel/pointages',             'EmployeController::pointages');

// ── Planning ─────────────────────────────────────────
$routes->get ('personnel/planning',              'EmployeController::planning');
$routes->post('personnel/planning/sauvegarder',  'EmployeController::sauvegarderPlanning');
$routes->get ('personnel/planning/delete/(:num)','EmployeController::supprimerPlanning/$1');

// ── Productivité ──────────────────────────────────────
$routes->get ('personnel/productivite',          'EmployeController::productivite');

// ── API temps réel ────────────────────────────────────
$routes->get ('personnel/api/stats',             'EmployeController::apiStats');

// ── Postes ───────────────────────────────────────────
$routes->get ('position',                 'PosteController::index');
$routes->post('position/store',           'PosteController::store');
$routes->post('position/update/(:num)',   'PosteController::update/$1');
$routes->get ('position/delete/(:num)',   'PosteController::delete/$1');

// ── Shops ────────────────────────────────────────────
$routes->get ('shop',                     'ShopController::index');
$routes->post('shop/store',               'ShopController::store');
$routes->post('shop/update/(:num)',       'ShopController::update/$1');
$routes->get ('shop/delete/(:num)',       'ShopController::delete/$1');