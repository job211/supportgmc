<?php
require_once 'config/database.php';

// Désactiver les exceptions
mysqli_report(MYSQLI_REPORT_OFF);

$indexes = [
    "CREATE INDEX idx_comments_ticket_id ON comments(ticket_id)",
    "CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to)",
    "CREATE INDEX idx_tasks_status ON tasks(status)",
    "CREATE INDEX idx_tasks_ticket_id ON tasks(ticket_id)",
    "CREATE INDEX idx_users_username ON users(username)",
    "CREATE INDEX idx_specifications_created_by ON specifications(created_by)"
];

echo "========================================\n";
echo "CRÉATION DES INDEXES MANQUANTS\n";
echo "========================================\n\n";

$created = 0;
$failed = 0;

foreach ($indexes as $index) {
    mysqli_query($link, $index);
    $error = mysqli_error($link);
    
    if (empty($error)) {
        $created++;
        preg_match('/idx_\w+/', $index, $matches);
        echo "✅ " . $matches[0] . " créé\n";
    } elseif (strpos($error, 'Duplicate key name') !== false) {
        $created++;
        preg_match('/idx_\w+/', $index, $matches);
        echo "✅ " . $matches[0] . " (déjà existant)\n";
    } else {
        $failed++;
        preg_match('/idx_\w+/', $index, $matches);
        echo "⚠️ " . $matches[0] . " - " . $error . "\n";
    }
}

echo "\n========================================\n";
echo "RÉSULTAT: $created indexes OK, $failed problèmes\n";
echo "========================================\n";

mysqli_close($link);
?>
