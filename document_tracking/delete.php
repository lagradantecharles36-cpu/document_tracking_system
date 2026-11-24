<?php
require_once 'db_connect.php';

// Handle Record Deletion (DELETE Operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    if ($id > 0) {
        // MySQL DELETE Query
        $stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    header("Location: index.php?msg=deleted");
    exit;
}

header("Location: index.php");
exit;
?>
