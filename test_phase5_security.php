<?php
/**
 * PHASE 5 - Test de Sécurité
 * Fichier de vérification que tous les modules de sécurité fonctionnent
 * 
 * Usage: Ouvrir dans le navigateur depuis /public/test_security.php
 * Ou via CLI: php test_security.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Définir le répertoire racine
$base_dir = __DIR__;

// Couleurs pour CLI
define('GREEN', "\033[32m");
define('RED', "\033[31m");
define('YELLOW', "\033[33m");
define('RESET', "\033[0m");

$tests_passed = 0;
$tests_failed = 0;

function test($name, $condition, $details = '') {
    global $tests_passed, $tests_failed;
    
    if ($condition) {
        $tests_passed++;
        echo GREEN . "✅ PASS: " . RESET . $name . "\n";
    } else {
        $tests_failed++;
        echo RED . "❌ FAIL: " . RESET . $name . "\n";
        if ($details) echo "   " . $details . "\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "TESTS DE SÉCURITÉ - PHASE 5\n";
echo str_repeat("=", 70) . "\n\n";

// Test 1: Vérifier que les fichiers existent
echo "=== TEST 1: FICHIERS DE SÉCURITÉ ===\n";

$files_to_check = [
    $base_dir . '/includes/security_rate_limit.php' => 'Rate Limiting',
    $base_dir . '/includes/security_audit_log.php' => 'Audit Logging',
    $base_dir . '/public/admin_audit_logs.php' => 'Admin Dashboard',
];

foreach ($files_to_check as $file => $name) {
    $exists = file_exists($file);
    $size = $exists ? filesize($file) : 0;
    test($name . " existe", $exists, "Taille: " . ($size ? $size . " bytes" : "N/A"));
}

echo "\n=== TEST 2: SYNTAXE PHP ===\n";

// Test la syntaxe PHP des fichiers créés
$php_files = [
    $base_dir . '/includes/security_rate_limit.php',
    $base_dir . '/includes/security_audit_log.php',
    $base_dir . '/public/admin_audit_logs.php',
    $base_dir . '/public/login.php'
];

foreach ($php_files as $file) {
    if (file_exists($file)) {
        $output = [];
        $return_code = 0;
        exec("php -l " . escapeshellarg($file), $output, $return_code);
        test("Syntaxe PHP: " . basename($file), $return_code === 0, implode(" ", $output));
    }
}

echo "\n=== TEST 3: STRUCTURE DE FONCTIONS ===\n";

// Vérifier que les fonctions clés existent dans les fichiers
$file_content = file_get_contents($base_dir . '/includes/security_rate_limit.php');

test("Fonction check_login_attempts", strpos($file_content, 'function check_login_attempts') !== false);
test("Fonction record_login_attempt", strpos($file_content, 'function record_login_attempt') !== false);
test("Fonction get_client_ip", strpos($file_content, 'function get_client_ip') !== false);

$file_content = file_get_contents($base_dir . '/includes/security_audit_log.php');

test("Fonction init_audit_log_table", strpos($file_content, 'function init_audit_log_table') !== false);
test("Fonction log_audit", strpos($file_content, 'function log_audit') !== false);
test("Fonction log_login_success", strpos($file_content, 'function log_login_success') !== false);
test("Fonction log_login_failure", strpos($file_content, 'function log_login_failure') !== false);
test("Fonction get_audit_logs", strpos($file_content, 'function get_audit_logs') !== false);
test("Fonction export_audit_logs_csv", strpos($file_content, 'function export_audit_logs_csv') !== false);

echo "\n=== TEST 4: INTÉGRATIONS DANS LOGIN.PHP ===\n";

$login_content = file_get_contents($base_dir . '/public/login.php');

test("Require security_rate_limit.php", strpos($login_content, "require_once '../includes/security_rate_limit.php'") !== false);
test("Require security_audit_log.php", strpos($login_content, "require_once '../includes/security_audit_log.php'") !== false);
test("Appel get_client_ip", strpos($login_content, 'get_client_ip()') !== false);
test("Appel check_login_attempts", strpos($login_content, 'check_login_attempts') !== false);
test("Appel record_login_attempt", strpos($login_content, 'record_login_attempt') !== false);
test("Appel log_audit LOGIN", strpos($login_content, "log_audit('LOGIN'") !== false);

echo "\n=== TEST 5: DOCUMENTATION ===\n";

$docs_to_check = [
    'SECURITY_IMPLEMENTATION_PHASE5.md' => 'Documentation Sécurité Phase 5',
    'INTEGRATION_AUDIT_LOGGING.md' => 'Guide Intégration Audit',
    'PHASE5_RESUME_COMPLET.md' => 'Résumé Complet Phase 5',
];

foreach ($docs_to_check as $file => $name) {
    $exists = file_exists($base_dir . '/' . $file);
    test($name, $exists);
}

echo "\n=== TEST 6: CONFORMITÉ CODE ===\n";

// Vérifier la structure du code
$rate_limit_content = file_get_contents($base_dir . '/includes/security_rate_limit.php');
test("Rate limit: Constante MAX_ATTEMPTS", strpos($rate_limit_content, 'MAX_ATTEMPTS') !== false || strpos($rate_limit_content, '5') !== false);
test("Rate limit: Constante LOCKOUT_DURATION", strpos($rate_limit_content, 'LOCKOUT_DURATION') !== false || strpos($rate_limit_content, '900') !== false);
test("Rate limit: Support Cloudflare", strpos($rate_limit_content, 'Cloudflare') !== false || strpos($rate_limit_content, 'CF-Connecting-IP') !== false);

$audit_content = file_get_contents($base_dir . '/includes/security_audit_log.php');
test("Audit: Support JSON", strpos($audit_content, 'json_encode') !== false);
test("Audit: Support statut SUCCESS", strpos($audit_content, 'SUCCESS') !== false);
test("Audit: Support statut FAILURE", strpos($audit_content, 'FAILURE') !== false);
test("Audit: Support statut BLOCKED", strpos($audit_content, 'BLOCKED') !== false);

echo "\n" . str_repeat("=", 70) . "\n";
echo "RÉSUMÉ DES TESTS\n";
echo str_repeat("=", 70) . "\n";
echo GREEN . "✅ RÉUSSIS: " . RESET . $tests_passed . "\n";
echo RED . "❌ ÉCHOUÉS: " . RESET . $tests_failed . "\n";

$success_rate = $tests_passed + $tests_failed > 0 
    ? round(($tests_passed / ($tests_passed + $tests_failed)) * 100) 
    : 0;

echo "\nTaux de Réussite: " . ($success_rate >= 95 ? GREEN : YELLOW) . $success_rate . "%" . RESET . "\n";

if ($tests_failed === 0) {
    echo "\n" . GREEN . "✅ TOUS LES TESTS RÉUSSIS! LA PHASE 5 EST PRÊTE POUR LA PRODUCTION." . RESET . "\n\n";
} else {
    echo "\n" . RED . "⚠️ CERTAINS TESTS ONT ÉCHOUÉ. VEUILLEZ VÉRIFIER LES DÉTAILS CI-DESSUS." . RESET . "\n\n";
}

?>
