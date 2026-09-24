<?php

namespace Sphp\Auth;

use App\Models\Users;
use Utopia\Auth\Proofs\Password;
use Utopia\Auth\Hashes\Bcrypt;

class Auth
{

  protected static ?array $cached_user = null;
  protected static ?string $session_token = 'auth_user_id';

  // we need to initialize password from utopia

  public static function attempt(array $credentials): bool
  {
    $email = $credentials['email'] ?? null;
    $password = $credentials['password'] ?? null;

    if (!$email || !$password) {
      return false;
    }

    $user = Users::findByEmail($email);

    if (!$user) {
      return false;
    }

    $verifier = new Password([Password::BCRYPT => new Bcrypt()]);
    $hashedPassword = is_array($user) ? ($user['password'] ?? '') : ($user->password ?? '');

    if (!$verifier->verify($password, $hashedPassword)) {
      return false;
    }

    self::login($user);
    return true;
  }

  public static function login($user): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    session_regenerate_id(true);

    $userId = is_array($user) ? $user['id'] : $user->id;
    $_SESSION[self::$session_token] = $userId;
    $_SESSION['user'] = is_array($user) ? $user : (array)$user;

    self::$cached_user = is_array($user) ? $user : (array)$user;
  }

  public static function check(): bool
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    return !empty($_SESSION[self::$session_token]);
  }

  public static function user(): ?array
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    if (self::$cached_user !== null) {
      return self::$cached_user;
    }

    $userId = $_SESSION[self::$session_token] ?? null;
    if (!$userId) {
      return null;
    }

    $user = Users::findByID($userId);
    if ($user) {
      self::$cached_user = is_array($user) ? $user : (array)$user;
      return self::$cached_user;
    }

    return $_SESSION['user'] ?? null;
  }

  public static function logout(): void
  {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }

    unset($_SESSION[self::$session_token]);
    unset($_SESSION['user']);
    self::$cached_user = null;
    session_regenerate_id(true);
  }
}
