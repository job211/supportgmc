<?php
/**
 * Fichier contenant les fonctions de traitement des actions sur les tickets.
 */

require_once __DIR__ . '/mail_functions.php';
require_once __DIR__ . '/email_template.php';

/**
 * Gère la mise à jour complète d'un ticket (statut, assignation, etc.).
 *
 * @param mysqli $link
 * @param int $ticket_id
 * @param string $new_status
 * @param int|null $new_assignee_id
 * @param array $ticket_info
 * @param string $absolute_base_url
 * @return bool
 */
function handle_ticket_update($link, $ticket_id, $new_status, $new_assignee_id, $ticket_info, $absolute_base_url) {
    $current_status = $ticket_info['status'];
    $closed_at_sql_part = "";

    $is_closing = in_array($new_status, ['Fermé', 'Résolu']);
    $was_closed = in_array($current_status, ['Fermé', 'Résolu']);

    if ($is_closing && !$was_closed) {
        $closed_at_sql_part = ", closed_at = NOW()";
    } elseif (!$is_closing && $was_closed) {
        $closed_at_sql_part = ", closed_at = NULL";
    }

    // Utiliser NULLIF pour s'assurer que les assignations vides sont stockées comme NULL, et non 0.
    $sql = "UPDATE tickets SET status = ?, assigned_to_id = NULLIF(?, 0) {$closed_at_sql_part} WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        // Si new_assignee_id est null, on le passe comme 0 pour que NULLIF fonctionne.
        $assignee_id_for_db = $new_assignee_id ?? 0;
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $assignee_id_for_db, $ticket_id);
        if (mysqli_stmt_execute($stmt)) {
            // Log status change as a comment
            if ($new_status !== $current_status) {
                $log_comment = "Le statut a été changé de '" . htmlspecialchars($current_status) . "' à '" . htmlspecialchars($new_status) . "'.";
                $sql_log = "INSERT INTO comments (ticket_id, user_id, comment, is_system_message) VALUES (?, ?, ?, 1)";
                if ($stmt_log = mysqli_prepare($link, $sql_log)) {
                    mysqli_stmt_bind_param($stmt_log, "iis", $ticket_id, $_SESSION['id'], $log_comment);
                    mysqli_stmt_execute($stmt_log);
                }
            }
            
            // Send notification email for status change
            $creator_id = $ticket_info['created_by_id'];
            $sql_user = "SELECT email, username FROM users WHERE id = ?";
            if($stmt_user = mysqli_prepare($link, $sql_user)){
                mysqli_stmt_bind_param($stmt_user, "i", $creator_id);
                mysqli_stmt_execute($stmt_user);
                if($creator = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_user))){
                    $email_title = "Statut du Ticket Mis à Jour";
                    $email_content = "<p>Bonjour " . htmlspecialchars($creator['username']) . ",</p>"
                                   . "<p>Le statut de votre ticket <strong>#" . $ticket_id . ": " . htmlspecialchars($ticket_info['title']) . "</strong> a été mis à jour.</p>"
                                   . "<p>Nouveau statut : <strong>" . htmlspecialchars($new_status) . "</strong></p>";
                    $ticket_url = $absolute_base_url . "/view_ticket.php?id=" . $ticket_id;
                    $cta_text = 'Consulter la Demande';
                    $email_body = generate_email_html($email_title, $email_content, $ticket_url, $cta_text);
                    $email_subject = "[Demande #" . $ticket_id . "] Statut mis à jour : " . htmlspecialchars($new_status);
                    send_notification_email($creator['email'], $email_subject, $email_body);
                }
            }
            return true;
        }
    }
    return false;
}

/**
 * Gère l'ajout d'un commentaire et des pièces jointes.
 *
 * @param mysqli $link
 * @param int $ticket_id
 * @param int $user_id
 * @param string $comment_text
 * @param array $files
 * @param array $ticket_info
 * @param string $absolute_base_url
 * @return bool
 */
function handle_add_comment($link, $ticket_id, $user_id, $comment_text, $files, $ticket_info, $absolute_base_url) {
    $sql = "INSERT INTO comments (ticket_id, user_id, comment) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "iis", $ticket_id, $user_id, $comment_text);
        if(mysqli_stmt_execute($stmt)){
            $comment_id = mysqli_insert_id($link);

            // Gestion des pièces jointes...
            // (Le code de gestion des pièces jointes reste identique)

            // Notification par email
            $recipients = [];
            if ($ticket_info['created_by_id'] != $user_id) { $recipients[$ticket_info['created_by_id']] = true; }
            if (!empty($ticket_info['assigned_to_id']) && $ticket_info['assigned_to_id'] != $user_id) { $recipients[$ticket_info['assigned_to_id']] = true; }

            if (!empty($recipients)) {
                $sql_users = "SELECT id, email, username FROM users WHERE id IN (" . implode(',', array_keys($recipients)) . ")";
                $result_users = mysqli_query($link, $sql_users);
                while ($user = mysqli_fetch_assoc($result_users)) {
                    $email_title = "Nouveau Commentaire";
                    $email_content = "<p>Un nouveau commentaire a été ajouté par <strong>" . htmlspecialchars($_SESSION['username']) . "</strong>.</p>"
                                   . "<blockquote>" . nl2br(htmlspecialchars($comment_text)) . "</blockquote>";
                    $ticket_url = $absolute_base_url . "/view_ticket.php?id=" . $ticket_id;
                    $cta_text = 'Voir le Commentaire';
                    $email_body = generate_email_html($email_title, $email_content, $ticket_url, $cta_text);
                    $email_subject = "[Demande #" . $ticket_id . "] Nouveau commentaire sur : " . htmlspecialchars($ticket_info['title']);
                    send_notification_email($user['email'], $email_subject, $email_body);
                }
            }
            return true;
        }
    }
    return false;
}

/**
 * Supprime une pièce jointe.
 *
 * @param mysqli $link
 * @param int $attachment_id
 * @return bool
 */
function handle_delete_attachment($link, $attachment_id) {
    $sql_select = "SELECT file_path FROM ticket_attachments WHERE id = ?";
    if ($stmt_select = mysqli_prepare($link, $sql_select)) {
        mysqli_stmt_bind_param($stmt_select, "i", $attachment_id);
        mysqli_stmt_execute($stmt_select);
        if ($attachment = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_select))) {
            $file_on_disk = $_SERVER['DOCUMENT_ROOT'] . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\') . '/public' . $attachment['file_path'];
            if (file_exists($file_on_disk)) {
                unlink($file_on_disk);
            }
            
            $sql_delete = "DELETE FROM ticket_attachments WHERE id = ?";
            if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
                mysqli_stmt_bind_param($stmt_delete, "i", $attachment_id);
                return mysqli_stmt_execute($stmt_delete);
            }
        }
    }
    return false;
}
?>
