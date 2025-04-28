<?php
# modules/timecamp_sync/config/config.php
defined('BASEPATH') or exit('No direct script access allowed');

/* -------------------------------------------------------------
 * CONTROLLER: Timecamp_sync.php
 * -------------------------------------------------------------
 */

    class Timecamp_sync extends AdminController
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->model('timecamp_sync_model');
        }

        public function settings()
        {
            try {
                //code...
                if ($this->input->post()) {
                    update_option('timecamp_api_key', $this->input->post('timecamp_api_key'));
                    set_alert('success', 'Settings updated');
                    redirect(admin_url('timecamp_sync/settings'));
                }
                $data['title']   = 'TimeCamp Sync Settings';
                $data['api_key'] = get_option('timecamp_api_key');
                $this->load->view('timecamp_sync/settings', $data);
                } catch (Exception $e) {
                    log_message('timecamp', '⏱️[TimeCamp Sync] Save settings: ' . $e->getMessage());
                }
        }

        public function sync_project($project_id)
        {
            try {
                $project = $this->projects_model->get($project_id);
                if (! $project) {
                    echo json_encode(['error' => 'Invalid project ID']);
                    return;
                }
    
                $mapped  = $this->timecamp_sync_model->get_or_create_mapping($project->id, $project->name);
                $entries = $this->timecamp_sync_model->get_time_entries($mapped['timecamp_project_id'], $mapped['last_sync']);
                $this->timecamp_sync_model->insert_time_entries_to_first_task($project->id, $entries, $mapped['timecamp_project_id'] );
                $this->timecamp_sync_model->update_last_sync($mapped['id']);
    
                echo json_encode(['status' => 'success', 'imported' => count($entries)]);
            } catch (Exception $e) {
                log_message('timecamp', '⏱️[TimeCamp Sync] sync_project: ' . $e->getMessage());
            }
        }

        public function force_install()
        {
            timecamp_sync_module_sql_activate(); // or the correct renamed version
            echo "Module setup completed.";
        }

    }
