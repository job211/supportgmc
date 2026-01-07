<?php
/**
 * Fichier contenant les fonctions liées à la récupération des données des tickets.
 */

/**
 * Récupère les détails complets d'un ticket.
 *
 * @param mysqli $link La connexion à la base de données.
 * @param int $ticket_id L'ID du ticket.
 * @return array|null Les données du ticket ou null si non trouvé.
 */
function get_ticket_details($link, $ticket_id) {
    $sql = "SELECT t.*, u.username as created_by_name, s.name as service_name, a.username as assignee_name, tt.name as type_name 
            FROM tickets t 
            JOIN users u ON t.created_by_id = u.id 
            JOIN services s ON t.service_id = s.id 
            LEFT JOIN users a ON t.assigned_to_id = a.id 
            LEFT JOIN ticket_types tt ON t.type_id = tt.id 
            WHERE t.id = ?";
    
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ticket = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $ticket ?: null;
    }
    return null;
}

/**
 * Récupère les commentaires d'un ticket.
 *
 * @param mysqli $link La connexion à la base de données.
 * @param int $ticket_id L'ID du ticket.
 * @return array La liste des commentaires.
 */
function get_ticket_comments($link, $ticket_id) {
    $comments = [];
    $sql = "SELECT c.comment, c.created_at, c.user_id, u.username, u.role, s.name as service_name 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            LEFT JOIN services s ON u.service_id = s.id 
            WHERE c.ticket_id = ? 
            ORDER BY c.created_at ASC";
            
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)){
            $comments[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $comments;
}

/**
 * Récupère les pièces jointes d'un ticket.
 *
 * @param mysqli $link La connexion à la base de données.
 * @param int $ticket_id L'ID du ticket.
 * @return array La liste des pièces jointes.
 */
function get_ticket_attachments($link, $ticket_id) {
    $attachments = [];
    $sql = "SELECT id, file_name, file_path, file_size FROM ticket_attachments WHERE ticket_id = ?";
    
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)){
            $attachments[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $attachments;
}

/**
 * Récupère les tâches liées à un ticket.
 *
 * @param mysqli $link La connexion à la base de données.
 * @param int $ticket_id L'ID du ticket.
 * @return array La liste des tâches.
 */
function get_related_tasks($link, $ticket_id) {
    $tasks = [];
    $sql = "SELECT t.*, u.username as assigned_name 
            FROM tasks t 
            JOIN users u ON t.assigned_to = u.id 
            WHERE t.ticket_id = ? 
            ORDER BY t.due_date ASC, t.created_at DESC";
            
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, 'i', $ticket_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)) {
            $tasks[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
    return $tasks;
}

/**
 * Récupère les utilisateurs assignables (agents et admins).
 *
 * @param mysqli $link La connexion à la base de données.
 * @return array La liste des utilisateurs assignables.
 */
function get_assignable_users($link) {
    $users = [];
    $sql = "SELECT id, username, service_id, country_id FROM users WHERE role IN ('agent', 'admin') ORDER BY username";
    
    if($result = mysqli_query($link, $sql)){
        while($row = mysqli_fetch_assoc($result)){
            $users[] = $row;
        }
    }
    return $users;
}
?>
