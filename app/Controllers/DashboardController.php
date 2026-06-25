<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repositories\TrajetRepository;
use App\Models\Repositories\ReservationRepository;
use App\Models\Repositories\MembreRepository;
use App\Models\Repositories\EvaluationRepository;

class DashboardController extends Controller
{
    private TrajetRepository $trajetRepo;
    private ReservationRepository $reservationRepo;
    private MembreRepository $membreRepo;
    private EvaluationRepository $evalRepo;

    public function __construct()
    {
        $this->trajetRepo      = new TrajetRepository();
        $this->reservationRepo = new ReservationRepository();
        $this->membreRepo      = new MembreRepository();
        $this->evalRepo        = new EvaluationRepository();
    }

    public function index(): void
    {
        $this->requireAuth();
        $role = $_SESSION['user_role'] ?? 'membre';

        if ($role === 'admin') {
            $this->redirect('/admin');
        } else {
            $this->redirect('/dashboard/passager');
        }
    }

    public function conducteur(): void
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $membre = $this->membreRepo->findAvecProfil($userId);

        $trajets = $this->trajetRepo->findParConducteur($userId);

        // Récupérer les réservations de chaque trajet
        $reservationsParTrajet = [];
        foreach ($trajets as $trajet) {
            $reservationsParTrajet[$trajet->getId()] = $this->reservationRepo->findParTrajet($trajet->getId());
        }

        $evaluations = $this->evalRepo->findParEvalue($userId);

        $this->render('dashboard.conducteur', [
            'titre'                 => 'Mon espace conducteur',
            'membre'                => $membre,
            'trajets'               => $trajets,
            'reservationsParTrajet' => $reservationsParTrajet,
            'evaluations'           => $evaluations,
        ]);
    }

    public function passager(): void
    {
        $this->requireAuth();
        $userId      = (int) $_SESSION['user_id'];
        $membre      = $this->membreRepo->findAvecProfil($userId);
        $reservations = $this->reservationRepo->findParPassager($userId);

        $this->render('dashboard.passager', [
            'titre'        => 'Mes réservations',
            'membre'       => $membre,
            'reservations' => $reservations,
        ]);
    }
}
