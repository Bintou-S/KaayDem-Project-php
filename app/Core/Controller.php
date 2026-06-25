<?php
declare(strict_types=1);
namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $data = []): void
    {
        // Convert dot notation to path: 'auth.connexion' => 'auth/connexion'
        $viewPath = ROOT_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "Vue introuvable : {$view} ({$viewPath})";
            return;
        }

        extract($data);
        $flash = $this->getFlash();

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require ROOT_PATH . '/app/Views/layouts/main.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: /kaaydem75' . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/connexion');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->redirect('/dashboard');
        }
    }

    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
