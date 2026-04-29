<?php
require_once 'config.php';

// VULNERABILITY: No session invalidation on logout (ASVS 7.3.1)
// session_destroy() is not called, session file remains on server

session_unset();  // Just clears variables, doesn't destroy session
// session_destroy();  // Intentionally commented out

header("Location: index.php");
exit();
?>