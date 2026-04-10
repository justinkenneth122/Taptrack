<?php
/**
 * Installation Page
 */

function showInstallPage($host, $dbname, $user, $pass) {
    require_once __DIR__ . '/../config/constants.php';
    ?><!DOCTYPE html><html><head><title>Taptrack Install</title></head><body style="font-family:sans-serif;max-width:700px;margin:40px auto;padding:20px;">
    <h1>Taptrack — Database Setup Required</h1>
    <p>Create a MySQL database called <code><?= htmlspecialchars($dbname) ?></code> and run this SQL:</p>
    <pre style="background:#f5f5f5;padding:15px;border-radius:8px;overflow:auto;font-size:13px;"><?= htmlspecialchars(getInstallSQL()) ?></pre>
    <p>Then refresh this page.</p></body></html><?php
}
