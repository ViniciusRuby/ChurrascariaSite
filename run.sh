#!/bin/bash

# 1. Ativa o ambiente virtual do Python
source venv/bin/activate

# 2. Inicia o servidor Python (Flask/SQLite) em segundo plano na porta 5000
python LocalServer.py &
PYTHON_PID=$!

# 3. Inicia o PHP portátil local na porta 8000
./php_bin/php -S 127.0.0.1:8000 &
PHP_PID=$!

echo "----------------------------------------------------"
echo "Servidores Rodando!"
echo "API Python (Banco): http://127.0.0.1:5000"
echo "Site PHP (Interface): http://127.0.0.1:8000/index.php"
echo "Pressione [CTRL+C] para encerrar ambos os servidores."
echo "----------------------------------------------------"

# Mantém o script rodando e fecha ambos os servidores se der CTRL+C
trap "kill $PYTHON_PID $PHP_PID; exit" INT
wait
