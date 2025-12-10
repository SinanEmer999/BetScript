<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\UserService;

class AuthController
{
    private Twig $twig;
    private UserService $userService;

    public function __construct(Twig $twig, UserService $userService)
    {
        $this->twig = $twig;
        $this->userService = $userService;
    }

    public function showLogin(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'auth/login.twig');
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->userService->authenticate($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();

            $response->getBody()->write(json_encode([
                'success' => true,
                'redirect' => '/',
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Invalid credentials',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }

    public function showRegister(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->twig->render($response, 'auth/register.twig');
    }

    public function register(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        error_log('Register attempt - Received data: ' . json_encode($data));
        
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        
        error_log("Register - Username: '$username', Email: '$email', Password length: " . strlen($password));

        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            error_log('Register failed: Empty fields');
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Bitte alle Felder ausfüllen',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (strlen($username) < 3) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Username muss mindestens 3 Zeichen lang sein',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Ungültige Email-Adresse',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        if (strlen($password) < 6) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'error' => 'Passwort muss mindestens 6 Zeichen lang sein',
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $user = $this->userService->createUser($username, $email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['username'] = $user->getUsername();

            $response->getBody()->write(json_encode([
                'success' => true,
                'redirect' => '/',
            ]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Username oder Email bereits vergeben',
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    public function logout(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        session_destroy();
        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
