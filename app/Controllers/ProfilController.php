<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Repositories\MembreRepository;
use App\Models\Repositories\ProfilConducteurRepository;
use App\Exceptions\KaayDemException;

class ProfilController extends Controller
{
    private MembreRepository $membreRepo;
    private ProfilConducteurRepository $profilRepo;

    public function __construct()
    {
        $this->membreRepo = new MembreRepository();
        $this->profilRepo = new ProfilConducteurRepository();
    }

    public function demanderStatut(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->setFlash('error', 'Les administrateurs ne peuvent pas devenir conducteurs.');
            $this->redirect('/admin');
            return;
        }
        $membre = $this->membreRepo->findAvecProfil((int) $_SESSION['user_id']);
        $this->render('profil.conducteur', [
            'titre'  => 'Devenir conducteur',
            'membre' => $membre,
        ]);
    }

    public function soumettreStatut(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            $this->redirect('/admin');
            return;
        }
        $membre = $this->membreRepo->findAvecProfil((int) $_SESSION['user_id']);

        $existant = $this->profilRepo->findByMembre((int) $_SESSION['user_id']);
        if ($existant) {
            $this->setFlash('info', 'Votre demande est déjà en cours de traitement.');
            $this->redirect('/dashboard');
            return;
        }

        try {
            $profil = $membre->demanderStatutConducteur(
                trim($_POST['numero_permis'] ?? ''),
                trim($_POST['marque_vehicule'] ?? ''),
                trim($_POST['modele_vehicule'] ?? ''),
                trim($_POST['immatriculation'] ?? ''),
                (int) ($_POST['nb_places'] ?? 4)
            );
            $this->profilRepo->save($profil);
            $this->setFlash('success', 'Demande soumise ! Un administrateur va valider votre profil.');
            $this->redirect('/dashboard');

        } catch (KaayDemException $e) {
            $this->render('profil.conducteur', [
                'titre'   => 'Devenir conducteur',
                'membre'  => $membre,
                'erreurs' => [$e->getMessage()],
            ]);
        }
    }
}