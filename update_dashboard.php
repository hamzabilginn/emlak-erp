<?php
$file = 'app/Controllers/DashboardController.php';
$content = file_get_contents($file);

$taskFunctions = <<<EOT

    public function addTask(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset(\$_SESSION['tenant_id'])) {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        \$description = trim(\$_POST['description'] ?? '');
        \$taskDate = trim(\$_POST['task_date'] ?? date('Y-m-d'));

        if (empty(\$description)) {
            echo json_encode(['success' => false, 'message' => 'Açıklama boş olamaz.']);
            return;
        }

        \$db = \Config\Database::getInstance()->getConnection();
        \$stmt = \$db->prepare("INSERT INTO tasks (tenant_id, task_date, description, is_completed) VALUES (:t, :date, :desc, false) RETURNING id");
        \$stmt->execute([
            ':t' => \$_SESSION['tenant_id'],
            ':date' => \$taskDate,
            ':desc' => \$description
        ]);
        
        \$newId = \$stmt->fetchColumn();

        echo json_encode(['success' => true, 'id' => \$newId]);
    }

    public function completeTask(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset(\$_SESSION['tenant_id'])) {
            echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
            return;
        }

        \$id = (int)(\$_POST['id'] ?? 0);
        \$completed = filter_var(\$_POST['is_completed'] ?? false, FILTER_VALIDATE_BOOLEAN);

        \$db = \Config\Database::getInstance()->getConnection();
        \$stmt = \$db->prepare("UPDATE tasks SET is_completed = :c WHERE id = :id AND tenant_id = :t");
        \$stmt->execute([
            ':c' => \$completed ? 'true' : 'false',
            ':id' => \$id,
            ':t' => \$_SESSION['tenant_id']
        ]);

        echo json_encode(['success' => true]);
    }

EOT;

$indexReplacementSearch = "        // Tüm verileri hazırladığımız 'dashboard/index.php' view'ına Render ile paslarız.";
$indexReplacementSearchAlt = "        // TÃ¼m verileri hazÄ±rladÄ±ÄŸÄ±mÄ±z 'dashboard/index.php' view'Ä±na Render ile paslarÄ±z.";

$taskFetch = <<<EOT
        // Günlük Ajanda
        \$stmt = \$db->prepare("SELECT * FROM tasks WHERE tenant_id = :t ORDER BY task_date ASC, is_completed ASC, id DESC LIMIT 20");
        \$stmt->execute([':t' => \$tenantId]);
        \$tasks = \$stmt->fetchAll();

EOT;

if (strpos($content, $indexReplacementSearch) !== false) {
    echo "Found clean text";
    $content = str_replace($indexReplacementSearch, $taskFetch . "\n        // Tüm verileri hazırladığımız 'dashboard/index.php' view'ına Render ile paslarız.", $content);
} elseif (strpos($content, $indexReplacementSearchAlt) !== false) {
    echo "Found corrupted text";
    $content = str_replace($indexReplacementSearchAlt, $taskFetch . "\n        // Tüm verileri hazırladığımız 'dashboard/index.php' view'ına Render ile paslarız.", $content);
}

$content = str_replace("        \$this->render('dashboard/index', [", "        \$this->render('dashboard/index', [\n            'tasks' => \$tasks,", $content);

$content = preg_replace('/}\s*$/', $taskFunctions . "\n}", $content);

file_put_contents($file, $content);
echo " Done";