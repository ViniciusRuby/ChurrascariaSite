document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('form').addEventListener('submit', function (event) {
        var cpfInput = document.getElementById('cpf');
        var telefoneInput = document.querySelector('input[name="fone"]');

        if (cpfInput.value.length !== 14) {
            alert('Campo de CPF não preenchido.');
            event.preventDefault();
        }

        if (telefoneInput.value.length !== 15) {
            alert('Campo de Telefone não preenchido.');
            event.preventDefault();
        }
    });
});
