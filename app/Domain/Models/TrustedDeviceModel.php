<?php

declare(strict_types=1);

namespace App\Domain\Models;

/**
 * Model for managing Trusted Devices for 2FA.
 */
class TrustedDeviceModel extends BaseModel
{
    // saving a new device token so they dont have to do 2fa every single time
   public function create(int $userId, string $token, string $deviceInfo, string $ipAddress): bool
    {
        // setting it to expire in 30 days
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));

        // changing 'token' to 'device_token' in the SQL cause i named it diff in the db
        $sql = "INSERT INTO trusted_devices (user_id, device_token, device_info, ip_address, expires_at)
                VALUES (:user_id, :token, :device_info, :ip_address, :expires_at)";

        $affectedRows = $this->execute($sql, [
            'user_id' => $userId,
            'token' => $token,
            'device_info' => $deviceInfo,
            'ip_address' => $ipAddress,
            'expires_at' => $expiresAt
        ]);

        return $affectedRows > 0;
    }

   // checking if the token in their cookie is actually valid and belongs to them
  public function verify(int $userId, string $token): bool
    {
        // make sure its not expired yet
        $sql = "SELECT id FROM trusted_devices
                WHERE user_id = :user_id
                AND device_token = :token
                AND expires_at > NOW()
                LIMIT 1";

        $result = $this->selectOne($sql, [
            'user_id' => $userId,
            'token' => $token
        ]);

        // if  found it, updating the timestamp so know it was used recently
        if ($result) {
            $this->updateLastUsed($result['id']);
            return true;
        }

        return false;
    }

   // just a helper to update the last_used_at column
  private function updateLastUsed(int $id): void
    {
        $sql = "UPDATE trusted_devices SET last_used_at = NOW() WHERE id = :id";
        $this->execute($sql, ['id' => $id]);
    }

    // delete the token if they logout or uncheck the box
   public function remove(int $userId, string $token): void
    {
       
        $sql = "DELETE FROM trusted_devices WHERE user_id = :user_id AND device_token = :token";
        $this->execute($sql, [
            'user_id' => $userId,
            'token' => $token
        ]);
    }
}
