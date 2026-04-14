document.querySelectorAll('[data-scroll-target]').forEach((button) => {
  button.addEventListener('click', () => {
    const target = document.querySelector(button.getAttribute('data-scroll-target'));
    if (target) {
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
