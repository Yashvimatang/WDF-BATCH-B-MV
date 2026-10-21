
<?php
// Shared audit logging helper for StudentHub.

function logAdminAction(
    mysqli $conn,
    string $action,
    string $details = ""
): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return false;
    }

    if (($_SESSION["role"] ?? "") !== "admin") {
        return false;
    }

    $adminId = filter_var(
        $_SESSION["student_id"] ?? null,
        FILTER_VALIDATE_INT
    );

    $adminName = trim(
        (string) ($_SESSION["student_name"] ?? "Admin")
    );

    if (!$adminId || $adminName === "") {
        return false;
    }

    $stmt = $conn->prepare(
        "INSERT INTO audit_logs
         (admin_id, admin_name, action, details)
         VALUES (?, ?, ?, ?)"
    );

    if (!$stmt) {
        error_log("StudentHub audit log prepare failed.");
        return false;
    }

    $stmt->bind_param(
        "isss",
        $adminId,
        $adminName,
        $action,
        $details
    );

    $success = $stmt->execute();

    if (!$success) {
        error_log("StudentHub audit log insert failed.");
    }

    $stmt->close();

    return $success;
}
?>
