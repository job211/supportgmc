-- ========================================
-- AJOUT DES INDEXES CRITIQUES
-- Performance Optimization - 8 Janvier 2026
-- ========================================

USE palladvticket;

-- Suppression des anciens indexes avant recréation
ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_status_created;
ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_created_by_status;
ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_assigned_to;
ALTER TABLE tickets DROP INDEX IF EXISTS idx_tickets_type_id;
ALTER TABLE comments DROP INDEX IF EXISTS idx_comments_ticket_id;
ALTER TABLE tasks DROP INDEX IF EXISTS idx_tasks_assigned_to;
ALTER TABLE tasks DROP INDEX IF EXISTS idx_tasks_status;
ALTER TABLE tasks DROP INDEX IF EXISTS idx_tasks_ticket_id;
ALTER TABLE users DROP INDEX IF EXISTS idx_users_username;
ALTER TABLE specifications DROP INDEX IF EXISTS idx_specifications_created_by;

-- Création des nouveaux indexes
-- Indexes pour la table tickets
CREATE INDEX idx_tickets_status_created ON tickets(status, created_at);
CREATE INDEX idx_tickets_created_by_status ON tickets(created_by_id, status);
CREATE INDEX idx_tickets_assigned_to ON tickets(assigned_to);
CREATE INDEX idx_tickets_type_id ON tickets(type_id);

-- Indexes pour la table comments
CREATE INDEX idx_comments_ticket_id ON comments(ticket_id);

-- Indexes pour la table tasks
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_status ON tasks(status);
CREATE INDEX idx_tasks_ticket_id ON tasks(ticket_id);

-- Indexes pour la table users
CREATE INDEX idx_users_username ON users(username);

-- Indexes pour la table specifications
CREATE INDEX idx_specifications_created_by ON specifications(created_by);

-- ========================================
-- VÉRIFICATION DES INDEXES CRÉÉS
-- ========================================

-- Pour vérifier que les indexes ont été créés avec succès:
-- SHOW INDEX FROM tickets;
-- SHOW INDEX FROM comments;
-- SHOW INDEX FROM tasks;
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM specifications;
