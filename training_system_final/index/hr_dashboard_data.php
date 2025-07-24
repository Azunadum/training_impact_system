<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");

$filter = $_GET['filter'] ?? '';
$search = trim($_GET['search'] ?? '');
$sort = in_array($_GET['sort'] ?? '', ['staff_name', 'title', 'start_date', 'end_date', 'hours']) ? $_GET['sort'] : 'te.id';
$dir = strtoupper($_GET['dir'] ?? 'ASC') === 'ASC' ? 'ASC' : 'DESC';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$whereParts = [];
$params = [];

if ($filter === 'Pending' || $filter === 'Completed') {
  $whereParts[] = "te.status = ?";
  $params[] = $filter;
} elseif ($filter === 'COS' || $filter === 'Permanent') {
  $whereParts[] = "te.staff_type = ?";
  $params[] = $filter;
}

if ($search) {
  $whereParts[] = "(te.staff_name LIKE ? OR te.title LIKE ? OR te.unique_code LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

$where = count($whereParts) ? "WHERE " . implode(" AND ", $whereParts) : '';

$countStmt = $pdo->prepare("SELECT COUNT(DISTINCT te.id) FROM training_entries te $where");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

$stmt = $pdo->prepare("
  SELECT te.*, 
         GROUP_CONCAT(sd.file_path SEPARATOR '||') AS file_paths,
         GROUP_CONCAT(sd.file_name SEPARATOR '||') AS file_names
  FROM training_entries te
  LEFT JOIN supporting_docs sd ON te.id = sd.training_entry_id
  $where
  GROUP BY te.id
  ORDER BY $sort $dir
  LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$showingStart = ($totalRows > 0) ? ($offset + 1) : 0;
$showingEnd = min($offset + count($entries), $totalRows);

echo "<p>Showing {$showingStart}&ndash;{$showingEnd} of $totalRows entries</p>";

echo '<div class="table-responsive"><table class="table table-bordered table-striped align-middle">';
echo '<thead class="table-dark"><tr>';
function sortLink($col, $label, $sort, $dir) {
  $icon = '⇅';
  if ($sort == $col) $icon = $dir == 'ASC' ? '▲' : '▼';
  $nextDir = ($sort == $col && $dir == 'ASC') ? 'DESC' : 'ASC';
  return "<a href='#' data-sort='$col' data-dir='$nextDir'>$label <span class='sort-icon'>$icon</span></a>";
}
echo '<th>'.sortLink('staff_name', 'Name', $sort, $dir).'</th>';
echo '<th>Email</th><th>Staff</th>';
echo '<th>'.sortLink('title', 'Training Title', $sort, $dir).'</th>';
echo '<th>Role</th>';
echo '<th>'.sortLink('start_date', 'Start Date', $sort, $dir).'</th>';
echo '<th>'.sortLink('end_date', 'End Date', $sort, $dir).'</th>';
echo '<th>'.sortLink('hours', 'Hours', $sort, $dir).'</th>';
echo '<th>Type</th><th>Conducted/Sponsored</th><th>Code</th><th>Status</th><th>Document</th>';
echo '</tr></thead><tbody>';

if ($entries) {
  foreach ($entries as $e) {
    echo '<tr>';
    echo '<td>'.htmlspecialchars($e['staff_name']).'</td>';
    echo '<td>'.htmlspecialchars($e['staff_email']).'</td>';
    echo '<td>'.htmlspecialchars($e['staff_type']).'</td>';
    echo '<td>'.htmlspecialchars($e['title']).'</td>';
    echo '<td>'.htmlspecialchars($e['role']).'</td>';
    echo '<td>'.htmlspecialchars($e['start_date']).'</td>';
    echo '<td>'.htmlspecialchars($e['end_date']).'</td>';
    echo '<td>'.htmlspecialchars($e['hours']).'</td>';
    echo '<td>'.htmlspecialchars($e['type']).'</td>';
    echo '<td>'.htmlspecialchars($e['institution']).'</td>';
    echo '<td>'.htmlspecialchars($e['unique_code']).'</td>';
    echo '<td>';
    if ($e['status'] === 'Completed') {
      echo '<span class="badge bg-primary">Completed</span>';
    } elseif ($e['status'] === 'Pending') {
      echo '<span class="badge bg-secondary">Pending</span>';
    } else {
      echo htmlspecialchars($e['status']);
    }
    echo '</td>';
    echo '<td>';
        if ($e['file_paths']) {
              $paths = explode('||', $e['file_paths']);
              $names = explode('||', $e['file_names']);
              echo '<div class="d-grid gap-1">';
              foreach ($paths as $i => $path) {
                $name = htmlspecialchars($names[$i] ?? basename($path));
                $fileExt = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $fileUrl = htmlspecialchars($path);

                if (in_array($fileExt, ['pdf'])) {
                  echo '<button class="btn btn-custom btn-sm" onclick="showPreview(\''.$fileUrl.'\', \'pdf\')">';
                  echo '<i class="bi bi-file-earmark-pdf"></i> ' . $name . '</button>';
                } elseif (in_array($fileExt, ['jpg','jpeg','png','gif'])) {
                  echo '<button class="btn btn-custom btn-sm" onclick="showPreview(\''.$fileUrl.'\', \'image\')">';
                  echo '<i class="bi bi-file-image"></i> ' . $name . '</button>';
                } else {
                  echo '<a href="'.$fileUrl.'" target="_blank" class="btn btn-custom btn-sm">';
                  echo '<i class="bi bi-download"></i> ' . $name . '</a>';
                }
              }
              echo '</div>';
            } else {
              echo 'No file';
            }
    echo '</td>';
    echo '</tr>';
  }
} else {
  echo '<tr><td colspan="13" class="text-center">No records found.</td></tr>';
}
echo '</tbody></table></div>';

if ($totalPages > 1) {
  echo '<nav><ul class="pagination justify-content-center">';
  for ($i = 1; $i <= $totalPages; $i++) {
    $active = $page == $i ? 'active' : '';
    echo "<li class='page-item $active'><a href='#' class='page-link' data-page='$i'>$i</a></li>";
  }
  echo '</ul></nav>';
}
