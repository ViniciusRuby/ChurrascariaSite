function formatarCampo(campoTexto) {
    if (campoTexto.id === 'cpf') {
        campoTexto.value = mascaraCpf(campoTexto.value);
    } else if (campoTexto.id === 'fone') {
        campoTexto.value = mascaraFone(campoTexto.value);
    }
}

function mascaraCpf(cpf) {
    cpf = cpf.replace(/\D/g, ''); // Remove caracteres não numéricos
    cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Aplica a primeira máscara
    cpf = cpf.replace(/(\d{3})(\d)/, "$1.$2"); // Aplica a segunda máscara
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, "$1-$2"); // Aplica a terceira máscara
    return cpf;
}

function mascaraFone(fone) {
    fone = fone.replace(/\D/g, ''); // Remove caracteres não numéricos
    fone = fone.replace(/(\d{2})(\d)/, "($1) $2"); // Aplica a primeira máscara
    fone = fone.replace(/(\d{4})(\d)/, "$1-$2"); // Aplica a segunda máscara
    return fone;
}

function retirarFormatacao(campoTexto) {
    campoTexto.value = campoTexto.value.replace(/[\.\-\(\)\/\s]/g, "");
}
