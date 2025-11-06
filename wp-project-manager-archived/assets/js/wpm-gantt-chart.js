/**
 * Handles rendering the Gantt chart using Google Charts.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin/assets/js
 */

google.charts.load('current', {'packages':['gantt']});
google.charts.setOnLoadCallback(drawChart);

function daysToMilliseconds(days) {
    return days * 24 * 60 * 60 * 1000;
}

function drawChart() {

    var data = new google.visualization.DataTable();
    data.addColumn('string', 'Task ID');
    data.addColumn('string', 'Task Name');
    data.addColumn('string', 'Resource'); // Resource column for critical path styling
    data.addColumn('date', 'Start Date');
    data.addColumn('date', 'End Date');
    data.addColumn('number', 'Duration');
    data.addColumn('number', 'Percent Complete');
    data.addColumn('string', 'Dependencies');

    // wpm_gantt_data is localized from PHP
    var tasks = wpm_gantt_data.tasks;
    var projectStartDate = new Date(wpm_gantt_data.project_start_date);
    var criticalPath = wpm_gantt_data.critical_path;

    var rows = [];
    for (var i = 0; i < tasks.length; i++) {
        var task = tasks[i];
        var startDate = new Date(projectStartDate.getTime());
        startDate.setDate(startDate.getDate() + task.es - 1);

        var endDate = new Date(projectStartDate.getTime());
        endDate.setDate(endDate.getDate() + task.ef - 1);

        var resource = criticalPath.includes(task.id) ? 'Critical' : null;

        rows.push([
            task.id.toString(),
            task.name,
            resource, // Use resource to identify critical tasks
            startDate,
            endDate,
            null, // Duration in milliseconds (null lets chart calculate it)
            task.progress,
            task.dependencies.join(',')
        ]);
    }

    data.addRows(rows);

    var options = {
        height: 400,
        gantt: {
            trackHeight: 30,
            criticalPathEnabled: true,
            criticalPathStyle: {
                stroke: '#e64a19',
                strokeWidth: 5
            },
            arrow: {
                angle: 100,
                width: 2,
                color: 'grey',
                radius: 0
            }
        }
    };

    var chart = new google.visualization.Gantt(document.getElementById('gantt_chart_div'));

    chart.draw(data, options);
}