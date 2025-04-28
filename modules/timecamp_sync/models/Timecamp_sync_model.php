<?php
# modules/timecamp_sync/models/Timecamp_sync_model.php
defined('BASEPATH') or exit('No direct script access allowed');

class Timecamp_sync_model extends App_Model
{

    public function __construct()
    {
        parent::__construct();
    }
    public function get_or_create_mapping($perfex_project_id, $project_name)
    {
        $existing = $this->db->get_where('tbl_timecamp_projects', ['perfex_project_id' => $perfex_project_id])->row_array();
        if ($existing) return $existing;

        $apiKey = get_option('timecamp_api_key');
        $url = "https://app.timecamp.com/third_party/api/tasks?format=json";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $apiKey]);
        $response = curl_exec($ch);
        curl_close($ch);

        log_message('timecamp', '⏱️[TimeCamp Sync] get_or_create_mapping API response: ' . $response);

        $tasks = json_decode($response, true);

        if (!is_array($tasks)) {
            log_message('timecamp', '⏱️[TimeCamp Sync] Invalid response from TimeCamp when fetching tasks');
            return [];
        }

        foreach ($tasks as $task_id => $task) {
            if (isset($task['name']) && trim(strtolower($task['name'])) == trim(strtolower($project_name))) {
                $this->db->insert('tbl_timecamp_projects', [
                    'perfex_project_id' => $perfex_project_id,
                    'timecamp_project_id' => $task_id,
                    'last_sync' => null
                ]);
                log_message('timecamp', '⏱️[TimeCamp Sync] Matched and saved project "' . $project_name . '" with ID: ' . $task_id);
                return $this->db->get_where('tbl_timecamp_projects', ['perfex_project_id' => $perfex_project_id])->row_array();
            }
        }

        log_message('timecamp', '⏱️[TimeCamp Sync] No matching TimeCamp task found for: ' . $project_name);
        return [];
    }


    public function get_time_entries($timecamp_project_id, $last_sync)
    {
        log_message('timecamp', '⏱️[TimeCamp Sync] Fetching entries for: ' . $timecamp_project_id . ' since: ' . $last_sync);

        $from = $last_sync ? date('Y-m-d', strtotime($last_sync)) : date('Y-m-d', strtotime('-600 days'));
        $to = date('Y-m-d');

        $url     = "https://app.timecamp.com/third_party/api/entries?task_id={$timecamp_project_id}&from={$from}&to={$to}";
        $headers = [
            "Authorization: Bearer " . get_option('timecamp_api_key'),
            "Accept: application/json",
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        log_message('timecamp', '⏱️[TimeCamp Sync] Raw API response: ' . $response);
        curl_close($ch);

        $data = json_decode($response, true);
        return is_array($data) ? $data : [];
    }

    public function insert_time_entries_to_first_task($project_id, $entries, $timecamp_task_id)
    {
        $task = $this->db->limit(1)->get_where('tasks', ['rel_type' => 'project', 'rel_id' => $project_id])->row();
        if (!$task) {
            return;
        }

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                log_message('timecamp', '⏱️[TimeCamp Sync] Skipping invalid entry (not array): ' . print_r($entry, true));
                continue;
            }

            // ✅ Filter entries by exact TimeCamp task ID
            if (!isset($entry['task_id']) || $entry['task_id'] != $timecamp_task_id) {
                continue;
            }

            $staff_id = get_staff_user_id();
            $staff = $this->db->get_where('staff', ['staffid' => $staff_id])->row();
            $hourly_rate = ($staff && isset($staff->hourly_rate)) ? (float) $staff->hourly_rate : 0.00;

            $start = $entry['start_time'] ?? ($entry['from'] ?? null);
            $end   = $entry['end_time'] ?? ($entry['to'] ?? null);
            $duration = $entry['duration'] ?? 0;
            $note  = $entry['description'] ?? ($entry['description'] ?? 'Imported from TimeCamp');

            if (!$start || !$end) {
                log_message('timecamp', '⏱️[TimeCamp Sync] Invalid entry missing time: ' . print_r($entry, true));
                continue;
            }

            $start_unix = strtotime($start);
            // $end_unix   = strtotime($end);
            $end_unix   = $start_unix + $duration;
            // $duration   = $end_unix - $start_unix;

            // $duration = strtotime($end) - strtotime($start);

            // ✅ Insert into Perfex core timer table
            $this->db->insert('tbltaskstimers', [
                'task_id'     => $task->id,
                'start_time'  => $start_unix,
                'end_time'    => $end_unix,
                'staff_id'    => get_staff_user_id(),
                'hourly_rate' => $hourly_rate, // Or fetch based on staff ID if needed
                'note'        => $note,
            ]);

            $this->db->insert('tbl_timecamp_logs', [
                'task_id'    => $task->id,
                'user_id'    => get_staff_user_id(),
                'start_time' => date('Y-m-d H:i:s', strtotime($start)),
                'end_time'   => date('Y-m-d H:i:s', strtotime($end)),
                'duration'   => $duration,
                'note'       => $note,
            ]);

            $this->db->insert('task_comments', [
                'taskid'    => $task->id,
                'content'   => '[TimeCamp Sync] ' . $note,
                'staffid'   => get_staff_user_id(),
                'dateadded' => date('Y-m-d H:i:s'),
            ]);
        }
    }


    public function update_last_sync($mapping_id)
    {
        $this->db->where('id', $mapping_id);
        $this->db->update('tbl_timecamp_projects', ['last_sync' => date('Y-m-d H:i:s')]);
    }

}
