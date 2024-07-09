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
});
