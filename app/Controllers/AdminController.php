<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repositories\MembreRepository;
use App\Models\Repositories\TrajetRepository;
use App\Models\Repositories\SignalementRepository;
use App\Models\Repositories\ProfilConducteurRepository;
use App\Enums\StatutCompte;
use App\Enums\StatutValidation;

class AdminController extends Controller
{
    private MembreRepository $membreRepo;
    private TrajetRepository $trajetRepo;
    private SignalementRepository $signalementRepo;
    private ProfilConducteurRepository $profilRepo;

    public function __construct()
    {
        $this->membreRepo      = new MembreRepository();
        $this->trajetRepo      = new TrajetRepository();
        $this->signalementRepo = new SignalementRepository();
        $this->profilRepo      = new ProfilConducteurRepository();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $this->render('admin.index', ['titre' => 'Tableau de bord Admin']);
    }

    public function conducteurs(): void
    {
        $this->requireAdmin();
        $enAttente = $this->membreRepo->findConducteursEnAttente();
        $this->render('admin.conducteurs', ['titre' => 'Validation conducteurs', 'enAttente' => $enAttente]);
    }

    public function valider(string $id): void
    {
        $this->requireAdmin();
        $profil = $this->profilRepo->find((int) $id);
        if ($profil) {
            $profil->setStatutValidation(StatutValidation::from(StatutValidation::VALIDE));
            $profil->setDateValidation(new \DateTime());
            $this->profilRepo->save($profil);
            $this->setFlash('success', 'Conducteur validé.');
        }
        $this->redirect('/admin/conducteurs');
    }

    public function refuser(string $id): void
    {
        $this->requireAdmin();
        $profil = $this->profilRepo->find((int) $id);
        if ($profil) {
            $profil->setStatutValidation(StatutValidation::from(StatutValidation::REFUSE));
            $this->profilRepo->save($profil);
            $this->setFlash('success', 'Conducteur refusé.');
        }
        $this->redirect('/admin/conducteurs');
    }

    public function suspendre(string $id): void
    {
        $this->requireAdmin();
        $this->membreRepo->updateStatut((int) $id, StatutCompte::from(StatutCompte::SUSPENDU));
        $this->setFlash('success', 'Compte suspendu.');
        $this->redirect('/admin');
    }

    public function signalements(): void
    {
        $this->requireAdmin();
        $signalements = $this->signalementRepo->findNonTraites();
        $this->render('admin.signalements', ['titre' => 'Signalements', 'signalements' => $signalements]);
    }

    public function traiterSignalement(string $id): void
    {
        $this->requireAdmin();
        $signalement = $this->signalementRepo->find((int) $id);
        if ($signalement) {
            $signalement->marquerTraite();
            $this->signalementRepo->save($signalement);
            $this->setFlash('success', 'Signalement traité.');
        }
        $this->redirect('/admin/signalements');
    }

    public function statistiques(): void
    {
        $this->requireAdmin();
        $pdo = \App\Core\Database::getInstance();

        $stats = [
            'total_membres'      => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role='membre'")->fetchColumn(),
            'total_trajets'      => (int) $pdo->query("SELECT COUNT(*) FROM trajets")->fetchColumn(),
            'total_reservations' => (int) $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn(),
            'taux_occupation'    => $pdo->query("SELECT AVG((nb_places_total - nb_places_dispo) / nb_places_total * 100) FROM trajets WHERE statut != 'annule'")->fetchColumn(),
        ];

        $trajetsParMois = $pdo->query(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') as mois, COUNT(*) as total
             FROM trajets GROUP BY mois ORDER BY mois DESC LIMIT 12"
        )->fetchAll();

        $topConducteurs = $pdo->query(
            "SELECT u.prenom, u.nom, p.note_moyenne, p.nombre_evaluations, COUNT(t.id) as nb_trajets
             FROM profils_conducteur p
             JOIN utilisateurs u ON u.id = p.membre_id
             LEFT JOIN trajets t ON t.conducteur_id = u.id
             WHERE p.statut_validation = 'valide'
             GROUP BY u.id ORDER BY p.note_moyenne DESC LIMIT 5"
        )->fetchAll();

        $this->render('admin.statistiques', [
            'titre' => 'Statistiques', 'stats' => $stats,
            'trajetsParMois' => $trajetsParMois, 'topConducteurs' => $topConducteurs,
        ]);
    }
}
