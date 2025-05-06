<?php
/**
 * Module Name:       TimeCamp Sync
 * Description: Sync TimeCamp entries into Perfex project tasks.
 * Version:    1.0.0
 * Author:     Ömer Teknoloji
 * Requires at least: 2.3.*
 */

defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('admin_init', 'timecamp_sync_module_init_menu');
hooks()->add_action('app_admin_footer', 'timecamp_sync_footer_script');

function timecamp_sync_footer_script()
{
    $CI  = &get_instance();
    $uri = $CI->uri->uri_string();

    if (strpos($uri, 'projects/view') !== false) {
        echo '<script>
        $(function(){
            if ($(".btn-info.mbot25").length) {
                var $btn = $("<a/>", {
                    class: "btn btn-success mbot25 mleft5",
                    href: "#",
                    onclick: "sync_timecamp_entries();return false;",
                    text: "Sync from TimeCamp"
                });
                $(".btn-info.mbot25").after($btn);
            }

            window.sync_timecamp_entries = function() {
                var project_id = $("input[name=project_id]").val();
                if (!project_id) {
                    alert("Project ID not found.");
                    return;
                }
                $.get(admin_url + "timecamp_sync/sync_project/" + project_id, function(res) {
                    let data = JSON.parse(res);
                    alert(data.status === "success" ? "Imported " + data.imported + " entries." : data.error);
                    location.reload();
                });
            }
        });
        </script>';
    }
}
// Add menu item in sidebar
function timecamp_sync_module_init_menu()
{
    try {
        if (is_admin()) {
            $CI = &get_instance();
            $CI->app_menu->add_setup_menu_item('timecamp-sync', [
                'slug'     => 'timecamp-sync',
                'name'     => 'TimeCamp Sync',
                'href'     => admin_url('timecamp_sync/settings'),
                'position' => 35,
                // 'icon'     => 'fa fa-clock-o',
            ]);
        }
    } catch (Exception $e) {
        log_message('debug', '⏱️[TimeCamp Sync] Failed to initialize menu: ' . $e->getMessage());
    }
}


// Register module metadata for activation
register_activation_hook('timecamp_sync', 'timecamp_sync_module_activate');

function timecamp_sync_module_activate()
{
    try {
        require_once __DIR__ . '/install.php';
        timecamp_sync_module_sql_activate(); // call the function inside install.php
    } catch (Exception $e) {
        log_message('debug', '⏱️[TimeCamp Sync] timecamp_sync_module_activate: ' . $e->getMessage());
    }
}
