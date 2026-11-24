<?php
require_once 'db_connect.php';

// Optional: Seed sample records if table is completely empty and user clicks "Load Sample Data"
if (isset($_POST['seed_sample'])) {
    $check = $conn->query("SELECT COUNT(*) AS cnt FROM documents");
    $row = $check->fetch_assoc();
    if ((int)$row['cnt'] === 0) {
        $sampleSQL = "INSERT INTO `documents` (
            `tracking_number`, `title`, `doc_type`, `sender_name`, `origin_dept`,
            `current_office`, `priority`, `status`, `date_received`, `remarks`
        ) VALUES
        ('DTS-2026-1001', 'Annual IT Equipment Procurement Request', 'Purchase Request', 'Engr. Marco Villanueva', 'IT Department', 'Budget & Finance Office', 'High', 'Under Review', '2026-11-10', 'Awaiting budget clearance signature from Finance Director.'),
        ('DTS-2026-1002', 'Memorandum of Agreement - Industry Internship', 'Legal Contract', 'Dr. Elena Soriano', 'Academic Affairs', 'Legal Office', 'Normal', 'In Transit', '2026-11-12', 'Forwarded for legal review and notarization.'),
        ('DTS-2026-1003', 'Q4 Facility Maintenance & Safety Audit Report', 'Audit Report', 'Arch. Dennis Ramos', 'General Services', 'Office of the President', 'Urgent', 'Approved', '2026-11-14', 'Approved for immediate implementation of electrical upgrades.'),
        ('DTS-2026-1004', 'Faculty Travel Authority - National Conference', 'Travel Order', 'Prof. Clarissa Mendoza', 'College of Computing', 'Human Resources', 'Normal', 'Completed', '2026-11-08', 'Signed copy released to the requesting faculty member.'),
        ('DTS-2026-1005', 'Revised Student Handbook Policy Addendum', 'Policy Memo', 'Dean Roberto Cruz', 'Student Affairs Office', 'Records Section', 'Low', 'Pending', '2026-11-15', 'Received at front desk; queued for routing to Academic Council.')";
        $conn->query($sampleSQL);
        header("Location: index.php?msg=seeded");
        exit;
    }
}

// Fetch Summary KPI counts (SELECT queries)
$totalDocs = 0;
$activeDocs = 0;
$urgentDocs = 0;
$completedDocs = 0;

$statsRes = $conn->query("SELECT 
    COUNT(*) AS total,
    SUM(CASE WHEN status IN ('Pending', 'In Transit', 'Under Review') THEN 1 ELSE 0 END) AS active_cnt,
    SUM(CASE WHEN priority = 'Urgent' AND status != 'Completed' THEN 1 ELSE 0 END) AS urgent_cnt,
    SUM(CASE WHEN status IN ('Approved', 'Completed') THEN 1 ELSE 0 END) AS done_cnt
FROM documents");

if ($statsRes && $statRow = $statsRes->fetch_assoc()) {
    $totalDocs     = (int)$statRow['total'];
    $activeDocs    = (int)$statRow['active_cnt'];
    $urgentDocs    = (int)$statRow['urgent_cnt'];
    $completedDocs = (int)$statRow['done_cnt'];
}

// Search and Filter Parameters (SELECT operation)
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$status   = isset($_GET['status']) ? trim($_GET['status']) : '';
$priority = isset($_GET['priority']) ? trim($_GET['priority']) : '';

$whereClauses = [];
$params = [];
$types = "";

if ($search !== '') {
    $whereClauses[] = "(tracking_number LIKE ? OR title LIKE ? OR sender_name LIKE ? OR origin_dept LIKE ? OR current_office LIKE ?)";
    $likeTerm = "%" . $search . "%";
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $params[] = $likeTerm;
    $types .= "sssss";
}

if ($status !== '') {
    $whereClauses[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}

if ($priority !== '') {
    $whereClauses[] = "priority = ?";
    $params[] = $priority;
    $types .= "s";
}

$sql = "SELECT * FROM documents";
if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Helper functions for badge classes
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Pending':      return 'status-pending';
        case 'In Transit':   return 'status-transit';
        case 'Under Review': return 'status-review';
        case 'Approved':     return 'status-approved';
        case 'Completed':    return 'status-completed';
        case 'Returned':     return 'status-returned';
        default:             return 'bg-secondary text-white';
    }
}

function getPriorityDotClass($priority) {
    switch ($priority) {
        case 'Low':    return 'priority-low';
        case 'Normal': return 'priority-normal';
        case 'High':   return 'priority-high';
        case 'Urgent': return 'priority-urgent';
        default:       return 'priority-normal';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuTrack | Document Tracking System</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-brand-custom py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php">
            <span class="brand-icon-box">
                <i class="bi bi-folder2-open fs-5"></i>
            </span>
            <div>
                <span class="fs-5 tracking-tight">DocuTrack</span>
                <span class="d-block text-info fw-normal" style="font-size: 0.7rem; letter-spacing: 0.04em;">DOCUMENT TRACKING SYSTEM</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link active fw-medium" href="index.php"><i class="bi bi-grid-1x2 me-1"></i> Dashboard & Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="create.php"><i class="bi bi-file-earmark-plus me-1"></i> Log New Document</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <a href="create.php" class="btn btn-info text-dark fw-semibold btn-sm px-3 py-2 rounded-3 shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> New Document
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="container py-4">

    <!-- Status Alert Messages -->
    <?php if (isset($_GET['msg'])): ?>
        <?php
        $msgType = 'success';
        $msgText = '';
        switch ($_GET['msg']) {
            case 'created':
                $msgText = '<strong>Success!</strong> New document record has been logged and assigned a tracking number.';
                break;
            case 'updated':
                $msgText = '<strong>Updated!</strong> Document routing and status details have been saved.';
                break;
            case 'deleted':
                $msgType = 'warning';
                $msgText = '<strong>Deleted!</strong> The document record has been permanently removed from the system.';
                break;
            case 'seeded':
                $msgType = 'info';
                $msgText = '<strong>Sample Data Loaded!</strong> 5 sample document records have been added for testing.';
                break;
        }
        ?>
        <?php if ($msgText !== ''): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show shadow-sm border-0 rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $msgText ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- KPI Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted small fw-semibold text-uppercase mb-1">Total Logged</p>
                    <h3 class="fw-bold mb-0"><?= $totalDocs ?></h3>
                </div>
                <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-files"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted small fw-semibold text-uppercase mb-1">In Routing / Active</p>
                    <h3 class="fw-bold mb-0 text-primary"><?= $activeDocs ?></h3>
                </div>
                <div class="stat-icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted small fw-semibold text-uppercase mb-1">Urgent Action</p>
                    <h3 class="fw-bold mb-0 text-danger"><?= $urgentDocs ?></h3>
                </div>
                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="text-muted small fw-semibold text-uppercase mb-1">Completed / Approved</p>
                    <h3 class="fw-bold mb-0 text-success"><?= $completedDocs ?></h3>
                </div>
                <div class="stat-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-patch-check"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls + Document Table -->
    <div class="content-card overflow-hidden">
        <div class="p-3 p-md-4 border-bottom bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-auto">
                    <h5 class="fw-bold mb-1">Document Routing Registry</h5>
                    <p class="text-muted small mb-0">Track incoming and outgoing documents, routing offices, and approval status.</p>
                </div>
                <div class="col-12 col-md-auto">
                    <form method="GET" action="index.php" class="row g-2 align-items-center">
                        <div class="col-12 col-sm-auto">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Tracking #, title, sender..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <?php
                                $statuses = ['Pending', 'In Transit', 'Under Review', 'Approved', 'Completed', 'Returned'];
                                foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st ?>" <?= ($status === $st) ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priorities</option>
                                <?php
                                $priorities = ['Low', 'Normal', 'High', 'Urgent'];
                                foreach ($priorities as $pr):
                                ?>
                                    <option value="<?= $pr ?>" <?= ($priority === $pr) ? 'selected' : '' ?>><?= $pr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-sm-auto d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                            <?php if ($search !== '' || $status !== '' || $priority !== ''): ?>
                                <a href="index.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Responsive Data Table -->
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Tracking No.</th>
                        <th>Document Title & Type</th>
                        <th>Origin / Sender</th>
                        <th>Current Office</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date Received</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="tracking-code"><?= htmlspecialchars($row['tracking_number']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($row['title']) ?></div>
                                    <span class="badge bg-light text-secondary border mt-1"><?= htmlspecialchars($row['doc_type']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($row['sender_name']) ?></div>
                                    <div class="small text-muted"><i class="bi bi-building me-1"></i><?= htmlspecialchars($row['origin_dept']) ?></div>
                                </td>
                                <td>
                                    <span class="fw-medium text-primary"><i class="bi bi-geo-alt-fill me-1"></i><?= htmlspecialchars($row['current_office']) ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold">
                                        <span class="priority-dot <?= getPriorityDotClass($row['priority']) ?>"></span>
                                        <?= htmlspecialchars($row['priority']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-status <?= getStatusBadgeClass($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    <?= date('M d, Y', strtotime($row['date_received'])) ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- View Modal Trigger -->
                                        <button type="button" class="btn btn-outline-secondary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#viewModal<?= $row['id'] ?>"
                                                title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <!-- Edit Button (UPDATE) -->
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary" title="Edit / Route Document">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <!-- Delete Button (DELETE) -->
                                        <button type="button" class="btn btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal<?= $row['id'] ?>"
                                                title="Delete Record">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <!-- View Details Modal -->
                                    <div class="modal fade text-start" id="viewModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header bg-light">
                                                    <div>
                                                        <h6 class="modal-title fw-bold mb-0">Document Routing Details</h6>
                                                        <span class="tracking-code mt-1 d-inline-block"><?= htmlspecialchars($row['tracking_number']) ?></span>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="text-muted small text-uppercase fw-bold">Document Title</label>
                                                        <div class="fs-6 fw-semibold"><?= htmlspecialchars($row['title']) ?></div>
                                                    </div>
                                                    <div class="row g-3 mb-3">
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Document Type</label>
                                                            <div><?= htmlspecialchars($row['doc_type']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Date Received</label>
                                                            <div><?= date('F d, Y', strtotime($row['date_received'])) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Sender Name</label>
                                                            <div><?= htmlspecialchars($row['sender_name']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Origin Department</label>
                                                            <div><?= htmlspecialchars($row['origin_dept']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Current Office</label>
                                                            <div class="fw-semibold text-primary"><?= htmlspecialchars($row['current_office']) ?></div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small text-uppercase fw-bold">Status & Priority</label>
                                                            <div>
                                                                <span class="badge-status <?= getStatusBadgeClass($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span>
                                                                <span class="ms-1 small text-muted">(<?= htmlspecialchars($row['priority']) ?>)</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-light rounded-3 border">
                                                        <label class="text-muted small text-uppercase fw-bold d-block mb-1">Routing Remarks / Notes</label>
                                                        <p class="mb-0 small"><?= !empty($row['remarks']) ? nl2br(htmlspecialchars($row['remarks'])) : '<em class="text-muted">No remarks recorded.</em>' ?></p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm"><i class="bi bi-pencil-square me-1"></i> Update Routing</a>
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade text-start" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-sm">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h6 class="modal-title fw-bold text-danger"><i class="bi bi-exclamation-octagon me-1"></i> Confirm Deletion</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="small text-muted mb-2">Are you sure you want to delete this document record?</p>
                                                    <div class="p-2 bg-light rounded border small">
                                                        <strong><?= htmlspecialchars($row['tracking_number']) ?></strong><br>
                                                        <?= htmlspecialchars($row['title']) ?>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="delete.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm">Delete Record</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                    <h6 class="fw-semibold mb-1">No Document Records Found</h6>
                                    <p class="small mb-0">Start by logging a new document or loading sample test data.</p>
                                </div>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="create.php" class="btn btn-primary btn-sm px-3">
                                        <i class="bi bi-plus-lg me-1"></i> Log New Document
                                    </a>
                                    <?php if ($totalDocs === 0): ?>
                                        <form method="POST" action="index.php" class="d-inline">
                                            <button type="submit" name="seed_sample" class="btn btn-outline-secondary btn-sm px-3">
                                                <i class="bi bi-database-add me-1"></i> Load 5 Sample Documents
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="py-3 bg-white border-top mt-5">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted">
        <div>
            <strong>DocuTrack</strong> &mdash; Document Tracking System (PHP &amp; MySQL CRUD Project)
        </div>
        <div>
            Built with Bootstrap 5, PHP &amp; MySQL
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
