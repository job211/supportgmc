

    <!-- Section Tâches (Restyled) -->
 
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Tâches Associées</h5>
                    <span class="badge bg-primary rounded-pill"><?= count($tasks) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($tasks)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 table-sm" style="max-width: 1200px; font-size: 0.875rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-3">Titre</th>
                                        <th scope="col" class="text-center">Statut</th>
                                        <th scope="col" class="text-center d-none d-xl-table-cell">Priorité</th>
                                        <th scope="col" class="d-none d-lg-table-cell">Responsable</th>
                                        <th scope="col" class="d-none d-lg-table-cell">Échéance</th>
                                        <th scope="col" class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold"><?= htmlspecialchars($task['title']) ?></div>
                                            <small class="text-muted">#<?= $task['id'] ?></small>
                                        </td>
                                        <td class="text-center"><?= get_status_badge($task['status']) ?></td>
                                        <td class="text-center d-none d-xl-table-cell"><?= get_priority_badge($task['priority']) ?></td>
                                        <td class="d-none d-lg-table-cell"><?= htmlspecialchars($task['assigned_to_username'] ?? 'Non assigné') ?></td>
                                        <td class="d-none d-lg-table-cell">
                                            <?php 
                                            if (!empty($task['due_date'])) {
                                                $due_date = new DateTime($task['due_date']);
                                                $now = new DateTime();
                                                $interval = $now->diff($due_date);
                                                $is_past = $interval->invert === 1 && $now->format('Y-m-d') > $due_date->format('Y-m-d');

                                                if ($is_past) {
                                                    echo '<span class="text-danger fw-bold">' . $due_date->format('d/m/Y') . '</span>';
                                                } elseif ($interval->days <= 7) {
                                                    echo '<span class="text-warning fw-bold">' . $due_date->format('d/m/Y') . '</span>';
                                                } else {
                                                    echo $due_date->format('d/m/Y');
                                                }
                                            } else {
                                                echo '<span class="text-muted">N/A</span>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="task_edit.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-light border" title="Voir la tâche">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-folder-plus fa-3x text-primary mb-3"></i>
                            <h5 class="mb-1">Aucune tâche pour l'instant</h5>
                            <p class="text-muted small">Ce projet n'a pas encore de tâche. <br>Cliquez sur "Nouvelle Tâche" pour en créer une.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
  