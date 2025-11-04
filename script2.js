<<<<<<< HEAD
const hamburguer = document.querySelector('.hamburguer');
    const navLinks = document.querySelector('.nav_links');

    hamburguer.addEventListener('click', () => {
      hamburguer.classList.toggle('active');
      navLinks.classList.toggle('active');
    });

    document.querySelectorAll('.li_nav').forEach(n =>
      n.addEventListener('click', () => {
        hamburguer.classList.remove('active');
        navLinks.classList.remove('active');
      })
=======
const hamburguer = document.querySelector('.hamburguer');
    const navLinks = document.querySelector('.nav_links');

    hamburguer.addEventListener('click', () => {
      hamburguer.classList.toggle('active');
      navLinks.classList.toggle('active');
    });

    document.querySelectorAll('.li_nav').forEach(n =>
      n.addEventListener('click', () => {
        hamburguer.classList.remove('active');
        navLinks.classList.remove('active');
      })
>>>>>>> e6ccc33e35b8ae902cdbae75d8f0dba6db4cc411
    );