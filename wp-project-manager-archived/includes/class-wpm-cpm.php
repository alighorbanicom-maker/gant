<?php
/**
 * Handles the Critical Path Method (CPM) calculations.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 */

class WPM_CPM {

    private $tasks = [];
    private $critical_path;

    public function __construct(array $tasks_from_db) {
        $this->tasks = $this->prepare_tasks($tasks_from_db);
    }

    private function prepare_tasks($tasks_from_db) {
        $tasks = [];
        foreach ($tasks_from_db as $task) {
            $dependencies = !empty($task['dependencies']) ? explode(',', $task['dependencies']) : [];
            $tasks[$task['id']] = [
                'id' => $task['id'],
                'name' => $task['task_name'],
                'duration' => (int)$task['duration'],
                'dependencies' => $dependencies,
                'es' => 0, 'ef' => 0, 'ls' => 0, 'lf' => 0, 'slack' => 0,
                'successors' => []
            ];
        }

        // Build successor relationships
        foreach ($tasks as $task_id => &$task_data) {
            foreach ($task_data['dependencies'] as $dep_id) {
                if (isset($tasks[$dep_id])) {
                    $tasks[$dep_id]['successors'][] = $task_id;
                }
            }
        }
        return $tasks;
    }

    public function calculate() {
        if (empty($this->tasks)) return [];

        $this->forward_pass();
        $this->backward_pass();
        $this->calculate_slack();
        $this->find_critical_path();

        return $this->tasks;
    }

    private function forward_pass() {
        // This is a simplified implementation. A topological sort would be more robust.
        // For now, we iterate multiple times to ensure propagation.
        for ($i = 0; $i < count($this->tasks); $i++) {
            foreach ($this->tasks as &$task) {
                if (empty($task['dependencies'])) {
                    $task['es'] = 1; // Start day 1
                } else {
                    $max_ef = 0;
                    foreach ($task['dependencies'] as $dep_id) {
                        if (isset($this->tasks[$dep_id]) && $this->tasks[$dep_id]['ef'] > $max_ef) {
                            $max_ef = $this->tasks[$dep_id]['ef'];
                        }
                    }
                    $task['es'] = $max_ef + 1;
                }
                $task['ef'] = $task['es'] + $task['duration'] - 1;
            }
        }
    }

    private function backward_pass() {
        $project_finish_time = 0;
        foreach ($this->tasks as $task) {
            if ($task['ef'] > $project_finish_time) {
                $project_finish_time = $task['ef'];
            }
        }

        foreach (array_reverse(array_keys($this->tasks)) as $task_id) {
            $task = &$this->tasks[$task_id];
            if (empty($task['successors'])) {
                $task['lf'] = $project_finish_time;
            } else {
                $min_ls = PHP_INT_MAX;
                foreach ($task['successors'] as $succ_id) {
                    if (isset($this->tasks[$succ_id]) && $this->tasks[$succ_id]['ls'] < $min_ls) {
                        $min_ls = $this->tasks[$succ_id]['ls'];
                    }
                }
                $task['lf'] = $min_ls - 1;
            }
            $task['ls'] = $task['lf'] - $task['duration'] + 1;
        }
    }

    private function calculate_slack() {
        foreach ($this->tasks as &$task) {
            $task['slack'] = $task['lf'] - $task['ef'];
        }
    }

    private function find_critical_path() {
        $this->critical_path = [];
        foreach ($this->tasks as $task) {
            if ($task['slack'] == 0) {
                $this->critical_path[] = $task['id'];
            }
        }
    }

    public function get_critical_path() {
        return $this->critical_path;
    }
}
?>