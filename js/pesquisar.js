function searchData() {
    const search = document.getElementById('pesquisar').value;
    // Codifica o valor da pesquisa para uso em uma URL
    const encodedSearch = encodeURIComponent(search);
    window.location.href = 'registro_cliente.php?search=' + encodedSearch;
}

var search = document.getElementById('pesquisar');
search.addEventListener("keydown", function (event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Evita o envio do formulário padrão
        searchData(); // Chama a função de pesquisa
    }
});
