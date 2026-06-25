<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repositories\TrajetRepository;
use App\Models\Repositories\MembreRepository;
use App\Exceptions\KaayDemException;

class TrajetController extends Controller
{
    private TrajetRepository $trajetRepo;
    private MembreRepository $membreRepo;

    public function __construct()
    {
        $this->trajetRepo = new TrajetRepository();
        $this->membreRepo = new MembreRepository();
    }

    public function index(): void
    {
        $trajets = $this->trajetRepo->rechercher([], 1, 10);
        $total   = $this->trajetRepo->compterTotal();
        $this->render('trajets.index', [
            'titre'   => 'Trajets disponibles',
            'trajets' => $trajets,
            'total'   => $total,
        ]);
    }

    public function recherche(): void
    {
        $criteres = [
            'villeDepart'  => trim($_GET['depart'] ?? ''),
            'villeArrivee' => trim($_GET['arrivee'] ?? ''),
            'date'         => $_GET['date'] ?? '',
            'prixMax'      => $_GET['prix_max'] ?? null,
            'placesMin'    => $_GET['places_min'] ?? null,
        ];
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $trajets = $this->trajetRepo->rechercher($criteres, $page);
        $total   = $this->trajetRepo->compterTotal($criteres);

        $this->render('trajets.recherche', [
            'titre'    => 'Résultats de recherche',
            'trajets'  => $trajets,
            'criteres' => $criteres,
            'page'     => $page,
            'total'    => $total,
        ]);
    }

    public function nouveau(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->setFlash('error', 'Les administrateurs ne peuvent pas publier de trajets.');
            $this->redirect('/admin');
            return;
        }
        $membre = $this->membreRepo->findAvecProfil((int) $_SESSION['user_id']);
        if (!$membre || !$membre->peutConduire()) {
            $this->setFlash('error', 'Vous devez être conducteur validé pour publier un trajet.');
            $this->redirect('/profil/conducteur');
            return;
        }
        $this->render('trajets.formulaire', ['titre' => 'Nouveau trajet']);
    }

    public function creer(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('/admin');
            return;
        }
        $membre = $this->membreRepo->findAvecProfil((int) $_SESSION['user_id']);

        try {
            $arrets = array_filter(array_map('trim', explode(',', $_POST['arrets'] ?? '')));

            $trajet = $membre->publierTrajet(
                $_POST['ville_depart'] ?? '',
                $_POST['ville_arrivee'] ?? '',
                new \DateTime($_POST['date_heure_depart']),
                (int) ($_POST['nb_places'] ?? 1),
                (float) ($_POST['prix_par_place'] ?? 0),
                array_values($arrets)
            );

            $this->trajetRepo->save($trajet);
            $this->setFlash('success', 'Trajet publié avec succès !');
            $this->redirect('/dashboard/conducteur');

        } catch (KaayDemException $e) {
            $this->render('trajets.formulaire', [
                'titre'   => 'Nouveau trajet',
                'erreurs' => [$e->getMessage()],
                'ancien'  => $_POST,
            ]);
        }
    }

    public function voir(string $id): void
    {
        $trajet = $this->trajetRepo->find((int) $id);
        if (!$trajet) {
            http_response_code(404);
            $this->render('errors.404', []);
            return;
        }
        $this->render('trajets.detail', ['titre' => 'Détail du trajet', 'trajet' => $trajet]);
    }

    public function modifier(string $id): void
    {
        $this->requireAuth();
        $trajet = $this->trajetRepo->find((int) $id);
        if (!$trajet || $trajet->getConducteur()->getId() !== (int) $_SESSION['user_id']) {
            $this->redirect('/dashboard');
            return;
        }
        $this->render('trajets.formulaire', ['titre' => 'Modifier le trajet', 'trajet' => $trajet]);
    }

    public function mettreAJour(string $id): void
    {
        $this->requireAuth();
        $trajet = $this->trajetRepo->find((int) $id);

        if (!$trajet || $trajet->getConducteur()->getId() !== (int) $_SESSION['user_id']) {
            $this->redirect('/dashboard');
            return;
        }

        try {
            $trajet->mettreAJour([
                'villeDepart'     => $_POST['ville_depart'] ?? $trajet->getVilleDepart(),
                'villeArrivee'    => $_POST['ville_arrivee'] ?? $trajet->getVilleArrivee(),
                'dateHeureDepart' => new \DateTime($_POST['date_heure_depart']),
                'nbPlacesTotal'   => (int) ($_POST['nb_places'] ?? $trajet->getNbPlacesTotal()),
                'prixParPlace'    => (float) ($_POST['prix_par_place'] ?? $trajet->getPrixParPlace()),
            ]);
            $this->trajetRepo->save($trajet);
            $this->setFlash('success', 'Trajet modifié.');
            $this->redirect('/trajets/' . $id);

        } catch (KaayDemException $e) {
            $this->render('trajets.formulaire', [
                'titre'   => 'Modifier le trajet',
                'trajet'  => $trajet,
                'erreurs' => [$e->getMessage()],
            ]);
        }
    }

    public function annuler(string $id): void
    {
        $this->requireAuth();
        $trajet = $this->trajetRepo->find((int) $id);
        if ($trajet && $trajet->getConducteur()->getId() === (int) $_SESSION['user_id']) {
            $trajet->annuler();
            $this->trajetRepo->save($trajet);
            $this->setFlash('success', 'Trajet annulé.');
        }
        $this->redirect('/dashboard/conducteur');
    }

    public function clore(string $id): void
    {
        $this->requireAuth();
        $trajet = $this->trajetRepo->find((int) $id);
        if ($trajet && $trajet->getConducteur()->getId() === (int) $_SESSION['user_id']) {
            $trajet->clore();
            $this->trajetRepo->save($trajet);
            $this->setFlash('success', 'Trajet clôturé.');
        }
        $this->redirect('/dashboard/conducteur');
    }
}