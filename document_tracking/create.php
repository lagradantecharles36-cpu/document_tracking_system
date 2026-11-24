<?php
require_once 'db_connect.php';
require_once 'functions.php';

$tracking_number = generateTrackingNumber();


$error = '';
$defaultTrackingNo = generateTrackingNumber($conn);

// Handle Form Submission (INSERT Operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $title           = trim($_POST['title'] ?? '');
    $doc_type        = trim($_POST['doc_type'] ?? '');
    $sender_name     = trim($_POST['sender_name'] ?? '');
    $origin_dept     = trim($_POST['origin_dept'] ?? '');
    $current_office  = trim($_POST['current_office'] ?? '');
    $priority        = trim($_POST['priority'] ?? 'Normal');
    $status          = trim($_POST['status'] ?? 'Pending');
    $date_received   = trim($_POST['date_received'] ?? date('Y-m-d'));
    $remarks         = trim($_POST['remarks'] ?? '');

    if ($tracking_number === '' || $title === '' || $doc_type === '' || $sender_name === '' || $origin_dept === '' || $current_office === '' || $date_received === '') {
        $error = 'Please fill in all required fields marked with an asterisk (*).';
    } else {
        // MySQL INSERT Query
        $sql = "INSERT INTO documents (
                    tracking_number, title, doc_type, sender_name, origin_dept,
                    current_office, priority, status, date_received, remarks
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssss",
            $tracking_number,
            $title,
            $doc_type,
            $sender_name,
            $origin_dept,
            $current_office,
            $priority,
            $status,
            $date_received,
            $remarks
        );

        if ($stmt->execute()) {
            header("Location: index.php?msg=created");
            exit;
        } else {
            if ($conn->errno === 1062) {
                $error = "Tracking Number <strong>" . htmlspecialchars($tracking_number) . "</strong> already exists. Please use a unique tracking number.";
            } else {
                $error = "Database Error: " . htmlspecialchars($conn->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log New Document | DocuTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link fw-medium" href="index.php"><i class="bi bi-grid-1x2 me-1"></i> Dashboard & Logs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active fw-medium" href="create.php"><i class="bi bi-file-earmark-plus me-1"></i> Log New Document</a>
                </li>
            </ul>
            <a href="index.php" class="btn btn-outline-light btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Registry
            </a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="content-card p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-plus text-primary me-2"></i>Log New Document</h4>
                        <p class="text-muted small mb-0">Register an incoming or outgoing document for routing and status tracking.</p>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2">INSERT Operation</span>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="create.php">
                    <div class="row g-3">
                        <!-- Tracking Number & Date Received -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tracking Number <span class="text-danger">*</span></label>
                            <input type="text" name="tracking_number" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($_POST['tracking_number'] ?? $defaultTrackingNo) ?>" required>
                            <div class="form-text">Auto-generated unique tracking code.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date Received <span class="text-danger">*</span></label>
                            <input type="date" name="date_received" class="form-control"
                                   value="<?= htmlspecialchars($_POST['date_received'] ?? date('Y-m-d')) ?>" required>
                        </div>

                        <!-- Document Title -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Document Subject / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   placeholder="e.g., Annual IT Equipment Procurement Request"
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                        </div>

                        <!-- Document Type -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select" required>
                                <?php
                                $typesList = ['Memorandum', 'Purchase Request', 'Travel Order', 'Legal Contract', 'Audit Report', 'Policy Memo', 'Endorsement Letter', 'Financial Voucher', 'Other'];
                                $selectedType = $_POST['doc_type'] ?? 'Memorandum';
                                foreach ($typesList as $t):
                                ?>
                                    <option value="<?= $t ?>" <?= ($selectedType === $t) ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sender Name & Origin Department -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sender / Requesting Person <span class="text-danger">*</span></label>
                            <input type="text" name="sender_name" class="form-control"
                                   placeholder="e.g., Engr. Marco Villanueva"
                                   value="<?= htmlspecialchars($_POST['sender_name'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Originating Department <span class="text-danger">*</span></label>
                            <input type="text" name="origin_dept" class="form-control"
                                   placeholder="e.g., IT Department"
                                   value="<?= htmlspecialchars($_POST['origin_dept'] ?? '') ?>" required>
                        </div>

                        <!-- Current Receiving Office -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Current / Receiving Office <span class="text-danger">*</span></label>
                            <input type="text" name="current_office" class="form-control"
                                   placeholder="e.g., Records Section"
                                   value="<?= htmlspecialchars($_POST['current_office'] ?? '') ?>" required>
                        </div>

                        <!-- Priority Level -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <?php
                                $priorities = ['Low', 'Normal', 'High', 'Urgent'];
                                $selectedPriority = $_POST['priority'] ?? 'Normal';
                                foreach ($priorities as $pr):
                                ?>
                                    <option value="<?= $pr ?>" <?= ($selectedPriority === $pr) ? 'selected' : '' ?>><?= $pr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Routing Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Initial Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <?php
                                $statuses = ['Pending', 'In Transit', 'Under Review', 'Approved', 'Completed', 'Returned'];
                                $selectedStatus = $_POST['status'] ?? 'Pending';
                                foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st ?>" <?= ($selectedStatus === $st) ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Remarks -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Routing Remarks / Action Notes</label>
                            <textarea name="remarks" rows="3" class="form-control"
                                      placeholder="Enter handling instructions, required signatures, or routing remarks..."><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-light border px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-check2-circle me-1"></i> Save &amp; Log Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="py-3 bg-white border-top mt-5">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center small text-muted">
        <div><strong>DocuTrack</strong> &mdash; Document Tracking System</div>
        <div>Built with Bootstrap 5, PHP &amp; MySQL</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
