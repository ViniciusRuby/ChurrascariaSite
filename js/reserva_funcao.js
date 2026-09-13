document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('quantidade_mesas').addEventListener('input', function() {
        var qtdMesas = parseInt(this.value);
        var valorPorMesa = 50;
        var valorTotal = qtdMesas * valorPorMesa;
        document.getElementById('valor_total').textContent = valorTotal.toFixed(2);
    });

    // Data mínima para o input de data (hoje)
    document.getElementById('data_reserva').min = new Date().toISOString().split('T')[0];

    // Função para excluir domingos
    document.getElementById('data_reserva').addEventListener('input', function() {
        var selectedDate = new Date(this.value);
        if (selectedDate.getDay() === 0) { // Domingo
            alert("Domingos não são permitidos. Selecione outro dia.");
            this.value = '';
        }
    });
});

function updateValue(val) {
    document.getElementById('valor_quantidade_mesas').textContent = val;
}
