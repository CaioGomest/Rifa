<style>
  .alerta_customizado {
    position: fixed;
    top: 30px;
    right: 30px;
    min-width: 300px;
    max-width: 400px;
    padding: 18px 28px 18px 20px;
    border-radius: 14px;
    color: #fff;
    font-size: 1.05rem;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
    z-index: 9999;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.3s, top 0.3s;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .alerta_customizado.success {
    background: #2ecc40eb;
  }

  .alerta_customizado.error {
    background: #e74c3c;
  }

  .alerta_customizado.show {
    opacity: 1;
    pointer-events: auto;
    top: 50px;
  }

  .alerta_customizado .close-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 1.2em;
    cursor: pointer;
    margin-left: 18px;
  }
</style>

<script>

  function showCustomAlert(message, type = 'success', duration = 3500) {
    const oldAlert = document.querySelector('.alerta_customizado');
    if (oldAlert) oldAlert.remove();

    // Cria o elemento do alerta
    const alertDiv = document.createElement('div');
    alertDiv.className = `alerta_customizado ${type}`;
    alertDiv.innerHTML = `
      <span>${message}</span>
      <button class="close-btn" onclick="this.parentElement.remove()">×</button>
    `;

    document.body.appendChild(alertDiv);

    // Mostra o alerta
    setTimeout(() => {
      alertDiv.classList.add('show');
    }, 100);

    // Remove automaticamente após o tempo
    setTimeout(() => {
      alertDiv.classList.remove('show');
      setTimeout(() => alertDiv.remove(), 300);
    }, duration);
  }

  // Exibe alerta automaticamente se houver parametros na URL
  (function () {
    try {
      const params = new URLSearchParams(window.location.search);
      const msg = params.get('alert_message');
      if (!msg) return;
      const type = params.get('alert_type') || 'success';
      const durationStr = params.get('alert_duration');
      const duration = durationStr ? parseInt(durationStr, 10) : 3500;
      showCustomAlert(msg, type, isNaN(duration) ? 3500 : duration);
    } catch (e) {
      // silencia erros de URL
    }
  })();
</script>