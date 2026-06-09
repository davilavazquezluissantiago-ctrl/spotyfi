// Elementos para alternar pantallas (Login / Registro)
const loginBox = document.getElementById('login-box');
const registerBox = document.getElementById('register-box');
const goToRegister = document.getElementById('go-to-register');
const goToLogin = document.getElementById('go-to-login');

// Elementos de Roles en Login
const btnLoginCliente = document.getElementById('btn-login-cliente');
const btnLoginAdmin = document.getElementById('btn-login-admin');
const groupCodigoAdmin = document.getElementById('group-codigo-admin');
const loginRol = document.getElementById('login-rol');

// Elementos de Roles en Registro
const btnRegCliente = document.getElementById('btn-reg-cliente');
const btnRegAdmin = document.getElementById('btn-reg-admin');
const registerAdminFields = document.getElementById('register-admin-fields');
const registerRol = document.getElementById('register-rol');

// INTERCAMBIO DE PANTALLAS
goToRegister.addEventListener('click', (e) => {
    e.preventDefault();
    loginBox.style.display = 'none';
    registerBox.style.display = 'block';
});

goToLogin.addEventListener('click', (e) => {
    e.preventDefault();
    registerBox.style.display = 'none';
    loginBox.style.display = 'block';
});

// ROLES EN LOGIN
btnLoginAdmin.addEventListener('click', () => {
    btnLoginAdmin.classList.add('active');
    btnLoginCliente.classList.remove('active');
    groupCodigoAdmin.style.display = 'block';
    loginRol.value = 'admin';
});

btnLoginCliente.addEventListener('click', () => {
    btnLoginCliente.classList.add('active');
    btnLoginAdmin.classList.remove('active');
    groupCodigoAdmin.style.display = 'none';
    loginRol.value = 'cliente';
});

// ROLES EN REGISTRO
btnRegAdmin.addEventListener('click', () => {
    btnRegAdmin.classList.add('active');
    btnRegCliente.classList.remove('none'); // Reset class
    btnRegCliente.classList.remove('active');
    registerAdminFields.style.display = 'block';
    registerRol.value = 'admin';
    document.getElementById('reg-disquera').required = true;
    document.getElementById('reg-codigo').required = true;
});

btnRegCliente.addEventListener('click', () => {
    btnRegCliente.classList.add('active');
    btnRegAdmin.classList.remove('active');
    registerAdminFields.style.display = 'none';
    registerRol.value = 'cliente';
    document.getElementById('reg-disquera').required = false;
    document.getElementById('reg-codigo').required = false;
});