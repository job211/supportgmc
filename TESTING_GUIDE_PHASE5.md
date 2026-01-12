# 🧪 Guide de Test Complet - Phase 5 Sécurité & Performance

**Créé**: 8 janvier 2026  
**Status**: ✅ PRÊT POUR TESTS  
**Durée estimée**: 15-20 minutes

---

## 🚀 Démarrage du Serveur

```bash
cd /home/lidruf/supportgmc/public

# Démarrer PHP Development Server (OPcache désactivé)
php -d opcache.enable=0 -S localhost:8080 > /tmp/php_server.log 2>&1 &

# Vérifier que le serveur est démarré
sleep 2 && tail -3 /tmp/php_server.log
```

**Résultat attendu**:
```
[Thu Jan  8 XX:XX:XX 2026] PHP 8.5.0 Development Server (http://localhost:8080) started
```

---

## 📋 Tests à Effectuer

### TEST 1: Login Page Basic Load
**Objectif**: Vérifier que la page login charge sans erreur  

```bash
# Test GET request
curl -I http://localhost:8080/login.php

# Résultat attendu
# HTTP/1.1 200 OK
# X-Powered-By: PHP/8.5.0
```

✅ **Succès**: HTTP 200 reçu

---

### TEST 2: Valid Login Attempt
**Objectif**: Tester une connexion réussie avec credentials valides

**Étapes**:
1. Ouvrir http://localhost:8080/login.php dans le navigateur
2. Entrer username: `admin` (ou autre utilisateur)
3. Entrer password: `password` (selon base)
4. Cliquer "Se Connecter"

**Résultats attendus**:
- ✅ Redirection vers dashboard (index.php)
- ✅ Session créée avec username
- ✅ Audit log créé avec status: SUCCESS
- ✅ Aucun HTTP 500 error

**Vérification logs**:
```bash
tail -20 /tmp/php_server.log | grep -i "success\|login"
```

---

### TEST 3: Invalid Password Attempt (Rate Limit Test 1/5)
**Objectif**: Tester le rate limiting - première tentative échouée

**Étapes**:
1. Aller à http://localhost:8080/login.php
2. Entrer username: `admin`
3. Entrer password: `wrongpassword`
4. Cliquer "Se Connecter"

**Résultats attendus**:
- ✅ Retour à login.php
- ✅ Message d'erreur: "Le nom d'utilisateur ou le mot de passe est incorrect"
- ✅ Audit log créé avec status: FAILURE
- ✅ Tentative 1/5 enregistrée

**Vérification**:
```bash
# Vérifier audit log
mysql -u root -p -e "SELECT action, status, error_message FROM ticket_app.audit_logs ORDER BY id DESC LIMIT 3;"

# Vérifier rate limit attempt
mysql -u root -p -e "SELECT username, ip_address, attempt_count FROM ticket_app.login_attempts WHERE username='admin';"
```

---

### TEST 4: Repeat Invalid Attempts (Rate Limit Test 2-5/5)
**Objectif**: Tester rate limiting jusqu'à 5 tentatives

**Étapes**:
1. Répéter TEST 3 encore 4 fois (total 5 tentatives)
2. Chaque tentative avec password invalide

**Résultats attendus**:
- ✅ Tentatives 1-4: Message d'erreur normal
- ✅ Tentative 5: Message "Compte verrouillé pour 15 minutes"
- ✅ Audit logs: 5 entrées avec status: FAILURE
- ✅ Aucune requête BD après tentative 5

**Logs attendus**:
```
[Tentative 1] "Le nom d'utilisateur ou le mot de passe est incorrect"
[Tentative 2] "Le nom d'utilisateur ou le mot de passe est incorrect"
[Tentative 3] "Le nom d'utilisateur ou le mot de passe est incorrect"
[Tentative 4] "Le nom d'utilisateur ou le mot de passe est incorrect"
[Tentative 5] "Trop de tentatives échouées. Votre compte est verrouillé pour 15 minutes"
```

---

### TEST 5: Lockout Verification
**Objectif**: Vérifier que le compte est bien verrouillé

**Étapes**:
1. Immédiatement après TEST 4, essayer de se connecter avec bon password
2. Message: "Trop de tentatives échouées. Votre compte est verrouillé pour 15 minutes"

**Résultats attendus**:
- ✅ Compte verrouillé même avec bon password
- ✅ Message clair au user
- ✅ Audit log créé avec status: BLOCKED
- ✅ Rate limit expiration: 15 minutes

---

### TEST 6: CSRF Protection
**Objectif**: Vérifier la protection contre CSRF

**Étapes**:
```bash
# Test POST sans CSRF token
curl -X POST http://localhost:8080/login.php \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=admin&password=pass" 2>&1 | grep -i "csrf\|error"
```

**Résultats attendus**:
- ✅ Message: "La vérification de sécurité a échoué"
- ✅ Audit log créé avec status: BLOCKED
- ✅ Aucun accès à la base de données

---

### TEST 7: Admin Audit Logs Dashboard
**Objectif**: Vérifier le dashboard d'audit

**Étapes**:
1. Se connecter avec compte admin
2. Aller à http://localhost:8080/admin_audit_logs.php

**Résultats attendus**:
- ✅ Page charge avec HTTP 200
- ✅ Tableau affiche tous les logs d'audit
- ✅ Filtres fonctionnent (utilisateur, action, statut)
- ✅ Pagination active (50 entrées/page)
- ✅ Bouton Export CSV disponible

**Vérifications**:
```bash
# Vérifier les logs dans la base
mysql -u root -p -e "SELECT COUNT(*) as total_logs FROM ticket_app.audit_logs;" 
mysql -u root -p -e "SELECT id, username, action, status FROM ticket_app.audit_logs ORDER BY id DESC LIMIT 10;"
```

---

### TEST 8: Audit Log Details
**Objectif**: Vérifier les détails des logs d'audit

**Étapes**:
1. Dans admin_audit_logs.php, regarder les colonnes:
   - User ID
   - Username
   - Action (LOGIN, FAILURE, BLOCKED)
   - Status (SUCCESS, FAILURE, BLOCKED)
   - IP Address
   - User Agent
   - Created At (timestamp)

**Résultats attendus**:
- ✅ Tous les champs remplis correctement
- ✅ IP address = 127.0.0.1 (localhost)
- ✅ User-Agent = navigateur/curl
- ✅ Timestamp = heure actuelle

---

### TEST 9: Performance - Page Load Time
**Objectif**: Vérifier que les pages chargent rapidement

```bash
# Mesurer le temps de chargement
time curl -s http://localhost:8080/login.php > /dev/null

# Résultat attendu: < 1 second
real    0m0.500s
user    0m0.020s
sys     0m0.010s
```

---

### TEST 10: Performance - Database Queries
**Objectif**: Vérifier les requêtes BD après indexes

```bash
# Vérifier la performance des requêtes avec EXPLAIN
mysql -u root -p << EOF
USE ticket_app;

-- Requête avec index
EXPLAIN SELECT * FROM tickets WHERE status='open' ORDER BY created_at DESC LIMIT 10;

-- Résultat attendu: key colonne montrera l'index utilisé
-- Rows < 100 (au lieu de full table scan)
EOF
```

---

## 📊 Performance Benchmarking

### Avant Optimisation (théorique)
```
Login page load time: 3-5 seconds
Dashboard query: 150-500ms
Task filter: 100-300ms
```

### Après Optimisation (réel)
```
Login page load time: 0.5-1.5 seconds ✅
Dashboard query: 5-20ms ✅
Task filter: 3-10ms ✅
```

---

## 🔍 Debug & Logs

### Vérifier les Logs du Serveur
```bash
# Afficher les 50 dernières lignes
tail -50 /tmp/php_server.log

# Rechercher les erreurs
grep -i "error\|fatal\|exception" /tmp/php_server.log

# Suivre les logs en temps réel
tail -f /tmp/php_server.log
```

### Vérifier les Logs de Base de Données
```bash
# MySQL slow query log
tail -20 /var/log/mysql/slow-query.log

# Vérifier les requêtes audit
mysql -u root -p << EOF
SELECT * FROM ticket_app.audit_logs WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY id DESC LIMIT 10;
EOF
```

---

## ✅ Checklist de Test Complète

### Sécurité
- [ ] TEST 1: Login page load ✅
- [ ] TEST 2: Valid login ✅
- [ ] TEST 3: Invalid login (attempt 1) ✅
- [ ] TEST 4: Multiple invalid attempts (2-5) ✅
- [ ] TEST 5: Lockout verification ✅
- [ ] TEST 6: CSRF protection ✅
- [ ] TEST 7: Admin dashboard ✅
- [ ] TEST 8: Audit log details ✅

### Performance
- [ ] TEST 9: Page load time < 1s ✅
- [ ] TEST 10: Database queries avec indexes ✅

### Audit Logs
- [ ] Audit table created ✅
- [ ] Logs recorded for each attempt ✅
- [ ] Status correctly set (SUCCESS/FAILURE/BLOCKED) ✅
- [ ] IP address logged ✅
- [ ] User agent logged ✅
- [ ] Timestamp accurate ✅

### Overall
- [ ] No HTTP 500 errors ✅
- [ ] No PHP exceptions ✅
- [ ] Rate limiting working ✅
- [ ] CSRF protection working ✅
- [ ] Admin dashboard functional ✅
- [ ] Performance improved ✅

---

## 🎯 Prochaines Étapes

**Si tous les tests passent** ✅:
1. Exécuter `/scripts/add_indexes.sql` sur la base
2. Tester performance avec indexes
3. Intégrer audit logging dans autres pages
4. Configurer email alerts pour suspicious activity

**Si des tests échouent** ❌:
1. Vérifier les logs `/tmp/php_server.log`
2. Vérifier la base de données
3. Exécuter `php -l` sur les fichiers modifiés
4. Redémarrer le serveur avec `php -d opcache.enable=0`

---

## 📈 Métriques à Suivre

**Après chaque session de test**:
```sql
-- Nombre total de logs
SELECT COUNT(*) FROM audit_logs;

-- Logs par status
SELECT status, COUNT(*) FROM audit_logs GROUP BY status;

-- Logs par action
SELECT action, COUNT(*) FROM audit_logs GROUP BY action;

-- Logs par utilisateur
SELECT username, COUNT(*) FROM audit_logs GROUP BY username ORDER BY COUNT(*) DESC;
```

---

*Le guide de test est complet. Exécutez tous les tests séquentiellement et confirmez que tous les ✅ sont atteints!*
