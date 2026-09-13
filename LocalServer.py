import sqlite3
import hashlib
import sys
import os
from flask import Flask, jsonify, render_template_string, request

app = Flask(__name__, template_folder='.')

DB_NAME = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'tcc.db')

def get_db_connection():
    conn = sqlite3.connect(DB_NAME)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON;")
    return conn

def init_db(force_reset=False):
    conn = get_db_connection()
    cursor = conn.cursor()

    if force_reset:
        cursor.executescript('''
        DROP TABLE IF EXISTS tb_imagens;
        DROP TABLE IF EXISTS tb_agendamentos;
        DROP TABLE IF EXISTS tb_reservas;
        DROP TABLE IF EXISTS tb_horarios;
        DROP TABLE IF EXISTS tb_eventos;
        DROP TABLE IF EXISTS tb_estabelecimentos;
        DROP TABLE IF EXISTS tb_clientes;
        ''')
        print("Tabelas antigas removidas (--reset ativado).")

    # Criação das tabelas com constraints compatíveis e tipos de texto para CPF/Fone/CNPJ
    cursor.executescript('''
    CREATE TABLE IF NOT EXISTS tb_clientes (
        cod_cliente INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        cpf TEXT UNIQUE NOT NULL,
        fone TEXT UNIQUE NOT NULL,
        dtnasc TEXT NOT NULL DEFAULT '2006-01-01',
        ativo TEXT DEFAULT 'S' NOT NULL CHECK(ativo IN('S','N')),
        tipo TEXT DEFAULT 'U' NOT NULL CHECK(tipo IN('A','U')),
        senha TEXT NOT NULL,
        pagamento TEXT DEFAULT 'Dinheiro' NOT NULL CHECK(pagamento IN('Dinheiro', 'Pix', 'Cartão Débito', 'Cartão Crédito', 'cartao', 'pix', 'dinheiro')),
        token_resetar TEXT DEFAULT NULL,
        token_expirar TEXT
    );

    CREATE TABLE IF NOT EXISTS tb_estabelecimentos (
        cod_estabelecimento INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        fone TEXT,
        ativo TEXT DEFAULT 'S' NOT NULL CHECK(ativo IN('S','N')),
        tipo TEXT NOT NULL DEFAULT 'R' CHECK(tipo IN('R','E')),
        cnpj TEXT
    );

    CREATE TABLE IF NOT EXISTS tb_reservas (
        cod_reserva INTEGER PRIMARY KEY AUTOINCREMENT,
        valor REAL,
        dt TEXT NOT NULL,
        hora TEXT NOT NULL,
        qtdmesa INTEGER NOT NULL,
        cod_cliente INTEGER NOT NULL,
        cod_estabelecimento INTEGER NOT NULL,
        pagamento TEXT DEFAULT 'Dinheiro' NOT NULL CHECK(pagamento IN('Dinheiro','Pix','Cartão Débito', 'Cartão Crédito', 'cartao', 'pix', 'dinheiro')),
        FOREIGN KEY (cod_cliente) REFERENCES tb_clientes(cod_cliente),
        FOREIGN KEY (cod_estabelecimento) REFERENCES tb_estabelecimentos(cod_estabelecimento)
    );

    CREATE TABLE IF NOT EXISTS tb_eventos (
        cod_evento INTEGER PRIMARY KEY AUTOINCREMENT,
        tipo TEXT DEFAULT 'P' NOT NULL CHECK(tipo IN('P','M','G')),
        ativo TEXT DEFAULT 'S' NOT NULL CHECK(ativo IN('S','N')),
        valor REAL,
        nome TEXT NOT NULL,
        data_inicio TEXT NOT NULL,
        data_fim TEXT NOT NULL, 
        descricao TEXT
    );

    CREATE TABLE IF NOT EXISTS tb_agendamentos (
        cod_agendamento INTEGER PRIMARY KEY AUTOINCREMENT,
        hora TEXT NOT NULL,
        dt TEXT NOT NULL,
        cod_estabelecimento INTEGER NOT NULL,
        cod_cliente INTEGER NOT NULL,
        cod_evento INTEGER NOT NULL,
        pagamento TEXT DEFAULT 'Dinheiro' NOT NULL CHECK(pagamento IN('Dinheiro','Pix','Cartão Débito', 'Cartão Crédito', 'cartao', 'pix', 'dinheiro')),
        FOREIGN KEY (cod_cliente) REFERENCES tb_clientes(cod_cliente),
        FOREIGN KEY (cod_estabelecimento) REFERENCES tb_estabelecimentos(cod_estabelecimento),
        FOREIGN KEY (cod_evento) REFERENCES tb_eventos(cod_evento)
    );

    CREATE TABLE IF NOT EXISTS tb_imagens (
        cod_imagem INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT,
        diretorio TEXT NOT NULL,
        cod_evento INTEGER,
        FOREIGN KEY (cod_evento) REFERENCES tb_eventos(cod_evento)
    );

    CREATE TABLE IF NOT EXISTS tb_horarios (
        cod_horario INTEGER PRIMARY KEY AUTOINCREMENT,
        entrada_manha TEXT,
        entrada_tarde TEXT,
        saida_manha TEXT,
        diasemana TEXT CHECK(diasemana IN('Segunda','Terça','Quarta','Quinta','Sexta','Sábado')),
        saida_tarde TEXT,
        entrada_noite TEXT,
        saida_noite TEXT
    );
    ''')

    # Verifica se já há clientes para decidir se popula os dados iniciais
    cursor.execute("SELECT COUNT(*) FROM tb_clientes;")
    qtd_clientes = cursor.fetchone()[0]

    if qtd_clientes == 0:
        print("Inserindo dados iniciais no banco de dados...")
        s1 = hashlib.md5("pedro123".encode()).hexdigest()
        s2 = hashlib.md5("jose123".encode()).hexdigest()
        s3 = hashlib.md5("jhonas123".encode()).hexdigest()

        cursor.executescript(f'''
        INSERT INTO tb_eventos (cod_evento, nome, tipo, ativo, valor, data_inicio, data_fim, descricao) VALUES
        (1, 'Show na pizzaria', 'P', 'S', 100.00, '2026-01-01', '2026-12-31', 'Um show com direito a bebida grátis e muita música!'),
        (2, 'Sabores na Brasa', 'G', 'S', 200.00, '2026-01-01', '2026-12-31', 'Carnes exóticas por tempo limitado!'),
        (3, 'Festa de família', 'P', 'S', 300.00, '2026-01-01', '2026-12-31', 'Venha em nossa festa com seus parentes!');

        INSERT INTO tb_clientes (cod_cliente, nome, email, cpf, fone, dtnasc, ativo, tipo, pagamento, senha, token_resetar, token_expirar) VALUES
        (1, 'Pedro dos Santos', 'pedro123@gmail.com', '11111111111', '11999991111', '2000-01-01', 'S', 'U', 'Dinheiro', '{s1}', NULL, NULL),
        (2, 'José Souze', 'jose123@gmail.com', '22222222222', '11999992222', '1995-05-10', 'N', 'U', 'Cartão Débito', '{s2}', NULL, NULL),
        (3, 'Jhonas da Silva', 'jhonas123@gmail.com', '33333333333', '11999993333', '1990-08-20', 'S', 'A', 'Pix', '{s3}', NULL, NULL);

        INSERT INTO tb_estabelecimentos (cod_estabelecimento, nome, fone, ativo, tipo, cnpj) VALUES
        (1, 'Restaurante Central', '1133334444', 'S', 'R', '11222333000144'),
        (2, 'Pizzaria da Esquina', '1144445555', 'S', 'E', '22333444000155'),
        (3, 'Churrascaria Boa Carne', '1155556666', 'S', 'R', '33444555000166'),
        (4, 'Espaço Nobre Eventos', '1166667777', 'S', 'E', '44555666000177');

        INSERT INTO tb_reservas (cod_reserva, valor, dt, hora, qtdmesa, cod_cliente, cod_estabelecimento, pagamento) VALUES
        (1, 150.00, '2026-09-20', '19:00', 2, 1, 1, 'Cartão Débito'),
        (2, 200.00, '2026-09-21', '20:00', 3, 2, 3, 'Cartão Crédito'),
        (3, 250.00, '2026-09-22', '21:00', 4, 3, 3, 'Dinheiro');

        INSERT INTO tb_agendamentos (cod_agendamento, hora, dt, cod_estabelecimento, cod_cliente, cod_evento, pagamento) VALUES
        (1, '15:00', '2026-09-25', 2, 1, 1, 'Dinheiro'),
        (2, '21:00', '2026-09-26', 2, 2, 2, 'Pix'),
        (3, '18:00', '2026-09-27', 4, 3, 3, 'Cartão Crédito');

        INSERT INTO tb_imagens (cod_imagem, nome, diretorio, cod_evento) VALUES
        (1, 'Show na pizzaria', 'img_banco/Show_na_pizzaria.png', 1),
        (2, 'Sabores na Brasa', 'img/banner.png', 2),
        (3, 'Festa de família', 'img_banco/Festa_de_familia.png', 3);

        INSERT INTO tb_horarios (entrada_manha, entrada_tarde, saida_manha, diasemana, saida_tarde, entrada_noite, saida_noite) VALUES
        ('08:00', '13:00', '12:00', 'Sábado', '18:00', '20:00', '23:00'),
        ('07:30', '12:30', '11:30', 'Sexta', '17:30', '19:30', '22:30'),
        ('07:00', '12:00', '11:00', 'Quinta', '17:00', '19:00', '22:00');
        ''')

    conn.commit()
    conn.close()

# Rota principal com Dashboard do Banco de Dados
@app.route('/', methods=['GET'])
def index():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';")
    tables = [row['name'] for row in cursor.fetchall()]
    
    stats = {}
    for table in tables:
        cursor.execute(f"SELECT COUNT(*) as count FROM {table};")
        stats[table] = cursor.fetchone()['count']
    
    conn.close()
    
    html = '''
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <title>Delicious Churras - Servidor Local Flask</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body { background: #121214; color: #e1e1e6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 40px 20px; }
            .card { background: #202024; border: 1px solid #323238; border-radius: 8px; margin-bottom: 20px; }
            .card-header { background: #29292e; font-weight: bold; border-bottom: 1px solid #323238; color: #ff9000; }
            .badge-primary { background: #ff9000; }
            a { color: #ff9000; }
            a:hover { color: #ffad33; text-decoration: none; }
            .status-indicator { display: inline-block; width: 12px; height: 12px; background: #04d361; border-radius: 50%; margin-right: 8px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><span class="status-indicator"></span>Servidor Flask / SQLite Ativo</h2>
                <div>
                    <a href="http://127.0.0.1:8000/index.php" target="_blank" class="btn btn-outline-warning">Abrir Site PHP (Porta 8000)</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">Status do Banco de Dados</div>
                        <div class="card-body">
                            <p><strong>Arquivo:</strong> <code>tcc.db</code></p>
                            <p><strong>Porta Flask:</strong> 5000</p>
                            <p><strong>Porta PHP:</strong> 8000</p>
                            <p><strong>Chaves Estrangeiras:</strong> <span class="badge badge-success">Ativadas</span></p>
                            <a href="/api/status" class="btn btn-sm btn-dark">Ver JSON Status</a>
                            <a href="/api/dados" class="btn btn-sm btn-dark">Ver JSON Dados</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Tabelas e Registros no SQLite</div>
                        <div class="card-body">
                            <table class="table table-dark table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Nome da Tabela</th>
                                        <th>Total de Registros</th>
                                        <th>Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {% for table, count in stats.items() %}
                                    <tr>
                                        <td><strong>{{ table }}</strong></td>
                                        <td><span class="badge badge-primary">{{ count }}</span></td>
                                        <td><a href="/api/tabela/{{ table }}" class="btn btn-sm btn-outline-light">Ver JSON</a></td>
                                    </tr>
                                    {% endfor %}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    '''
    return render_template_string(html, stats=stats)

@app.route('/api/status', methods=['GET'])
def status():
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';")
    tables = [row['name'] for row in cursor.fetchall()]
    conn.close()
    return jsonify({
        "status": "online",
        "database": "tcc.db",
        "tables": tables,
        "php_url": "http://127.0.0.1:8000/index.php"
    })

@app.route('/api/tabela/<nome_tabela>', methods=['GET'])
def obter_tabela(nome_tabela):
    conn = get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SELECT name FROM sqlite_master WHERE type='table' AND name = ?;", (nome_tabela,))
    if not cursor.fetchone():
        conn.close()
        return jsonify({"erro": "Tabela não encontrada"}), 404
        
    cursor.execute(f"SELECT * FROM {nome_tabela};")
    rows = [dict(row) for row in cursor.fetchall()]
    conn.close()
    return jsonify(rows)

@app.route('/api/dados', methods=['GET'])
def obter_dados():
    conn = get_db_connection()
    cursor = conn.cursor()
    
    query = '''
    SELECT tb_clientes.cod_cliente,
           tb_estabelecimentos.nome AS nome_estabelecimento,
           tb_reservas.dt AS data_reserva,
           tb_eventos.nome AS nome_evento,
           tb_agendamentos.hora AS hora_agendamento
    FROM tb_clientes
    JOIN tb_agendamentos ON tb_clientes.cod_cliente = tb_agendamentos.cod_cliente
    JOIN tb_estabelecimentos ON tb_estabelecimentos.cod_estabelecimento = tb_agendamentos.cod_estabelecimento
    JOIN tb_eventos ON tb_eventos.cod_evento = tb_agendamentos.cod_evento
    JOIN tb_reservas ON tb_clientes.cod_cliente = tb_reservas.cod_cliente
      AND tb_estabelecimentos.cod_estabelecimento = tb_reservas.cod_estabelecimento;
    '''
    
    cursor.execute(query)
    resultado = [dict(row) for row in cursor.fetchall()]
    conn.close()
    
    return jsonify(resultado)

if __name__ == '__main__':
    force_reset = '--reset' in sys.argv
    init_db(force_reset=force_reset)
    print("Banco de dados SQLite 'tcc.db' iniciado com sucesso!")
    app.run(host='127.0.0.1', port=5000, debug=True)
