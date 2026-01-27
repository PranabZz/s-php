<?php

namespace App\Middleware;

use Sphp\Core\Database;
use Sphp\Core\Request;
use Sphp\Core\Response;

class Pat // No longer extends Middleware based on framework analysis
{
    private Database $db; // Database connection will be instantiated once per request

    public function __construct()
    {
        $config = require __DIR__ . '/../../app/config/config.php';
        try {
            $this->db = new Database($config);
        } catch (\PDOException $e) {
            Response::response(500, 'Database connection failed for middleware: ' . $e->getMessage(), 'error');
            exit();
        }
    }

    public function handle()
    {
        $request = new Request();
        $token = $this->getBearerToken($request);

        if (empty($token)) {
            Response::response(401, 'Unauthorized: Bearer token missing or invalid format');
            return;
        }

        $tokenHash = hash('sha256', $token);

        $stmt = $this->db->connection->prepare(
            "SELECT user_id, scopes, expires_at, revoked_at FROM user_tokens WHERE token_hash = :token_hash"
        );

        $stmt->execute([':token_hash' => $tokenHash]);
        $pat = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$pat) {
            Response::response(401, 'Unauthorized: Invalid token or token not found');
            return;
        }

        if ($pat['revoked_at'] !== null) {
            Response::response(401, 'Unauthorized: Token has been revoked');
            return;
        }

        if (strtotime($pat['expires_at']) <= time()) {
            Response::response(401, 'Unauthorized: Token has expired');
            return;
        }

        // Use the instantiated Request object to set attributes
        $request->setAttribute('user_id', $pat['user_id']);
        $request->setAttribute('scopes', json_decode($pat['scopes'], true));

        $userScopes = $request->getAttribute('scopes');
        $method = $request->method();

        $isAuthorized = false;

        $hasRequiredScope = function (array $requiredScopes) use ($userScopes): bool {
            if (in_array('*', $userScopes)) {
                return true;
            }
            foreach ($requiredScopes as $scope) {
                if (in_array($scope, $userScopes)) {
                    return true;
                }
            }
            return false;
        };

        switch ($method) {
            case 'GET':
                $isAuthorized = $hasRequiredScope(['read', 'write']);
                break;
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                $isAuthorized = $hasRequiredScope(['write']);
                break;
            default:
                $isAuthorized = false;
                break;
        }

        if (!$isAuthorized) {
            Response::response(403, 'Forbidden: Insufficient scopes');
            return;
        }

        return true; // Signal to the Router that the request can proceed
    }

    private function getBearerToken(Request $request): ?string
    {
        $authHeader = $request->getHeader('Authorization');

        if (empty($authHeader)) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
