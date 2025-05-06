<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Register hooks

// Register module metadata for activation
// register_activation_hook('timecamp_sync', 'timecamp_sync_module_sql_activate');

function timecamp_sync_module_sql_activate()
{
    try {
        $CI = &get_instance();

        // Create options
        add_option('timecamp_api_key', '');

        // Create tables
        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_timecamp_projects` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `perfex_project_id` INT NOT NULL,
            `timecamp_project_id` VARCHAR(255) NOT NULL,
            `last_sync` DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        $CI->db->query("CREATE TABLE IF NOT EXISTS `tbl_timecamp_logs` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `task_id` INT NOT NULL,
            `user_id` INT,
            `start_time` DATETIME,
            `end_time` DATETIME,
            `duration` INT,
            `note` TEXT,
            `imported_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        log_message('passed', '⏱️[TimeCamp Sync] Activation error: ');
    } catch (Exception $e) {
        log_message('debug', '⏱️[TimeCamp Sync] Activation error: ' . $e->getMessage());
    }
}
