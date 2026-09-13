function formatarCampo(campoTexto) {
    if (campoTexto.id === 'cnpj') {
        campoTexto.value = mascaraCnpj(campoTexto.value);
    }
}

function mascaraCnpj(cnpj) {
    cnpj = cnpj.replace(/\D/g, '');
    cnpj = cnpj.replace(/^(\d{2})(\d)/, "$1.$2");
    cnpj = cnpj.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
    cnpj = cnpj.replace(/\.(\d{3})(\d)/, ".$1/$2");
    cnpj = cnpj.replace(/(\d{4})(\d)/, "$1-$2");
    return cnpj;
}

function retirarFormatacao(campoTexto) {
    campoTexto.value = campoTexto.value.replace(/[\.\-\(\)\/\s]/g, "");
}
