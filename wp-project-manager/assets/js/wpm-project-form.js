/**
 * Handles the dynamic table for adding/removing tasks in the project form.
 *
 * @link       https://dralighorbani.com/
 * @since      1.0.0
 *
 * @package    Wp_Project_Manager
 * @subpackage Wp_Project_Manager/admin/assets/js
 */

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('tasks-container');
    if (!container) return;

    const addBtn = document.getElementById('add-task-btn');
    let taskIdCounter = container.getElementsByTagName('tr').length + 1;

    function getNextId() {
        let maxId = 0;
        const rows = container.querySelectorAll('tr');
        rows.forEach(row => {
            const idSpan = row.querySelector('.task-id');
            if (idSpan) {
                const id = parseInt(idSpan.textContent, 10);
                if (id > maxId) {
                    maxId = id;
                }
            }
        });
        return maxId + 1;
    }

    function createRow() {
        const newId = getNextId();
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><span class="task-id">${newId}</span></td>
            <td><input type="text" name="tasks[new_${newId}][name]" class="large-text" required /></td>
            <td><input type="number" name="tasks[new_${newId}][duration]" min="1" value="1" class="small-text" required /></td>
            <td><input type="text" name="tasks[new_${newId}][dependencies]" placeholder="مثال: 1,2" /></td>
            <td><input type="number" name="tasks[new_${newId}][progress]" min="0" max="100" value="0" class="small-text" /></td>
            <td><button type="button" class="button button-link-delete remove-task-btn">حذف</button></td>
        `;
        container.appendChild(row);
    }

    if (addBtn) {
        addBtn.addEventListener('click', createRow);
    }

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-task-btn')) {
            e.target.closest('tr').remove();
        }
    });
});