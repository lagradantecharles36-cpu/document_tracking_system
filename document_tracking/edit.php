<?php
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) {
    header("Location: index.php");
    exit;
}

// Fetch existing document record (SELECT Operation)
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$doc = $res->fetch_assoc();

if (!$doc) {
    header("Location: index.php");
    exit;
}

$error = '';

// Handle Form Submission (UPDATE Operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $title           = trim($_POST['title'] ?? '');
    $doc_type        = trim($_POST['doc_type'] ?? '');
    $sender_name     = trim($_POST['sender_name'] ?? '');
    $origin_dept     = trim($_POST['origin_dept'] ?? '');
    $current_office  = trim($_POST['current_office'] ?? '');
    $priority        = trim($_POST['priority'] ?? 'Normal');
    $status          = trim($_POST['status'] ?? 'Pending');
    $date_received   = trim($_POST['date_received'] ?? '');
    $remarks         = trim($_POST['remarks'] ?? '');

    if ($tracking_number === '' || $title === '' || $doc_type === '' || $sender_name === '' || $origin_dept === '' || $current_office === '' || $date_received === '') {
        $error = 'Please fill in all required fields marked with an asterisk (*).';
    } else {
        // MySQL UPDATE Query
        $updateSQL = "UPDATE documents SET
                        tracking_number = ?,
                        title = ?,
                        doc_type = ?,
                        sender_name = ?,
                        origin_dept = ?,
                        current_office = ?,
                        priority = ?,
                        status = ?,
                        date_received = ?,
                        remarks = ?
                      WHERE id = ?";

        $upStmt = $conn->prepare($updateSQL);
        $upStmt->bind_param(
            "ssssssssssi",
            $tracking_number,
            $title,
            $doc_type,
            $sender_name,
            $origin_dept,
            $current_office,
            $priority,
            $status,
            $date_received,
            $remarks,
            $id
        );

        if ($upStmt->execute()) {
            header("Location: index.php?msg=updated");
            exit;
        } else {
            if ($conn->errno === 1062) {
                $error = "Tracking Number <strong>" . htmlspecialchars($tracking_number) . "</strong> is already assigned to another document.";
            } else {
                $error = "Database Error: " . htmlspecialchars($conn->error);
            }
        }
    }

    // Keep submitted values in form on error
    $doc = [
        'id'              => $id,
        'tracking_number' => $tracking_number,
        'title'           => $title,
        'doc_type'        => $doc_type,
        'sender_name'     => $sender_name,
        'origin_dept'     => $origin_dept,
        'current_office'  => $current_office,
        'priority'        => $priority,
        'status'          => $status,
        'date_received'   => $date_received,
        'remarks'         => $remarks
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Document | DocuTrack</title>
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
        <div class="ms-auto">
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
                        <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-primary me-2"></i>Update &amp; Route Document</h4>
                        <p class="text-muted small mb-0">Modify document information, update current office location, or change routing status.</p>
                    </div>
                    <span class="badge bg-info bg-opacity-10 text-primary border border-info-subtle px-3 py-2">UPDATE Operation</span>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="edit.php">
                    <input type="hidden" name="id" value="<?= (int)$doc['id'] ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Tracking Number <span class="text-danger">*</span></label>
                            <input type="text" name="tracking_number" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($doc['tracking_number']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date Received <span class="text-danger">*</span></label>
                            <input type="date" name="date_received" class="form-control"
                                   value="<?= htmlspecialchars($doc['date_received']) ?>" required>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold small">Document Subject / Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control"
                                   value="<?= htmlspecialchars($doc['title']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Document Type <span class="text-danger">*</span></label>
                            <select name="doc_type" class="form-select" required>
                                <?php
                                $typesList = ['Memorandum', 'Purchase Request', 'Travel Order', 'Legal Contract', 'Audit Report', 'Policy Memo', 'Endorsement Letter', 'Financial Voucher', 'Other'];
                                if (!in_array($doc['doc_type'], $typesList)) {
                                    $typesList[] = $doc['doc_type'];
                                }
                                foreach ($typesList as $t):
                                ?>
                                    <option value="<?= htmlspecialchars($t) ?>" <?= ($doc['doc_type'] === $t) ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Sender / Requesting Person <span class="text-danger">*</span></label>
                            <input type="text" name="sender_name" class="form-control"
                                   value="<?= htmlspecialchars($doc['sender_name']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Originating Department <span class="text-danger">*</span></label>
                            <input type="text" name="origin_dept" class="form-control"
                                   value="<?= htmlspecialchars($doc['origin_dept']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Current / Receiving Office <span class="text-danger">*</span></label>
                            <input type="text" name="current_office" class="form-control"
                                   value="<?= htmlspecialchars($doc['current_office']) ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Priority Level <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <?php
                                $priorities = ['Low', 'Normal', 'High', 'Urgent'];
                                foreach ($priorities as $pr):
                                ?>
                                    <option value="<?= $pr ?>" <?= ($doc['priority'] === $pr) ? 'selected' : '' ?>><?= $pr ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold small">Routing Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <?php
                                $statuses = ['Pending', 'In Transit', 'Under Review', 'Approved', 'Completed', 'Returned'];
                                foreach ($statuses as $st):
                                ?>
                                    <option value="<?= $st ?>" <?= ($doc['status'] === $st) ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Routing Remarks / Action Notes</label>
                            <textarea name="remarks" rows="3" class="form-control"><?= htmlspecialchars($doc['remarks'] ?? '') ?></textarea>
                        </div>

                        <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-light border px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                <i class="bi bi-save me-1"></i> Save Changes
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
