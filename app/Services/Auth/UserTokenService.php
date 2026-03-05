<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserAccessToken;
use App\Models\UserRefreshToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTokenService
{
    const ACCESS_TOKEN_PREFIX = 'uat_';
    const REFRESH_TOKEN_PREFIX = 'urt_';

    public function login($email, $password, $clientType = 'web', $deviceId = null)
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $this->issueTokenPair($user, array('*'), $clientType, $deviceId);
    }

    public function issueTokenPair(User $user, array $capabilities = array('*'), $clientType = 'web', $deviceId = null)
    {
        $normalizedDeviceId = $this->normalizeDeviceId($deviceId);

        return DB::transaction(function () use ($user, $capabilities, $clientType, $normalizedDeviceId) {
            $accessPlain = self::ACCESS_TOKEN_PREFIX . Str::random(64);
            $refreshPlain = self::REFRESH_TOKEN_PREFIX . Str::random(64);

            $access = UserAccessToken::create(array(
                'user_id' => $user->id,
                'token_hash' => hash('sha256', $accessPlain),
                'capabilities' => array_values(array_unique($capabilities)),
                'expires_at' => Carbon::now()->addMinutes(30),
            ));

            $refresh = UserRefreshToken::create(array(
                'user_id' => $user->id,
                'access_token_id' => $access->id,
                'token_hash' => hash('sha256', $refreshPlain),
                'device_id' => $normalizedDeviceId,
                'client_type' => $clientType,
                'expires_at' => Carbon::now()->addDays(14),
            ));

            return array(
                'token_type' => 'Bearer',
                'access_token' => $accessPlain,
                'access_expires_at' => $access->expires_at,
                'refresh_token' => $refreshPlain,
                'refresh_expires_at' => $refresh->expires_at,
                'capabilities' => $access->capabilities,
                'user' => array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ),
            );
        });
    }

    public function refresh($refreshPlain)
    {
        if (strpos($refreshPlain, self::REFRESH_TOKEN_PREFIX) !== 0) {
            return null;
        }

        $refresh = UserRefreshToken::with('user')
            ->where('token_hash', hash('sha256', $refreshPlain))
            ->first();

        if (!$refresh || !$refresh->user || !$refresh->isValid()) {
            return null;
        }

        return DB::transaction(function () use ($refresh) {
            // refresh token 单次使用，刷新后立刻撤销旧token
            $refresh->update(array('revoked_at' => Carbon::now()));

            if ($refresh->access_token_id) {
                UserAccessToken::where('id', $refresh->access_token_id)
                    ->whereNull('revoked_at')
                    ->update(array('revoked_at' => Carbon::now()));
            }

            return $this->issueTokenPair(
                $refresh->user,
                array('*'),
                $refresh->client_type ?: 'web',
                $refresh->device_id
            );
        });
    }

    public function revokeByAccessToken($accessPlain)
    {
        $access = UserAccessToken::where('token_hash', hash('sha256', $accessPlain))->first();

        if (!$access) {
            return false;
        }

        $now = Carbon::now();

        DB::transaction(function () use ($access, $now) {
            UserAccessToken::where('id', $access->id)
                ->whereNull('revoked_at')
                ->update(array('revoked_at' => $now));

            UserRefreshToken::where('access_token_id', $access->id)
                ->whereNull('revoked_at')
                ->update(array('revoked_at' => $now));
        });

        return true;
    }

    public function validateAccessToken($accessPlain)
    {
        if (strpos($accessPlain, self::ACCESS_TOKEN_PREFIX) !== 0) {
            return null;
        }

        $token = UserAccessToken::with('user')
            ->where('token_hash', hash('sha256', $accessPlain))
            ->first();

        if (!$token || !$token->user || !$token->isValid()) {
            return null;
        }

        $token->update(array('last_used_at' => Carbon::now()));

        return $token;
    }

    protected function normalizeDeviceId($deviceId)
    {
        if ($deviceId === null) {
            return null;
        }

        $value = trim((string)$deviceId);
        if ($value === '') {
            return null;
        }

        if (strlen($value) <= 120) {
            return $value;
        }

        // UA 很长时做稳定哈希，避免超过数据库长度限制。
        return 'ua256:' . hash('sha256', $value);
    }
}
