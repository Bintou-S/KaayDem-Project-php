<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repositories\ReservationRepository;
use App\Models\Repositories\TrajetRepository;
use App\Models\Repositories\MembreRepository;
use App\Exceptions\KaayDemException;

class ReservationController extends Controller
{
    private ReservationRepository $repo;
    private TrajetRepository $trajetRepo;
    private MembreRepository $membreRepo;

    public function __construct()
    {
        $this->repo       = new ReservationRepository();
        $this->trajetRepo = new TrajetRepository();
        $this->membreRepo = new MembreRepository();
    }

    public function creer(): void
    {
        $this->requireAuth();

        $trajetId = (int) ($_POST['trajet_id'] ?? 0);
        $nbPlaces = (int) ($_POST['nb_places'] ?? 1);

        $trajet = $this->trajetRepo->find($trajetId);
        $membre = $this->membreRepo->findAvecProfil((int) $_SESSION['user_id']);

        if (!$trajet || !$membre) {
            $this->setFlash('error', 'Trajet introuvable.');
            $this->redirect('/trajets');
            return;
        }

        // Vérifier chevauchement en base
        $chevauchements = $this->repo->findChevauchements(
            $membre->getId(),
            $trajet->getDateHeureDepart()
        );

        if (!empty($chevauchements)) {
            $this->setFlash('error', 'Vous avez déjà une réservation qui se chevauche avec ce trajet.');
            $this->redirect('/trajets/' . $trajetId);
            return;
        }

        try {
            $reservation = $membre->reserver($trajet, $nbPlaces);
            $this->trajetRepo->save($trajet);
            $this->repo->save($reservation);
            $this->setFlash('success', 'Réservation effectuée ! En attente de confirmation du conducteur.');
            $this->redirect('/dashboard/passager');

        } catch (KaayDemException $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/trajets/' . $trajetId);
        }
    }

    public function confirmer(string $id): void
    {
        $this->requireAuth();
        $reservation = $this->repo->find((int) $id);

        if (!$reservation || $reservation->getTrajet()->getConducteur()->getId() !== (int) $_SESSION['user_id']) {
            $this->redirect('/dashboard/conducteur');
            return;
        }

        try {
            $reservation->confirmer();
            $this->repo->save($reservation);
            $this->setFlash('success', 'Réservation confirmée.');
        } catch (KaayDemException $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/conducteur');
    }

    public function annuler(string $id): void
    {
        $this->requireAuth();
        $reservation = $this->repo->find((int) $id);
        $userId = (int) $_SESSION['user_id'];

        $estPassager   = $reservation?->getPassager()->getId() === $userId;
        $estConducteur = $reservation?->getTrajet()->getConducteur()->getId() === $userId;

        if (!$reservation || (!$estPassager && !$estConducteur)) {
            $this->redirect('/dashboard');
            return;
        }

        try {
            $reservation->annuler();
            $reservation->getTrajet()->libererPlace($reservation->getNbPlacesReservees());
            $this->trajetRepo->save($reservation->getTrajet());
            $this->repo->save($reservation);
            $this->setFlash('success', 'Réservation annulée.');
        } catch (KaayDemException $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect($estPassager ? '/dashboard/passager' : '/dashboard/conducteur');
    }

    public function terminer(string $id): void
    {
        $this->requireAuth();
        $reservation = $this->repo->find((int) $id);

        if (!$reservation || $reservation->getTrajet()->getConducteur()->getId() !== (int) $_SESSION['user_id']) {
            $this->redirect('/dashboard/conducteur');
            return;
        }

        try {
            $reservation->terminer();
            $this->repo->save($reservation);
            $this->setFlash('success', 'Réservation terminée.');
        } catch (KaayDemException $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/conducteur');
    }
}
