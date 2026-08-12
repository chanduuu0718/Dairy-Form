<?php
/**
 * Authentication Helper
 * PM Dairy Farm
 */

class Auth {

    // Hash password
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    // Verify password
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    // Generate JWT Token
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

    // Verify JWT Token
    public static function verifyToken($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        list($base64Header, $base64Payload, $base64Signature) = $parts;

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, JWT_SECRET, true);
        $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        if (!hash_equals($expectedSignature, $base64Signature)) return false;

        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);

        if ($payload['exp'] < time()) return false;

        return $payload;
    }

    // Get token from request
    public static function getTokenFromRequest() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        // Check cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        // Check session
        if (isset($_SESSION['auth_token'])) {
            return $_SESSION['auth_token'];
        }
        return null;
    }

    // Get authenticated user
    public static function getUser() {
        $token = self::getTokenFromRequest();
        if (!$token) return null;

        $payload = self::verifyToken($token);
        if (!$payload) return null;

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT id, name, phone, email, role, avatar, is_verified, created_at FROM users WHERE id = ?", [$payload['user_id']], 'i');
        return $user;
    }

    // Check if logged in
    public static function isLoggedIn() {
        return self::getUser() !== null;
    }

    // Check if admin
    public static function isAdmin() {
        $user = self::getUser();
        return $user && $user['role'] === 'admin';
    }

    // Require authentication (for API)
    public static function requireAuth() {
        $user = self::getUser();
        if (!$user) {
            jsonResponse(['success' => false, 'message' => 'Authentication required'], 401);
        }
        return $user;
    }

    // Require admin (for API)
    public static function requireAdmin() {
        $user = self::requireAuth();
        if ($user['role'] !== 'admin') {
            jsonResponse(['success' => false, 'message' => 'Admin access required'], 403);
        }
        return $user;
    }

    // Login user (set session & cookie)
    public static function loginUser($userId, $role = 'customer') {
        // Merge session-based cart items into user's cart
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
                } else {
                    $db->update(
                        "UPDATE cart_items SET user_id = ?, session_id = NULL WHERE session_id = ? AND product_id = ?",
                        [$userId, $sessionId, $item['product_id']], 'isi'
                    );
                }
            }
            // Clean up remaining session cart items
            $db->update("DELETE FROM cart_items WHERE session_id = ? AND user_id IS NULL", [$sessionId], 's');
        }

        $token = self::generateToken($userId, $role);
        $_SESSION['auth_token'] = $token;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        setcookie('auth_token', $token, time() + JWT_EXPIRY, '/', '', false, true);
        return $token;
    }

    // Logout user
    public static function logout() {
        unset($_SESSION['auth_token'], $_SESSION['user_id'], $_SESSION['user_role']);
        setcookie('auth_token', '', time() - 3600, '/', '', false, true);
        session_destroy();
    }

    // Get current user ID from session
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    // Get current user role from session
    public static function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}
