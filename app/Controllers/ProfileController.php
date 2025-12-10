<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;
use BetScript\Services\UserService;
use BetScript\Services\CosmeticService;

class ProfileController
{
    private Twig $twig;
    private UserService $userService;
    private CosmeticService $cosmeticService;

    public function __construct(
        Twig $twig,
        UserService $userService,
        CosmeticService $cosmeticService
    ) {
        $this->twig = $twig;
        $this->userService = $userService;
        $this->cosmeticService = $cosmeticService;
    }

    public function showProfile(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);

        return $this->twig->render($response, 'profile/view.twig', [
            'user' => $user,
        ]);
    }

    public function showShop(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $user = $this->userService->getUserById($userId);
        $cosmetics = $this->cosmeticService->getAllCosmetics();

        return $this->twig->render($response, 'profile/shop.twig', [
            'user' => $user,
            'cosmetics' => $cosmetics,
        ]);
    }

    public function purchaseCosmetic(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Not authenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $cosmeticId = $data['cosmeticId'] ?? '';

        $success = $this->cosmeticService->purchaseCosmetic($userId, $cosmeticId);

        $response->getBody()->write(json_encode(['success' => $success]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateAvatar(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            $response->getBody()->write(json_encode(['success' => false, 'error' => 'Not authenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $data = $request->getParsedBody();
        $avatar = $data['avatar'] ?? [];

        $user = $this->userService->getUserById($userId);
        if ($user) {
            $user->updateAvatar($avatar);
            $this->userService->updateUser($user);

            $response->getBody()->write(json_encode(['success' => true]));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['success' => false]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
}
