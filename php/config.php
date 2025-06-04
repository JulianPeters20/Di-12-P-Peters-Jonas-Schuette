<?php
// config.php

// Umschalter: true = MySQL, false = SQLite
const USE_MYSQL = false;

// Verbindungsdatei einbinden
if (USE_MYSQL) {
    require_once __DIR__ . '/../data/pdo-mysql.php';
} else {
    require_once __DIR__ . '/../data/pdo.php';
}
