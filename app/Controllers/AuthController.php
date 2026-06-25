<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Entities\Membre;
use App\Models\Repositories\MembreRepository;
use App\Enums\StatutCompte;

class AuthController extends Controller
{
    private MembreRepository $repo;

    public function __construct()
    {
        $this->repo = new MembreRepository();
    }

    public function index(): void
    {
        if (!empty($_SESSION['user_id'])) $this->redirect('/dashboard');
        $this->redirect('/trajets');
    }

    public function showInscription(): void
    {
        $this->render('auth.inscription', ['titre' => 'Inscription']);
    }

    public function inscription(): void
    {
        $nom     = trim($_POST['nom'] ?? '');
        $prenom  = trim($_POST['prenom'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $tel     = trim($_POST['telephone'] ?? '');
        $mdp     = $_POST['mot_de_passe'] ?? '';
        $mdpConf = $_POST['mot_de_passe_confirm'] ?? '';

        $erreurs = [];
        if (empty($nom) || empty($prenom)) $erreurs[] = "Nom et prénom obligatoires.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
        if (strlen($mdp) < 8) $erreurs[] = "Mot de passe trop court (min 8 caractères).";
        if ($mdp !== $mdpConf) $erreurs[] = "Les mots de passe ne correspondent pas.";
        if ($this->repo->findByEmail($email)) $erreurs[] = "Cet email est déjà utilisé.";

        if (!empty($erreurs)) {
            $this->render('auth.inscription', ['titre' => 'Inscription', 'erreurs' => $erreurs, 'ancien' => $_POST]);
            return;
        }

        $membre = new Membre(
            $nom, $prenom, $email,
            password_hash($mdp, PASSWORD_BCRYPT),
            $tel,
            StatutCompte::from(StatutCompte::ACTIF)
        );

        $this->repo->save($membre);
        $this->setFlash('success', 'Compte créé avec succès ! Connectez-vous.');
        $this->redirect('/connexion');
    }

    public function showConnexion(): void
    {
        if (!empty($_SESSION['user_id'])) $this->redirect('/dashboard');
        $this->render('auth.connexion', ['titre' => 'Connexion']);
    }

    public function connexion(): void
    {
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';

        $utilisateur = $this->repo->findByEmail($email);

        if (!$utilisateur || !$utilisateur->verifierMotDePasse($mdp)) {
            $this->render('auth.connexion', ['titre' => 'Connexion', 'erreurs' => ['Email ou mot de passe incorrect.']]);
            return;
        }

        if ($utilisateur->getStatutCompte()->value === StatutCompte::SUSPENDU) {
            $this->render('auth.connexion', ['titre' => 'Connexion', 'erreurs' => ['Votre compte a été suspendu.']]);
            return;
        }

        $_SESSION['user_id']   = $utilisateur->getId();
        $_SESSION['user_nom']  = $utilisateur->getNomComplet();
        $_SESSION['user_role'] = $utilisateur->getRole();

        $this->setFlash('success', 'Bienvenue ' . $utilisateur->getPrenom() . ' !');
        $this->redirect('/dashboard');
    }

    public function deconnexion(): void
    {
        session_destroy();
        $this->redirect('/connexion');
    }
}
