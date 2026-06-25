<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entities\Evaluation;
use App\Models\Repositories\ReservationRepository;
use App\Models\Repositories\EvaluationRepository;
use App\Models\Repositories\MembreRepository;

class EvaluationController extends Controller
{
    private ReservationRepository $reservationRepo;
    private EvaluationRepository $evaluationRepo;
    private MembreRepository $membreRepo;

    public function __construct()
    {
        $this->reservationRepo = new ReservationRepository();
        $this->evaluationRepo  = new EvaluationRepository();
        $this->membreRepo      = new MembreRepository();
    }

    public function nouveau(string $reservationId): void
    {
        $this->requireAuth();
        $reservation = $this->reservationRepo->find((int) $reservationId);

        if (!$reservation
            || $reservation->getPassager()->getId() !== (int) $_SESSION['user_id']
            || $reservation->getStatut()->value !== 'terminee'
            || $this->evaluationRepo->existePourReservation((int) $reservationId)
        ) {
            $this->setFlash('error', 'Impossible d\'évaluer cette réservation.');
            $this->redirect('/dashboard/passager');
            return;
        }

        $this->render('evaluations.formulaire', [
            'titre'       => 'Évaluer le conducteur',
            'reservation' => $reservation,
        ]);
    }

    public function creer(string $reservationId): void
    {
        $this->requireAuth();
        $reservation = $this->reservationRepo->find((int) $reservationId);
        $evaluateur  = $this->membreRepo->find((int) $_SESSION['user_id']);

        if (!$reservation || !$evaluateur) {
            $this->redirect('/dashboard/passager');
            return;
        }

        $note        = (int) ($_POST['note'] ?? 0);
        $commentaire = trim($_POST['commentaire'] ?? '');

        if ($note < 1 || $note > 5 || empty($commentaire)) {
            $this->render('evaluations.formulaire', [
                'titre'       => 'Évaluer le conducteur',
                'reservation' => $reservation,
                'erreurs'     => ['Note (1-5) et commentaire obligatoires.'],
            ]);
            return;
        }

        $conducteur = $this->membreRepo->find($reservation->getTrajet()->getConducteur()->getId());

        $evaluation = new Evaluation($reservation, $evaluateur, $conducteur, $note, $commentaire);
        $this->evaluationRepo->save($evaluation);

        $this->setFlash('success', 'Merci pour votre évaluation !');
        $this->redirect('/dashboard/passager');
    }
}
