// Ao carregar, faz o fade-in
window.addEventListener("DOMContentLoaded", () => {
  const page = document.getElementById("page");
  // forçar reflow antes de adicionar a classe
  void page.offsetWidth;
  page.classList.add("visible");
});

// Função genérica para navegar com fade-out
function fadeNavigate(url) {
  const page = document.getElementById("page");
  page.classList.remove("visible");
  setTimeout(() => {
    window.location.href = url;
  }, 50); // igual ao tempo de transition
}

// Botões index.php
const brr = document.getElementById("brr");
if (brr)
  brr.addEventListener("click", e => {
    e.preventDefault();
    fadeNavigate("brr.php");
  });

// Botão voltar em brr.php
const back = document.getElementById("voltar");
if (back)
  back.addEventListener("click", e => {
    e.preventDefault();
    fadeNavigate("index.php");
  });

const firstDateField = document.getElementById("data-field-1");
if (firstDateField) {
  firstDateField.classList.add("visible");
}
