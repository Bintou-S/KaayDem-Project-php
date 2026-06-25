<?php

declare(strict_types=1);

define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

require_once ROOT_PATH . '/config/autoload.php';
require_once ROOT_PATH . '/config/database.php';

session_start();

use App\Core\Router;

$router = new Router();

$router->get('/', 'AuthController@index');
$router->get('/inscription', 'AuthController@showInscription');
$router->post('/inscription', 'AuthController@inscription');
$router->get('/connexion', 'AuthController@showConnexion');
$router->post('/connexion', 'AuthController@connexion');
$router->get('/deconnexion', 'AuthController@deconnexion');

$router->get('/trajets', 'TrajetController@index');
$router->get('/trajets/recherche', 'TrajetController@recherche');
$router->get('/trajets/nouveau', 'TrajetController@nouveau');
$router->post('/trajets/nouveau', 'TrajetController@creer');
$router->get('/trajets/{id}', 'TrajetController@voir');
$router->get('/trajets/{id}/modifier', 'TrajetController@modifier');
$router->post('/trajets/{id}/modifier', 'TrajetController@mettreAJour');
$router->post('/trajets/{id}/annuler', 'TrajetController@annuler');
$router->post('/trajets/{id}/clore', 'TrajetController@clore');

$router->post('/reservations/creer', 'ReservationController@creer');
$router->post('/reservations/{id}/confirmer', 'ReservationController@confirmer');
$router->post('/reservations/{id}/annuler', 'ReservationController@annuler');
$router->post('/reservations/{id}/terminer', 'ReservationController@terminer');

$router->get('/evaluations/nouveau/{reservationId}', 'EvaluationController@nouveau');
$router->post('/evaluations/nouveau/{reservationId}', 'EvaluationController@creer');

$router->get('/dashboard', 'DashboardController@index');
$router->get('/dashboard/conducteur', 'DashboardController@conducteur');
$router->get('/dashboard/passager', 'DashboardController@passager');

$router->get('/admin', 'AdminController@index');
$router->get('/admin/conducteurs', 'AdminController@conducteurs');
$router->post('/admin/conducteurs/{id}/valider', 'AdminController@valider');
$router->post('/admin/conducteurs/{id}/refuser', 'AdminController@refuser');
$router->post('/admin/comptes/{id}/suspendre', 'AdminController@suspendre');
$router->get('/admin/signalements', 'AdminController@signalements');
$router->post('/admin/signalements/{id}/traiter', 'AdminController@traiterSignalement');
$router->get('/admin/statistiques', 'AdminController@statistiques');

$router->get('/profil/conducteur', 'ProfilController@demanderStatut');
$router->post('/profil/conducteur', 'ProfilController@soumettreStatut');

$router->dispatch();
