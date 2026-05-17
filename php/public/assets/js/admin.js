document.addEventListener('DOMContentLoaded', () => {
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar__nav a').forEach(link => {
    if (link.getAttribute('href') === currentPath) {
      link.style.background = 'rgba(99,102,241,0.22)';
      link.style.color = '#fff';
    }
  });

  document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', () => {
      const btn = form.querySelector('button[type="submit"]');
      if (btn) {
        btn.disabled = true;
        btn.dataset.original = btn.textContent;
        btn.textContent = 'Processing...';
        setTimeout(() => {
          btn.disabled = false;
          btn.textContent = btn.dataset.original || 'Submit';
        }, 2000);
      }
    });
  });
});
