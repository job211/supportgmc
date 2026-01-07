<?php
// Démarrer la session de manière sécurisée
if (session_status() === PHP_SESSION_NONE) {
    // Définir des paramètres de cookie de session stricts pour la sécurité et la cohérence
    $cookieParams = session_get_cookie_params();
    if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
        session_set_cookie_params([
            'lifetime' => $cookieParams['lifetime'],
            'path'     => '/',
            'domain'   => $cookieParams['domain'],
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(
            $cookieParams['lifetime'],
            '/',
            $cookieParams['domain'],
            isset($_SERVER['HTTPS']),
            true
        );
    }
    session_start();
}

// Générer un jeton CSRF s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Vérifie le jeton CSRF soumis avec celui stocké en session.
 * @param string $token Le jeton provenant du formulaire.
 * @return bool True si le jeton est valide, false sinon.
 */
function verify_csrf_token($token) {
    // Utilise hash_equals pour une comparaison sécurisée (évite les attaques temporelles)
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    // En cas d'échec, vous pourriez logger la tentative pour surveiller les activités suspectes.
    error_log('CSRF token validation failed for IP: ' . $_SERVER['REMOTE_ADDR']);
    return false;
}
?>
