document.addEventListener('DOMContentLoaded', function () {
  console.log('Agenda de Estudos loaded');

  document.querySelectorAll('.btn-start').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      fetch('../actions/start_task.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'task_id=' + encodeURIComponent(id)
      }).then(r => r.json()).then(data => {
        if (data.ok) location.reload();
        else alert(data.error || 'Erro ao iniciar tarefa');
      });
    });
  });

  document.querySelectorAll('.btn-toggle').forEach(btn => {
    btn.addEventListener('click', function () {
      const id = this.dataset.id;
      const action = this.dataset.action; // pause, stop, resume
      fetch('../actions/toggle_task.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'task_id=' + encodeURIComponent(id) + '&action=' + encodeURIComponent(action)
      }).then(r => r.json()).then(data => {
        if (data.ok) location.reload();
        else alert(data.error || 'Erro ao alterar estado');
      });
    });
  });
});