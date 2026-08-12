<?php
/**
 * Authentication Helper
 * PM Dairy Farm
 */

class Auth {

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateToken($userId, $role = 'customer') {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + JWT_EXPIRY
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    public static function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (!hash_equals($expectedSignature, $base64Signature)) return false;

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
        if (!is_array($payload) || empty($payload['user_id']) || empty($payload['exp'])) return false;
        if ($payload['exp'] < time()) return false;

        return $payload;
    }

    public static function getTokenFromRequest() {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        foreach ($headers as $name => $value) {
            if (strtolower($name) === 'authorization' && preg_match('/^Bearer\s+(\S+)$/i', $value, $matches)) {
                return $matches[1];
            }
        }

        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        if (isset($_SESSION['auth_token'])) {
            return $_SESSION['auth_token'];
        }
        return null;
    }

    public static function getUser() {
        $token = self::getTokenFromRequest();
        if (!$token) return null;

        $payload = self::verifyToken($token);
        if (!$payload) return null;

        $db = Database::getInstance();
        return $db->fetchOne(
            "SELECT id, name, phone, email, role, avatar, is_verified, created_at FROM users WHERE id = ?",
            [$payload['user_id']],
            'i'
        );
    }

    public static function isLoggedIn() {
        return self::getUser() !== null;
    }

    public static function isAdmin() {
        $user = self::getUser();
        return $user && $user['role'] === 'admin';
    }

    public static function requireAuth() {
        $user = self::getUser();
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        return $user;
    }

    public static function requireAdmin() {
        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
        }
        return $user;
    }

    private static function setAuthCookie($token, $expires) {
        $secure = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
        setcookie('auth_token', $token, [
            'expires' => $expires,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function loginUser($userId, $role = 'customer') {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }

        $sessionId = session_id();
        if ($sessionId) {
            $db = Database::getInstance();
            $sessionItems = $db->fetchAll(
                "SELECT product_id, quantity FROM cart_items WHERE session_id = ? AND user_id IS NULL",
                [$sessionId], 's'
            );
            foreach ($sessionItems as $item) {
                $existing = $db->fetchOne(
                    "SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?",
                    [$userId, $item['product_id']], 'ii'
                );
                if ($existing) {
                    $db->update(
                        "UPDATE cart_items SET quantity = quantity + ? WHERE id = ?",
                        [$item['quantity'], $existing['id']], 'ii'
                    );
                    $db->update("DELETE FROM cart_items WHERE session_id = ? AND product_id = ? AND user_id IS NULL", [$sessionId, $item['product_id']], 'si');
                } else {
                    $db->update(
                        "UPDATE cart_items SET user_id = ?, session_id = NULL WHERE session_id = ? AND product_id = ?",
                        [$userId, $sessionId, $item['product_id']], 'isi'
                    );
                }
            }
        }

        $token = self::generateToken($userId, $role);
        $_SESSION['auth_token'] = $token;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        self::setAuthCookie($token, time() + JWT_EXPIRY);
        return $token;
    }

    public static function logout() {
        unset($_SESSION['auth_token'], $_SESSION['user_id'], $_SESSION['user_role']);
        self::setAuthCookie('', time() - 3600);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}
