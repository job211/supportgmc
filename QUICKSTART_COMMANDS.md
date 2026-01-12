# ⚡ QUICK START - Phase 5 Commands

**Copier/coller les commandes pour exécuter rapidement**

---

## 🚀 Démarrage Rapide

### 1. Démarrer le Serveur PHP
```bash
cd /home/lidruf/supportgmc/public
php -d opcache.enable=0 -S localhost:8080 > /tmp/php_server.log 2>&1 &
```

### 2. Accéder à l'Application
```bash
# Login
http://localhost:8080/login.php

# Admin Dashboard (après login avec compte admin)
http://localhost:8080/admin_audit_logs.php

# Dashboard principal
http://localhost:8080/index.php
```

---

## 🗄️ Exécuter les Indexes

```bash
# Option 1: Fichier SQL (RECOMMANDÉE)
mysql -u root -p ticket_app < /home/lidruf/supportgmc/scripts/add_indexes.sql

# Option 2: Commande directe
mysql -u root -p ticket_app << EOF
CREATE INDEX idx_tickets_status_created ON tickets(status, created_at);
CREATE INDEX idx_tickets_created_by_status ON tickets(created_by_id, status);
CREATE INDEX idx_tickets_assigned_to ON tickets(assigned_to);
CREATE INDEX idx_tickets_type_id ON tickets(type_id);
CREATE INDEX idx_comments_ticket_id ON comments(ticket_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_ticket_id ON tasks(ticket_id);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_specifications_created_by ON specifications(created_by);
EOF
```

---

## 🧪 Tests Rapides

### Test Login Page
```bash
curl -I http://localhost:8080/login.php
# Résultat attendu: HTTP/1.1 200 OK
```

### Test Rate Limiting (5 tentatives)
```bash
for i in {1..5}; do
  echo "Tentative $i..."
  curl -s -X POST http://localhost:8080/login.php \
    -d "username=admin&password=wrong&csrf_token=test" \
    | grep -o "Le nom d'utilisateur\|Trop de tentatives" | head -1
  sleep 1
done
```

### Vérifier Audit Logs
```bash
mysql -u root -p << EOF
SELECT id, username, action, status, created_at 
FROM ticket_app.audit_logs 
ORDER BY id DESC 
LIMIT 10;
EOF
```

### Vérifier Indexes Créés
```bash
mysql -u root -p << EOF
SHOW INDEX FROM ticket_app.tickets;
SHOW INDEX FROM ticket_app.tasks;
SHOW INDEX FROM ticket_app.comments;
EOF
```

---

## 📊 Performance Check

### Avant Indexes
```bash
mysql -u root -p << EOF
EXPLAIN SELECT * FROM tickets WHERE status='open' ORDER BY created_at DESC;
-- Devrait afficher "Full Table Scan" (lent)
EOF
```

### Après Indexes
```bash
mysql -u root -p << EOF
EXPLAIN SELECT * FROM tickets WHERE status='open' ORDER BY created_at DESC;
-- Devrait afficher "idx_tickets_status_created" dans colonne "key"
EOF
```

---

## 🔍 Debug & Logs

### Afficher les 20 derniers logs du serveur
```bash
tail -20 /tmp/php_server.log
```

### Suivre les logs en temps réel
```bash
tail -f /tmp/php_server.log
```

### Rechercher les erreurs
```bash
grep -i "error\|fatal\|exception" /tmp/php_server.log
```

### Vérifier la syntaxe PHP
```bash
php -l /home/lidruf/supportgmc/includes/security_audit_log.php
php -l /home/lidruf/supportgmc/includes/security_rate_limit.php
php -l /home/lidruf/supportgmc/public/admin_audit_logs.php
```

---

## 🔐 Tests de Sécurité

### Test CSRF Protection
```bash
curl -X POST http://localhost:8080/login.php \
  -d "username=admin&password=pass&csrf_token=invalid" \
  2>&1 | grep -i "csrf"
# Résultat attendu: "La vérification de sécurité a échoué"
```

### Test Rate Limiting Lockout
```bash
# Après 5 tentatives échouées, tenter avec bon password
curl -X POST http://localhost:8080/login.php \
  -d "username=admin&password=correct&csrf_token=token"
# Résultat attendu: "Trop de tentatives échouées"
```

### Vérifier IP Address Logging
```bash
mysql -u root -p << EOF
SELECT username, ip_address, action, status FROM ticket_app.audit_logs 
WHERE username='admin' LIMIT 5;
EOF
```

---

## 📈 Metrics & Monitoring

### Nombre Total de Logs
```bash
mysql -u root -p << EOF
SELECT COUNT(*) as total_logs FROM ticket_app.audit_logs;
EOF
```

### Logs par Statut
```bash
mysql -u root -p << EOF
SELECT status, COUNT(*) as count 
FROM ticket_app.audit_logs 
GROUP BY status;
EOF
```

### Logs par Action
```bash
mysql -u root -p << EOF
SELECT action, COUNT(*) as count 
FROM ticket_app.audit_logs 
GROUP BY action 
ORDER BY count DESC;
EOF
```

### Rate Limit Attempts
```bash
mysql -u root -p << EOF
SELECT username, ip_address, attempt_count, last_attempt 
FROM ticket_app.login_attempts 
WHERE attempt_count > 0;
EOF
```

---

## 🔧 Maintenance

### Arrêter le Serveur PHP
```bash
killall php
# ou
pkill -f "php -S"
```

### Redémarrer le Serveur
```bash
killall php 2>/dev/null
sleep 2
cd /home/lidruf/supportgmc/public
php -d opcache.enable=0 -S localhost:8080 > /tmp/php_server.log 2>&1 &
```

### Effacer les Vieux Logs d'Audit (> 30 jours)
```bash
mysql -u root -p << EOF
DELETE FROM ticket_app.audit_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
EOF
```

### Analyser la Performance Indexes
```bash
mysql -u root -p << EOF
ANALYZE TABLE ticket_app.tickets;
ANALYZE TABLE ticket_app.tasks;
ANALYZE TABLE ticket_app.comments;
EOF
```

---

## 📚 Documentations Complètes

```bash
# Lire les guides détaillés
cat /home/lidruf/supportgmc/PHASE5_EXECUTIVE_SUMMARY.md
cat /home/lidruf/supportgmc/OPTIMIZATION_PHASE5_COMPLETE.md
cat /home/lidruf/supportgmc/INDEXES_EXECUTION_GUIDE.md
cat /home/lidruf/supportgmc/TESTING_GUIDE_PHASE5.md
```

---

## 🎯 Checklist Final

```bash
# 1. Vérifier serveur démarré
curl -I http://localhost:8080/login.php | grep "200 OK"

# 2. Exécuter les indexes
mysql -u root -p ticket_app < /home/lidruf/supportgmc/scripts/add_indexes.sql

# 3. Vérifier indexes créés
mysql -u root -p -e "SHOW INDEX FROM ticket_app.tickets;" | grep idx_

# 4. Tester login
# Manuellement ou avec curl

# 5. Vérifier audit logs
mysql -u root -p -e "SELECT COUNT(*) FROM ticket_app.audit_logs;"

# 6. Tester rate limiting
# 5 tentatives invalides

# 7. Vérifier admin dashboard
# Aller à http://localhost:8080/admin_audit_logs.php

# 8. Vérifier performance
time curl -s http://localhost:8080/login.php > /dev/null
# Résultat attendu: < 1 second
```

---

## 🆘 Quick Troubleshooting

```bash
# Erreur HTTP 500?
tail -50 /tmp/php_server.log | grep -A 5 "Fatal"

# OPcache problème?
php -d opcache.enable=0 -S localhost:8080 > /tmp/php_server.log 2>&1 &

# MySQL pas connecté?
mysql -u root -p -e "SELECT 1;"

# Indexes pas créés?
mysql -u root -p -e "SHOW INDEX FROM ticket_app.tickets;"

# Syntax erreur?
php -l /home/lidruf/supportgmc/includes/security_audit_log.php
```

---

*Garder ce fichier à portée de main pour les commandes rapides! ⚡*
