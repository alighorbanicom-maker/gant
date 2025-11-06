<?php
/**
 * Generates automated analysis text for a project.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/includes
 */

class WPM_Project_Analyzer {

    private $tasks;
    private $critical_path_ids;
    private $project_duration = 0;

    public function __construct(array $calculated_tasks, array $critical_path_ids) {
        $this->tasks = $calculated_tasks;
        $this->critical_path_ids = $critical_path_ids;
        $this->calculate_project_duration();
    }

    private function calculate_project_duration() {
        foreach ($this->tasks as $task) {
            if ($task['lf'] > $this->project_duration) {
                $this->project_duration = $task['lf'];
            }
        }
    }

    public function generate_analysis() {
        $analysis_p1 = $this->generate_status_paragraph();
        $analysis_p2 = $this->generate_recommendation_paragraph();

        return $analysis_p1 . "\n\n" . $analysis_p2;
    }

    private function generate_status_paragraph() {
        $total_tasks = count($this->tasks);
        $critical_tasks_count = count($this->critical_path_ids);

        $total_weighted_progress = 0;
        $total_duration = 0;
        foreach ($this->tasks as $task) {
            $total_weighted_progress += $task['progress'] * $task['duration'];
            $total_duration += $task['duration'];
        }
        $overall_progress = ($total_duration > 0) ? round($total_weighted_progress / $total_duration) : 0;

        $text = "این پروژه با مجموع <strong>{$total_tasks} فعالیت</strong> برای مدت زمان <strong>{$this->project_duration} روز</strong> برنامه‌ریزی شده است. ";
        $text .= "مسیر بحرانی پروژه شامل <strong>{$critical_tasks_count} فعالیت</strong> کلیدی است که هرگونه تاخیر در آن‌ها مستقیماً بر تاریخ اتمام پروژه تاثیرگذار خواهد بود. ";
        $text .= "در حال حاضر، پیشرفت کلی پروژه معادل <strong>{$overall_progress}%</strong> برآورد می‌شود. ";
        // A more complex logic can be added here to check if the project is behind schedule.
        $text .= "بررسی دقیق‌تر فعالیت‌های بحرانی برای ارزیابی وضعیت واقعی پروژه ضروری است.";

        return $text;
    }

    private function generate_recommendation_paragraph() {
        $critical_low_progress_tasks = [];
        foreach ($this->tasks as $task) {
            if (in_array($task['id'], $this->critical_path_ids) && $task['progress'] < 75) {
                $critical_low_progress_tasks[] = "<strong>" . $task['name'] . "</strong> (پیشرفت: " . $task['progress'] . "%)";
            }
        }

        if (empty($critical_low_progress_tasks)) {
            return "توصیه: وضعیت پروژه در مسیر بحرانی مطلوب به نظر می‌رسد. پیشنهاد می‌شود نظارت مستمر بر فعالیت‌های کلیدی ادامه یابد تا از بروز هرگونه تاخیر جلوگیری شود. همچنین، می‌توان از منابع فعالیت‌های غیربحرانی با شناوری بالا برای تسریع سایر بخش‌ها استفاده نمود.";
        }

        $tasks_list_str = implode('، ', $critical_low_progress_tasks);
        $text = "توصیه: توجه ویژه به فعالیت‌های بحرانی با پیشرفت کم ضروری است. در حال حاضر، فعالیت‌های " . $tasks_list_str . " نیاز به تمرکز و تخصیص منابع بیشتری دارند تا از تاخیر در برنامه کلی پروژه جلوگیری شود. پیشنهاد می‌شود اقدامات اصلاحی لازم برای سرعت بخشیدن به این فعالیت‌ها در اولویت قرار گیرد.";

        return $text;
    }
}
?>