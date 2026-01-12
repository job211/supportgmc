<?php
/**
 * Rate Limiting & Security Functions
 * Protège contre les attaques par force brute
 * Version: 1.0 | Date: 8 Janvier 2026
 */

/**
 * Vérifier et enregistrer les tentatives de connexion
 * @param string $username Nom d'utilisateur
 * @param string $ip Adresse IP du client
 * @return array ['allowed' => bool, 'message' => string, 'attempts' => int, 'wait_until' => string]
 */
function check_login_attempts($username, $ip) {
    global $link;
    
    // Configuration du rate limiting
    $max_attempts = 5;           // Maximum de tentatives
    $lockout_duration = 900;     // 15 minutes en secondes
    $check_window = 900;         // Fenêtre de vérification (15 minutes)
    
    // Créer la table si elle n'existe pas
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        success TINYINT(1) DEFAULT 0,
        INDEX idx_username_time (username, attempt_time),
        INDEX idx_ip_time (ip_address, attempt_time)
    ) ENGINE=InnoDB;
    ";
    
    mysqli_query($link, $create_table_sql);
    
    // Nettoyer les anciennes tentatives
    $cleanup_sql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    mysqli_query($link, $cleanup_sql);
    
    // Compter les tentatives échouées récentes
    $check_sql = "
    SELECT COUNT(*) as failed_count, MAX(attempt_time) as last_attempt
    FROM login_attempts
    WHERE username = ? AND ip_address = ? AND success = 0
    AND attempt_time > DATE_SUB(NOW(), INTERVAL $check_window SECOND)
    ";
    
    $stmt = mysqli_prepare($link, $check_sql);
    mysqli_stmt_bind_param($stmt, "ss", $username, $ip);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    $failed_count = (int)$row['failed_count'];
    $last_attempt = $row['last_attempt'];
    
    // Vérifier si l'utilisateur est verrouillé
    if ($failed_count >= $max_attempts) {
        $lockout_until = strtotime($last_attempt) + $lockout_duration;
        $current_time = time();
        
        if ($current_time < $lockout_until) {
            $wait_minutes = ceil(($lockout_until - $current_time) / 60);
            return [
                'allowed' => false,
                'message' => "Compte temporairement verrouillé. Veuillez réessayer dans $wait_minutes minute(s).",
                'attempts' => $failed_count,
                'wait_until' => date('Y-m-d H:i:s', $lockout_until)
            ];
        } else {
            // Réinitialiser les tentatives après le délai de verrouillage
            $reset_sql = "DELETE FROM login_attempts WHERE username = ? AND ip_address = ? AND success = 0";
            $reset_stmt = mysqli_prepare($link, $reset_sql);
            mysqli_stmt_bind_param($reset_stmt, "ss", $username, $ip);
            mysqli_stmt_execute($reset_stmt);
        }
    }
    
    return [
        'allowed' => true,
        'message' => 'Connexion autorisée',
        'attempts' => $failed_count,
        'wait_until' => null
    ];
}

/**
 * Enregistrer une tentative de connexion
 * @param string $username Nom d'utilisateur
 * @param string $ip Adresse IP
 * @param bool $success Succès ou non
 */
function record_login_attempt($username, $ip, $success) {
    global $link;
    
    $sql = "INSERT INTO login_attempts (username, ip_address, success, attempt_time) 
            VALUES (?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($link, $sql);
    $success_int = $success ? 1 : 0;
    mysqli_stmt_bind_param($stmt, "ssi", $username, $ip, $success_int);
    mysqli_stmt_execute($stmt);
}

/**
 * Obtenir l'adresse IP réelle du client (supportant proxies)
 * @return string Adresse IP
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP']; // Cloudflare
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
        $ips = explode(',', $_SERVER['HTTP_FORWARDED']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}
?>
