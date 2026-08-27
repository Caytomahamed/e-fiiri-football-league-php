console.log('hello');
// alert('hello');

document.addEventListener('DOMContentLoaded', function () {
  const menuBtns = document.querySelectorAll('.menu-btn');
  const tabContents = document.querySelectorAll('.tab-content');

  menuBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      // Remove 'active' class from all buttons and content sections
      menuBtns.forEach((item) => item.classList.remove('active'));
      tabContents.forEach((item) => item.classList.remove('active'));

      // Add 'active' class to the clicked button and corresponding content section
      btn.classList.add('active');
      const target = btn.getAttribute('data-target');
      document.getElementById(target).classList.add('active');
    });
  });

  // Generic modal popups: any element with [data-modal-open="id"] opens
  // #id; anything with [data-modal-close] or the overlay background closes
  // the modal it's inside.
  document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
      const modal = document.getElementById(trigger.getAttribute('data-modal-open'));
      if (modal) {
        modal.classList.add('open');
      }
    });
  });

  document.querySelectorAll('.modal-overlay').forEach((overlay) => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.remove('open');
      }
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach((btn) => {
    btn.addEventListener('click', () => {
      btn.closest('.modal-overlay').classList.remove('open');
    });
  });
});

// Fills the single shared "Edit" modal's fields from a row's data-*
// attributes, then opens it. Used by Teams and Competitions edit buttons.
function openEditModal(modalId, fields) {
  Object.keys(fields).forEach((name) => {
    const el = document.querySelector('#' + modalId + ' [name="' + name + '"]');
    if (el) {
      el.value = fields[name];
    }
  });
  document.getElementById(modalId).classList.add('open');
}
