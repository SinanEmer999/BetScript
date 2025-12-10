<?php

declare(strict_types=1);

namespace BetScript\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use BetScript\Services\UserService;

class ApiController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function getUserPoints(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            $response->getBody()->write(json_encode(['error' => 'Not authenticated']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        $user = $this->userService->getUserById($userId);
        
        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'User not found']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $response->getBody()->write(json_encode([
            'fietzPoints' => $user->getFietzPoints(),
            'username' => $user->getUsername()
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }
}
