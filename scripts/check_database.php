<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '', [PDO::ATTR_TIMEOUT => 2]);
    echo "MySQL tersedia\n";
} catch (Throwable $e) {
    echo "MySQL tidak aktif; pratonton boleh menggunakan SQLite setempat.\n";
}
