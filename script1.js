const usuarioLogado = JSON.parse(localStorage.getItem('usuarioLogado'));
if (!usuarioLogado) {
  // not logged in → redirect to login page
  window.location.href = 'login.php';
}
