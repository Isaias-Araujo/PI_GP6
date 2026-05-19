// theme.js - Alterna modo claro/escuro com botão flutuante (apenas ícone) - comentários em português
(function(){
  const body = document.body;
  const btn = document.createElement('button');
  btn.id = 'theme-toggle-float';
  btn.setAttribute('aria-label', 'Alternar tema');
  Object.assign(btn.style, {
    position: 'fixed',
    top: '85px',
    right: '20px',
    zIndex: '1050',
    width: '46px',
    height: '46px',
    borderRadius: '50%',
    border: 'none',
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontSize: '22px',
    boxShadow: '0 6px 18px rgba(0,0,0,0.15)',
    transition: 'all 0.3s ease'
  });

  document.addEventListener('DOMContentLoaded', () => {
    document.body.appendChild(btn);
    const stored = localStorage.getItem('agenda_theme') || 'light';
    applyTheme(stored);
    btn.addEventListener('click', () => {
      const cur = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
      const next = cur === 'dark' ? 'light' : 'dark';
      localStorage.setItem('agenda_theme', next);
      applyTheme(next);
    });
  });

  function applyTheme(mode){
    if(mode === 'dark'){
      body.classList.add('dark-mode');
      body.classList.remove('light-mode');
      btn.textContent = '☀️';
      btn.style.background = '#1e293b'; // fundo escuro
      btn.style.color = '#fff';
    } else {
      body.classList.add('light-mode');
      body.classList.remove('dark-mode');
      btn.textContent = '🌙';
      btn.style.background = '#dbeafe'; // azul claro
      btn.style.color = '#1e293b';
    }
  }
})();