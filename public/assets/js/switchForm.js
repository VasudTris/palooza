//! voor de switch tussen de login en account aanmaak form
document.getElementById('toggle-button').addEventListener('click', function () {
    var loginForm = document.getElementById('login-form');
    var registerForm = document.getElementById('register-form');

    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
        this.textContent = 'Geen account?';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
        this.textContent = 'Al een account?';
    }
});