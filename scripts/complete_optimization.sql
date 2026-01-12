-- ========================================
-- OPTIMISATION COMPLÈTE DE LA BASE DE DONNÉES
-- Performance Optimization - 11 Janvier 2026
-- ========================================

USE palladvticket;

-- ========================================
-- 1. ANALYSE ET OPTIMISATION DES TABLES
-- ========================================

-- Analyse toutes les tables
ANALYZE TABLE tickets;
ANALYZE TABLE comments;
ANALYZE TABLE tasks;
ANALYZE TABLE users;
ANALYZE TABLE specifications;
ANALYZE TABLE ticket_types;
ANALYZE TABLE services;
ANALYZE TABLE audit_logs;

-- Optimise toutes les tables
OPTIMIZE TABLE tickets;
OPTIMIZE TABLE comments;
OPTIMIZE TABLE tasks;
OPTIMIZE TABLE users;
OPTIMIZE TABLE specifications;
OPTIMIZE TABLE ticket_types;
OPTIMIZE TABLE services;
OPTIMIZE TABLE audit_logs;

-- ========================================
-- 2. VÉRIFICATION DE L'INTÉGRITÉ DES TABLES
-- ========================================

CHECK TABLE tickets;
CHECK TABLE comments;
CHECK TABLE tasks;
CHECK TABLE users;
CHECK TABLE specifications;

-- ========================================
-- 3. STATISTIQUES DES INDEXES
-- ========================================

-- Pour vérifier les performances des requêtes:
-- EXPLAIN SELECT * FROM tickets WHERE status = 'open' ORDER BY created_at DESC LIMIT 10;
-- EXPLAIN SELECT * FROM tasks WHERE assigned_to = 1 AND status != 'completed';
-- EXPLAIN SELECT * FROM tickets WHERE created_by_id = 5 AND status = 'open';

-- ========================================
-- 4. CONFIGURATION DU CACHE (À VÉRIFIER DANS my.cnf)
-- ========================================

-- Commandes de diagnostic :
-- SHOW VARIABLES LIKE 'query_cache%';
-- SHOW VARIABLES LIKE 'innodb_buffer_pool_size';
-- SHOW VARIABLES LIKE 'key_buffer_size';
-- SHOW VARIABLES LIKE 'max_connections';

-- ========================================
-- 5. VÉRIFICATION DES INDEXES CRÉÉS
-- ========================================

SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX,
    CARDINALITY
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA = 'palladvticket'
AND TABLE_NAME IN ('tickets', 'comments', 'tasks', 'users', 'specifications')
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- ========================================
-- 6. STATISTIQUES D'UTILISATION DES INDEXES
-- ========================================

-- Pour voir les indexes non utilisés (MySQL 5.6+):
-- SELECT * FROM performance_schema.table_io_waits_summary_by_index_usage;

-- ========================================
-- 7. TAILLE DES TABLES ET INDEXES
-- ========================================

SELECT 
    TABLE_NAME,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND((DATA_LENGTH / 1024 / 1024), 2) AS 'Data (MB)',
    ROUND((INDEX_LENGTH / 1024 / 1024), 2) AS 'Index (MB)'
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'palladvticket'
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;

-- ========================================
-- 8. VÉRIFICATION DES CLÉS ÉTRANGÈRES
-- ========================================

SELECT 
    CONSTRAINT_NAME,
    TABLE_NAME,
    COLUMN_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'palladvticket'
AND REFERENCED_TABLE_NAME IS NOT NULL;

-- ========================================
-- 9. NETTOYAGE DES DONNÉES OBSOLÈTES (OPTIONNEL)
-- ========================================

-- Supprime les tickets fermés depuis plus de 90 jours (à adapter selon votre besoin)
-- DELETE FROM tickets WHERE status = 'closed' AND DATEDIFF(NOW(), updated_at) > 90;

-- Supprime les tâches complétées depuis plus de 60 jours
-- DELETE FROM tasks WHERE status = 'completed' AND DATEDIFF(NOW(), updated_at) > 60;

-- Supprime les logs d'audit de plus de 180 jours
-- DELETE FROM audit_logs WHERE DATEDIFF(NOW(), created_at) > 180;

-- ========================================
-- 10. AMÉLIORATION DE LA PERFORMANCE REQUÊTES
-- ========================================

-- Index supplémentaires recommandés (si applicable):
-- CREATE INDEX idx_audit_logs_user_action ON audit_logs(user_id, action, created_at);
-- CREATE INDEX idx_specifications_status ON specifications(status, created_at);
-- CREATE INDEX idx_comments_user_created ON comments(user_id, created_at);

-- ========================================
-- VÉRIFICATION FINALE
-- ========================================

-- Résumé de la configuration (à vérifier après optimisation):
-- SHOW ENGINE INNODB STATUS;
-- SHOW PROCESSLIST;
-- SHOW STATUS LIKE 'Threads%';
