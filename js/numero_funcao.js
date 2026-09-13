// Função para formatar o número de telefone enquanto o usuário digita
function formatarTelefone(campoTexto) {
    // Remove todos os caracteres não numéricos do valor do telefone
    var telefone = campoTexto.value.replace(/\D/g, '');

    // Formato para telefone fixo (8 dígitos) ou celular (9 dígitos)
    if (telefone.length <= 10) {
        campoTexto.value = mascaraTelefone(telefone); // Formato para telefone com 8 ou 9 dígitos
    } else {
        campoTexto.value = mascaraCelular(telefone); // Formato para celular com 11 dígitos
    }
}

// Máscara para telefone fixo com 8 dígitos
function mascaraTelefone(valor) {
    return valor.replace(/(\d{2})(\d{4})(\d{4})/, "($1) $2-$3");
}

// Máscara para celular com 9 dígitos
function mascaraCelular(valor) {
    return valor.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
}
