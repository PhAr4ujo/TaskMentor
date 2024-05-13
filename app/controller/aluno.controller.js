"use strict"
const PATH_API = "../../../TaskMentor/app/model/aluno.php";
import { pegarImagens } from "./imageProfile.controller.js";

export function login() {
  const formLogin = document.getElementById("form-login");

  formLogin.addEventListener("submit", (event) => {
    event.preventDefault();

    const dados = {
      email: document.getElementById("input-email").value,
      senha: document.getElementById("input-senha").value,
      operation: "login",
    }

    enviarDados(dados);

    async function enviarDados(dados) {
      const request = await fetch(PATH_API, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(dados)
      });

      const response = await request.json();

      tratarDados(response)
    }

    function tratarDados({ type, message, token }) {
      if (type === "error") {
        alert(type, message);
        return;
      }

      if (armazenarToken(token)) {
        realizarLogin();
      } else {
        alert("error", "Não foi possível realizar o login");
      }
    }
  });

  const buttonVerSenha = document.getElementById("button-ver-senha");
  buttonVerSenha.addEventListener("click", (e) => {
    e.preventDefault()
    let inputSenha = document.getElementById("input-senha");
    mostrarSenha(inputSenha, e.target);
  });

  async function realizarLogin() {
    await alertTime("success", "Login realizado com sucesso", "Você será redirecionado para tela inicial", 2000);
    window.location.href = "../../../TaskMentor/index.html";
  }

  function armazenarToken(token) {
    try {
      localStorage.setItem("tokenAluno", token);
      return true;
    } catch (e) {
      return false;
    }
  }
}

export async function cadastro() {
  const dados = {};
  const sessaoCadastroInicial = document.getElementById("section-cadastro-inicial");
  const sessaoCadastroSenha = document.getElementById("section-cadastro-senha");
  const sessaoCadastroFinal = document.getElementById("section-cadastro-final");

  const buttonPegarNomeEEmail = document.getElementById("button-cadastro-inicial");

  buttonPegarNomeEEmail.addEventListener("click", (e) => {
    e.preventDefault();
    const nome = document.getElementById("input-nome").value;
    const email = document.getElementById("input-email").value;

    if (!validarNome(nome)) {
      return alert("error", "Nome de usuário inválido");
    }

    dados.nome = nome;
    dados.email = email;

    sessaoCadastroInicial.classList.replace("block", "hidden");
    sessaoCadastroSenha.classList.replace("hidden", "block");
  });

  const buttonVerSenha = document.getElementById("button-ver-senha");
  buttonVerSenha.addEventListener("click", (e) => {
    e.preventDefault()
    let inputSenha = document.getElementById("input-senha");
    mostrarSenha(inputSenha, e.target);
  });

  const buttonPegarSenha = document.getElementById("button-cadastro-senha");
  buttonPegarSenha.addEventListener("click", (e) => {
    e.preventDefault();

    const senha = document.getElementById("input-senha").value;
    const confirmarSenha = document.getElementById("input-confirmar-senha").value;

    if (!validarSenha(senha)) {
      return alert("error", "Senha deve conter números e letras e deve ter pelo menos 8 caracteres");
    }

    if (senha !== confirmarSenha) {
      return alert("error", "As senhas estão diferentes");
    }

    dados.senha = senha;
    dados.confirmarSenha = confirmarSenha;

    sessaoCadastroSenha.classList.replace("block", "hidden");
    sessaoCadastroFinal.classList.replace("hidden", "block");
  })

  const buttonVoltarPaginaInicial = document.getElementById("button-voltar-sessao-inicial");
  buttonVoltarPaginaInicial.addEventListener("click", (e) => {
    e.preventDefault();
    sessaoCadastroInicial.classList.replace("hidden", "block");
    sessaoCadastroSenha.classList.replace("block", "hidden");
  });

  const buttonVoltarPaginaSenha = document.getElementById("button-voltar-sessao-senha");
  buttonVoltarPaginaSenha.addEventListener("click", (e) => {
    e.preventDefault();
    sessaoCadastroSenha.classList.replace("hidden", "block");
    sessaoCadastroFinal.classList.replace("block", "hidden");
  });

  let container = document.getElementById("section-imagens-perfil");

  const imagensPerfil = await pegarImagens();
  inserirImagens(imagensPerfil, container);

  const imagens = document.querySelectorAll(".imagem-perfil");
  imagens.forEach(imagem => imagem.addEventListener("click", () => {

    imagens.forEach(imagem => {
      imagem.classList.add("bg-zinc-200");
      imagem.classList.add("dark:bg-[#181818]");
      imagem.classList.remove("bg-tm-purple");
    });

    imagem.classList.remove("bg-zinc-200");
    imagem.classList.remove("dark:bg-[#181818]");
    imagem.classList.add("bg-tm-purple");
    const idImageProfile = imagem.dataset.id;
    dados.idImageProfile = idImageProfile;
  }));

  const buttonSubmitCadastro = document.getElementById("button-cadastro-final");
  buttonSubmitCadastro.addEventListener("click", (e) => {
    e.preventDefault();

    enviarDados(dados);
  });

  async function enviarDados(dados) {
    dados.operation = "register";
    const request = await fetch(PATH_API, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(dados)
    });

    const response = await request.json();

    tratarDados(response);
  }

  function tratarDados(dados) {
    if(dados.type == "error") {
      sessaoCadastroFinal.classList.replace("block", "hidden");
      sessaoCadastroInicial.classList.replace("hidden", "block");
      alert(dados.type, dados.message); 
      return;
    }

    realizarCadastro();
  }
  
  async function realizarCadastro() {
    await alertTime("success", "Cadastro realizado com suceso", "Aguarde, você será redirecionado para tela de login", 3000);
    window.location.href = "../../../TaskMentor/login.html";
  }
  
}






function inserirImagens(imagens, container) {
  container.innerHTML = "";

  imagens.forEach(imagem => {
    let imagePath = imagem.path;
    imagePath = imagePath.replace(/\\/g, "/");
    imagePath = imagePath.replace("C:/xampp/htdocs/", "/");
    container.innerHTML += `
    <div data-id="${imagem.idImageProfile}" class="imagem-perfil hover:opacity-60 hover:cursor-pointer flex flex-col items-center p-1 bg-zinc-200 dark:bg-[#181818] text-white rounded-md gap-1 w-28">
      <div class="flex items-center rounded-full justify-center bg-[url(${imagePath})] bg-cover bg-center border-solid border-2 border-tm-white w-24 h-24">
      </div>
      <h3 class="text-[10px] dark:text-white text-zinc-700">${imagem.nome}</h3>
    </div>
    `;
  })
}

function mostrarSenha(inputSenha, icon) {
  const button = icon.parentElement;

  if (inputSenha.type === "password") {
    button.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" class='w-[24px] h-[24px]' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
    `;
    return inputSenha.type = "text";
  }

  button.innerHTML = `
  <svg xmlns="http://www.w3.org/2000/svg" class='w-[24px] h-[24px]' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-off"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
  `;
  return inputSenha.type = "password";
}

function alertTime(tipo, titulo, mensagem, time) {
  return new Promise((resolve) => {
    Swal.fire({
      icon: tipo,
      title: titulo,
      text: mensagem,
      timer: time,
      timerProgressBar: true,
      allowOutsideClick: false,
      allowEscapeKey: false,
      allowEnterKey: false,
      didOpen: () => {
        Swal.showLoading();
        ajustarAlturaDoAlerta();
      },
      didClose: () => {
        resolve();
      }
    })
  })
}

function alert(tipo, mensagem) {
  Swal.fire({
    icon: tipo,
    title: tipo == "success" ? "Sucesso" : "Erro",
    html: `<p class='text-center'>${mensagem}</p>`,
    customClass: {
      confirmButton: "btn-alert"
    },
    buttonsStyling: false,
  });
  ajustarAlturaDoAlerta()
}

function ajustarAlturaDoAlerta() {
  return document.body.classList.remove("swal2-height-auto");
}

function validarNome(nome) {
  // Expressão regular para verificar se o nome tem pelo menos 3 caracteres
  // e contém apenas letras, números, underscores (_) e traços (-).
  const regex = /^[a-zA-Z0-9_ -]{3,}$/;

  return regex.test(nome);
}

function validarSenha(senha) {
  // Expressão regular para verificar se a senha contém pelo menos uma letra e um número
  // e tem mais de 6 dígitos.
  const regex = /^(?=.*[a-zA-Z])(?=.*\d).{7,}$/;

  return regex.test(senha);
}

