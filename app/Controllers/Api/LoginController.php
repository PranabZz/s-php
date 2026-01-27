<?php

namespace App\Controllers\Api;

use Sphp\Core\ApiController;
use Sphp\Core\Request;
use Sphp\Core\Response;
use App\Models\Users;
use App\Models\UserTokens;

class LoginController extends ApiController
{
    public function login()
    {
        $request = new Request();
        $email = $request->param('email');
        $password = $request->param('password');

        if (empty($email) || empty($password)) {
            return $this->errorResponse('Email and password are required', 400);
        }

        $userModel = new Users();
        $userResult = $userModel->select(['id', 'name', 'email', 'password'], ['email' => $email]);
        $user = $userResult[0] ?? null;

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $userId = $user['id'];

        $userTokensModel = new UserTokens();
        $userTokensModel->revokeUserTokens($userId);

        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $scopes = json_encode(['read', 'write']);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day'));

        $userTokensModel->create([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'scopes' => $scopes,
            'expires_at' => $expiresAt
        ]);

        return $this->successResponse('Login successful', [
            'access_token' => $rawToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt
        ]);
    }
}
