document.querySelectorAll('form[data-confirm]').forEach(form => {
  form.addEventListener('submit', event => {
    if (!window.confirm(form.dataset.confirm)) event.preventDefault();
  });
});
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));

document.querySelectorAll('[data-show-password]').forEach(control => control.addEventListener('change', () => {
  control.form.querySelector('input[name="clave"]').type = control.checked ? 'text' : 'password';
}));


// Menú de pantallas pequeñas. Las acciones y formularios de la aplicación no cambian.
(() => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const sidebar = document.getElementById('sidebar');
  const workspace = document.querySelector('.workspace');
  if (!toggle || !sidebar) return;
  const mobile = window.matchMedia('(max-width: 900px)');
  function closeMenu(restoreFocus = true) {
    document.body.classList.remove('nav-open');
    toggle.setAttribute('aria-expanded', 'false');
    workspace.inert = false;
    if (restoreFocus) toggle.focus();
  }
  toggle.addEventListener('click', () => {
    if (!mobile.matches) return;
    document.body.classList.add('nav-open');
    toggle.setAttribute('aria-expanded', 'true');
    workspace.inert = true;
    sidebar.querySelector('[data-menu-close]').focus();
  });
  document.querySelectorAll('[data-menu-close]').forEach(button => button.addEventListener('click', () => closeMenu()));
  document.addEventListener('keydown', event => { if (event.key === 'Escape' && document.body.classList.contains('nav-open')) closeMenu(); });
  sidebar.querySelectorAll('nav a').forEach(link => link.addEventListener('click', () => closeMenu(false)));
  mobile.addEventListener('change', () => closeMenu(false));
})();
