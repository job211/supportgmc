# 🔐 Sécurité Renforcée - SUPPORT GMC

## 🎯 État Actuel de la Sécurité

### ✅ Mesures en Place
- CSRF Token protection (tokens uniques par session)
- Password hashing avec bcrypt (PASSWORD_DEFAULT)
- Session regeneration après connexion
- Prepared statements pour SQL injection prevention
- Input validation basic
- htmlspecialchars() sur les outputs

### ⚠️ Domaines à Améliorer
- Content Security Policy (CSP) headers
- Rate limiting sur les endpoints critiques
- Input sanitization renforcée
- Account lockout après tentatives échouées
- Audit logging
- HTTPS enforcement

## 🔒 Implémentations Recommandées (Phase 2)

### 1. **Content-Security-Policy (CSP) Headers**

#### Configuration Stricte
```php
// config/security.php
header("Content-Security-Policy: " .
  "default-src 'self'; " .
  "script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com; " .
  "style-src 'self' https://fonts.googleapis.com https://cdnjs.cloudflare.com 'unsafe-inline'; " .
  "img-src 'self' data: https:; " .
  "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com; " .
  "connect-src 'self'; " .
  "frame-ancestors 'none'; " .
  "base-uri 'self'; " .
  "form-action 'self'"
);

// Headers de sécurité supplémentaires
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
```

**Impact**: Protection contre XSS, clickjacking, MIME-sniffing

### 2. **Rate Limiting**

#### Implémentation PHP
```php
// includes/rate_limit.php
class RateLimiter {
    private $cache_dir = '/tmp/rate_limit/';
    
    public function checkLimit($identifier, $max_attempts = 5, $window = 300) {
        $file = $this->cache_dir . md5($identifier);
        
        if (!file_exists($this->cache_dir)) {
            mkdir($this->cache_dir, 0777, true);
        }
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if (time() - $data['start'] > $window) {
                // Réinitialiser
                unlink($file);
                return true;
            }
            if ($data['count'] >= $max_attempts) {
                return false; // Trop de tentatives
            }
            $data['count']++;
            file_put_contents($file, json_encode($data));
            return true;
        } else {
            file_put_contents($file, json_encode([
                'count' => 1,
                'start' => time()
            ]));
            return true;
        }
    }
}

// Usage dans login.php
$limiter = new RateLimiter();
if (!$limiter->checkLimit($_POST['username'], 5, 900)) {
    die("Trop de tentatives de connexion. Attendez 15 minutes.");
}
```

#### Configuration Redis (Recommandée)
```php
// includes/rate_limit_redis.php
class RedisRateLimiter {
    private $redis;
    
    public function __construct() {
        $this->redis = new Redis();
        $this->redis->connect('localhost', 6379);
    }
    
    public function checkLimit($identifier, $max_attempts = 5, $window = 300) {
        $key = "rate_limit:" . $identifier;
        $count = $this->redis->incr($key);
        
        if ($count == 1) {
            $this->redis->expire($key, $window);
        }
        
        return $count <= $max_attempts;
    }
}
```

### 3. **Account Lockout Protection**

```php
// includes/account_security.php
class AccountSecurity {
    private $link;
    private $max_attempts = 5;
    private $lockout_duration = 900; // 15 minutes
    
    public function __construct($db_link) {
        $this->link = $db_link;
    }
    
    public function recordFailedAttempt($username) {
        $sql = "UPDATE users 
                SET failed_login_attempts = failed_login_attempts + 1,
                    last_failed_attempt = NOW()
                WHERE username = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    public function isAccountLocked($username) {
        $sql = "SELECT failed_login_attempts, last_failed_attempt 
                FROM users 
                WHERE username = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $attempts, $last_attempt);
        
        if (!mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return false;
        }
        mysqli_stmt_close($stmt);
        
        if ($attempts >= $this->max_attempts) {
            $time_diff = time() - strtotime($last_attempt);
            if ($time_diff < $this->lockout_duration) {
                return true; // Compte verrouillé
            } else {
                // Réinitialiser après timeout
                $this->resetFailedAttempts($username);
                return false;
            }
        }
        return false;
    }
    
    public function resetFailedAttempts($username) {
        $sql = "UPDATE users 
                SET failed_login_attempts = 0
                WHERE username = ?";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
```

### 4. **Enhanced Input Validation**

```php
// includes/validation.php
class InputValidator {
    public static function sanitizeEmail($email) {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        return $email;
    }
    
    public static function sanitizeUsername($username) {
        $username = trim($username);
        // Seulement alphanumériques, tirets et underscores
        if (!preg_match('/^[a-zA-Z0-9_-]{3,32}$/', $username)) {
            return false;
        }
        return $username;
    }
    
    public static function sanitizeText($text, $max_length = 500) {
        $text = trim($text);
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        if (strlen($text) > $max_length) {
            return substr($text, 0, $max_length);
        }
        return $text;
    }
    
    public static function validatePassword($password) {
        // Minimum 8 caractères
        // Au moins une majuscule, une minuscule, un chiffre
        if (strlen($password) < 8) return false;
        if (!preg_match('/[A-Z]/', $password)) return false;
        if (!preg_match('/[a-z]/', $password)) return false;
        if (!preg_match('/[0-9]/', $password)) return false;
        return true;
    }
}
```

### 5. **Audit Logging**

```php
// includes/audit_log.php
class AuditLog {
    private $link;
    
    public function __construct($db_link) {
        $this->link = $db_link;
    }
    
    public function log($user_id, $action, $details = '', $ip = null) {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'];
        
        $sql = "INSERT INTO audit_logs (user_id, action, details, ip_address, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($this->link, $sql);
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $action, $details, $ip);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Audit log failed: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    }
    
    // Cas d'usage courants
    public function logLogin($user_id) {
        $this->log($user_id, 'LOGIN', 'Connexion réussie');
    }
    
    public function logFailedLogin($username) {
        $this->log(null, 'LOGIN_FAILED', 'Tentative échouée pour: ' . $username);
    }
    
    public function logLogout($user_id) {
        $this->log($user_id, 'LOGOUT', 'Déconnexion');
    }
    
    public function logDataModification($user_id, $table, $record_id, $action) {
        $details = "Table: $table, Record: $record_id, Action: $action";
        $this->log($user_id, 'DATA_MODIFICATION', $details);
    }
}

// Schéma de base de données
/*
CREATE TABLE audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50),
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);
*/
```

### 6. **HTTPS Enforcement**

```php
// config/security.php
// Rediriger HTTP vers HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
    exit;
}
```

### 7. **Session Security Hardening**

```php
// includes/session.php
// Configuration améliorée
ini_set('session.cookie_httponly', 1);    // Pas d'accès JavaScript
ini_set('session.cookie_secure', 1);      // HTTPS seulement
ini_set('session.cookie_samesite', 'Strict'); // CSRF protection
ini_set('session.cookie_lifetime', 0);    // Expire avec le navigateur
ini_set('session.gc_maxlifetime', 3600);  // 1 heure
ini_set('session.sid_length', 48);        // Plus long
ini_set('session.use_strict_mode', 1);    // Mode strict

session_start();

// Vérifier la validité de la session
$expected_ua = $_SERVER['HTTP_USER_AGENT'];
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $expected_ua) {
    session_destroy();
    die("Session compromise detected");
}
$_SESSION['user_agent'] = $expected_ua;

// Vérifier l'IP
$expected_ip = $_SERVER['REMOTE_ADDR'];
if (isset($_SESSION['ip']) && $_SESSION['ip'] !== $expected_ip) {
    session_destroy();
    die("Session compromise detected");
}
$_SESSION['ip'] = $expected_ip;
```

## 📋 Checklist de Sécurité

### Avant Production
- [ ] CSP headers configurés et testés
- [ ] Rate limiting activé sur login et AJAX
- [ ] Account lockout implémenté
- [ ] Input validation renforcée partout
- [ ] Audit logging configuré
- [ ] HTTPS enforced
- [ ] Session security hardened
- [ ] Secrets stockés en variables d'environnement
- [ ] Dependencies à jour (`composer update`)
- [ ] Logs régulièrement vérifiés

### En Production
- [ ] WAF (Web Application Firewall) configuré
- [ ] Monitoring des logs d'audit 24/7
- [ ] Alertes sur actions suspectes
- [ ] Backups réguliers et testés
- [ ] Plan de réponse aux incidents
- [ ] Pentesting périodique
- [ ] Compliance audit annuel

## 🔍 Outils de Test

### OWASP ZAP (Gratuit)
```bash
docker pull owasp/zap2docker-stable
docker run -t owasp/zap2docker-stable zap-baseline.py -t http://localhost:8080
```

### Burp Suite Community (Gratuit)
- Télécharger: https://portswigger.net/burp/communitydownload
- Proxy et test des vulnérabilités

### SQLMap (SQL Injection Testing)
```bash
sqlmap -u "http://localhost:8080/login.php" \
  --data="username=test&password=test" \
  --dbs
```

## 📊 Matrice de Sécurité

| Domaine | Actuel | Cible | Effort |
|---------|--------|-------|--------|
| **CSP Headers** | ❌ | ✅ | Faible |
| **Rate Limiting** | ❌ | ✅ | Moyen |
| **Account Lockout** | ❌ | ✅ | Moyen |
| **Audit Logging** | ❌ | ✅ | Moyen |
| **HTTPS** | ✅ | ✅ | Faible |
| **Session Security** | ⚠️ | ✅ | Faible |
| **Input Validation** | ⚠️ | ✅ | Moyen |

---

**Date**: 8 janvier 2026  
**Version**: 1.0  
**Statut**: 📋 Planification Complétée
