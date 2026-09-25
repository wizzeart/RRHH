document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#docs-table tbody');
  const modal = document.getElementById('sign-modal');
  const form = document.getElementById('sign-form');
  const alerts = document.getElementById('alerts');

  function showAlert(msg, type='info'){
    alerts.innerHTML = `<div class="alert ${type}">${msg}</div>`;
    setTimeout(()=> alerts.innerHTML = '', 5000);
  }

  async function loadDocs(){
    try{
      const res = await fetch('/api/list-documentos.php');
      const data = await res.json();
      tbody.innerHTML = '';
      data.forEach(d => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${d.id}</td>
          <td>${d.nombre}</td>
          <td>${d.path}</td>
          <td>${d.fecha_modificado}</td>
          <td>${d.firmado == 1 ? 'Sí' : 'No'}</td>
          <td><button data-id="${d.id}" data-path="${d.path}" class="btn-sign">Firmar</button></td>
        `;
        tbody.appendChild(tr);
      });
      attachButtons();
    }catch(e){
      showAlert('Error cargando documentos: ' + e.message, 'error');
    }
  }

  function attachButtons(){
    document.querySelectorAll('.btn-sign').forEach(btn => {
      btn.addEventListener('click', (e)=>{
        const id = e.target.dataset.id;
        document.getElementById('doc-id').value = id;
        modal.style.display = 'block';
      });
    });
  }

  document.getElementById('btn-cancel').addEventListener('click', ()=>{
    modal.style.display = 'none';
  });

  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const id = parseInt(document.getElementById('doc-id').value);
    const signer = document.getElementById('signer').value.trim();
    const reason = document.getElementById('reason').value.trim();
    if (!signer) { showAlert('Ingrese el nombre del firmante','warning'); return; }
    showAlert('Firmando...','info');
    try{
      const res = await fetch('/api/sign-documento.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ id, signer, reason })
      });
      const json = await res.json();
      if (json.status == 1){
        showAlert('Documento firmado correctamente','success');
        modal.style.display = 'none';
        loadDocs();
      } else {
        showAlert('Error firmando: ' + (json.error||JSON.stringify(json)),'error');
      }
    }catch(err){
      showAlert('Error firmando: ' + err.message,'error');
    }
  });

  loadDocs();
});
