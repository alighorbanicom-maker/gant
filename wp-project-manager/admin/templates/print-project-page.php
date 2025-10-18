<?php
/**
 * The template for displaying the printable project report.
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin/templates
 * @author     Jules
 */
?>
<!DOCTYPE html>
<html lang="fa-IR">
<head>
    <meta charset="UTF-8">
    <title>گزارش پروژه: <?php echo esc_html($project->project_name); ?></title>
    <link rel="stylesheet" href="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/css/wpm-print-styles.css'; ?>" type="text/css" media="all">
    <script src="https://www.gstatic.com/charts/loader.js"></script>
    <script>
        // JS for Gantt chart and print will be here
    </script>
</head>
<body dir="rtl">

    <div class="print-container">
        <header class="print-header">
            <div class="header-right">
                <h1>گزارش پروژه: <?php echo esc_html($project->project_name); ?></h1>
                <p><strong>نام ثبت کننده:</strong> <?php echo esc_html($user->display_name); ?></p>
                 <p><strong>تاریخ ثبت:</strong> <?php echo esc_html(wpm_gregorian_to_jalali($project->creation_date)); ?></p>
            </div>
            <div class="header-left">
                <h2>دکتر علی قربانی</h2>
                <p><strong>تاریخ پرینت:</strong> <span id="print-date"></span></p>
                <button id="print-button" onclick="window.print();">چاپ / ذخیره PDF</button>
            </div>
        </header>

        <main>
            <section class="gantt-section">
                <h2>نمودار گانت</h2>
                <div id="gantt_chart_div" style="width: 100%; height: 500px;"></div>
            </section>

            <section class="analysis-section">
                <h2>تحلیل پروژه</h2>
                <div class="analysis-content">
                    <?php echo wpautop( $project_analysis ); ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('print-date').textContent = new Date().toLocaleDateString('fa-IR');

        var wpm_gantt_data = <?php echo json_encode($gantt_data); ?>;

        google.charts.load('current', {'packages':['gantt']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            data.addColumn('string', 'Task ID');
            data.addColumn('string', 'Task Name');
            data.addColumn('string', 'Resource');
            data.addColumn('date', 'Start Date');
            data.addColumn('date', 'End Date');
            data.addColumn('number', 'Duration');
            data.addColumn('number', 'Percent Complete');
            data.addColumn('string', 'Dependencies');

            var tasks = wpm_gantt_data.tasks;
            var projectStartDate = new Date(wpm_gantt_data.project_start_date);
            var criticalPath = wpm_gantt_data.critical_path.map(String);

            var rows = tasks.map(task => {
                var startDate = new Date(projectStartDate);
                startDate.setDate(startDate.getDate() + task.es - 1);
                var endDate = new Date(projectStartDate);
                endDate.setDate(endDate.getDate() + task.ef - 1);
                var resource = criticalPath.includes(task.id.toString()) ? 'Critical' : null;

                return [
                    task.id.toString(),
                    task.name,
                    resource,
                    startDate,
                    endDate,
                    null,
                    task.progress,
                    Array.isArray(task.dependencies) ? task.dependencies.join(',') : ''
                ];
            });

            data.addRows(rows);

            var options = {
                height: (rows.length * 40) + 50,
                gantt: {
                    criticalPathEnabled: true,
                    criticalPathStyle: { stroke: '#e64a19', strokeWidth: 5 },
                }
            };

            var chart = new google.visualization.Gantt(document.getElementById('gantt_chart_div'));
            chart.draw(data, options);
        }
    </script>

</body>
</html>