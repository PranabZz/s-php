<?php

namespace App\Models;

use Sphp\Core\Models;

class UserTokens extends Models
{
    public function __construct()
    {
        $this->table = "user_tokens";
        $this->fillables = ['user_id', 'token_hash', 'scopes', 'expires_at', 'revoked_at'];
        parent::__construct();
    }

    /**
     * Revoke tokens for a given user by setting revoked_at timestamp.
     * @param int $userId The ID of the user whose tokens should be revoked.
     * @return bool
     */
    public function revokeUserTokens(int $userId): bool
    {
        $updateData = ['revoked_at' => date('Y-m-d H:i:s')];

        $stmt = $this->db->connection->prepare(
            "UPDATE {$this->table} SET revoked_at = :revoked_at WHERE user_id = :user_id AND revoked_at IS NULL"
        );
        return $stmt->execute([':revoked_at' => date('Y-m-d H:i:s'), ':user_id' => $userId]);
    }
}
