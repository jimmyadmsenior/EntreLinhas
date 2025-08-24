<?php
// Iniciar a sessão
session_start();

// Incluir arquivo de configuração
require_once "../backend/config.php";

// Função para exibir informações de depuração de forma segura
function debug_var($var, $var_name) {
    echo "<strong>" . htmlspecialchars($var_name) . ":</strong> ";
    if (is_array($var) || is_object($var)) {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    } else {
        echo htmlspecialchars(var_export($var, true)) . "<br>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - EntreLinhas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .debug-section {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        h2 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            overflow: auto;
        }
        .status-logado {
            color: green;
            font-weight: bold;
        }
        .status-nao-logado {
            color: red;
            font-weight: bold;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
    <h1>Página de Depuração - EntreLinhas</h1>
    <p><a href="../index.php">← Voltar para a página inicial</a></p>
    
    <div class="grid">
        <!-- Informações da Sessão PHP -->
        <div class="debug-section">
            <h2>Estado da Sessão PHP</h2>
            <p>Status: 
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <span class="status-logado">LOGADO</span>
                <?php else: ?>
                    <span class="status-nao-logado">NÃO LOGADO</span>
                <?php endif; ?>
            </p>
            
            <h3>Variáveis de Sessão:</h3>
            <?php 
            if (!empty($_SESSION)) {
                echo "<pre>";
                foreach ($_SESSION as $key => $value) {
                    if ($key === 'senha' || $key === 'password') continue; // Não mostrar senhas
                    echo htmlspecialchars($key) . ": " . htmlspecialchars(var_export($value, true)) . "\n";
                }
                echo "</pre>";
            } else {
                echo "<p>Nenhuma variável de sessão definida.</p>";
            }
            ?>
            
            <h3>Cookies:</h3>
            <?php
            if (!empty($_COOKIE)) {
                echo "<pre>";
                foreach ($_COOKIE as $key => $value) {
                    if ($key === 'senha' || $key === 'password') continue; // Não mostrar senhas
                    echo htmlspecialchars($key) . ": " . htmlspecialchars($value) . "\n";
                }
                echo "</pre>";
            } else {
                echo "<p>Nenhum cookie definido.</p>";
            }
            ?>
        </div>
        
        <!-- Informações do localStorage (via JavaScript) -->
        <div class="debug-section">
            <h2>Estado do localStorage</h2>
            <div id="localstorage-data">Carregando dados do localStorage...</div>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>Ações de Depuração</h2>
        <button id="btn-sync">Sincronizar localStorage com Sessão PHP</button>
        <button id="btn-clear-session">Limpar Sessão PHP</button>
        <button id="btn-clear-localstorage">Limpar localStorage</button>
        <button id="btn-clear-all">Limpar Tudo</button>
    </div>
    
    <script>
        // Função para exibir dados do localStorage
        function mostrarDadosLocalStorage() {
            const localStorageData = document.getElementById('localstorage-data');
            
            // Verificar se localStorage está disponível
            if (typeof(Storage) === "undefined") {
                localStorageData.innerHTML = "<p>LocalStorage não é suportado neste navegador.</p>";
                return;
            }
            
            // Verificar se o usuário está logado no localStorage
            const userLoggedIn = localStorage.getItem('userLoggedIn');
            
            localStorageData.innerHTML = `
                <p>Status: ${userLoggedIn === 'true' ? 
                    '<span class="status-logado">LOGADO</span>' : 
                    '<span class="status-nao-logado">NÃO LOGADO</span>'}
                </p>
                
                <h3>Itens no localStorage:</h3>
            `;
            
            // Obter todos os itens do localStorage
            let items = '';
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const value = localStorage.getItem(key);
                
                // Não mostrar senhas
                if (key !== 'senha' && key !== 'password') {
                    items += `${key}: ${value}<br>`;
                }
            }
            
            if (items) {
                localStorageData.innerHTML += `<p>${items}</p>`;
            } else {
                localStorageData.innerHTML += `<p>Nenhum item no localStorage.</p>`;
            }
        }
        
        // Configurar botões de ação
        document.getElementById('btn-sync').addEventListener('click', function() {
            // Chamar API para obter dados da sessão
            fetch('verificar_login.php')
                .then(response => response.json())
                .then(data => {
                    if (data.logado) {
                        localStorage.setItem('userLoggedIn', 'true');
                        localStorage.setItem('userName', data.dados.nome);
                        localStorage.setItem('userEmail', data.dados.email);
                        localStorage.setItem('userType', data.dados.tipo);
                        localStorage.setItem('userId', data.dados.id);
                        alert('Dados sincronizados da sessão para o localStorage');
                    } else {
                        localStorage.removeItem('userLoggedIn');
                        localStorage.removeItem('userName');
                        localStorage.removeItem('userEmail');
                        localStorage.removeItem('userType');
                        localStorage.removeItem('userId');
                        alert('Usuário não está logado na sessão. LocalStorage limpo.');
                    }
                    location.reload();
                })
                .catch(error => {
                    console.error('Erro ao sincronizar:', error);
                    alert('Erro ao sincronizar dados.');
                });
        });
        
        document.getElementById('btn-clear-session').addEventListener('click', function() {
            fetch('logout.php')
                .then(() => {
                    alert('Sessão PHP encerrada.');
                    location.reload();
                })
                .catch(() => {
                    alert('Erro ao encerrar sessão.');
                });
        });
        
        document.getElementById('btn-clear-localstorage').addEventListener('click', function() {
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('userType');
            localStorage.removeItem('userId');
            alert('Dados de autenticação removidos do localStorage.');
            location.reload();
        });
        
        document.getElementById('btn-clear-all').addEventListener('click', function() {
            // Limpar localStorage
            localStorage.removeItem('userLoggedIn');
            localStorage.removeItem('userName');
            localStorage.removeItem('userEmail');
            localStorage.removeItem('userType');
            localStorage.removeItem('userId');
            
            // Limpar sessão
            fetch('logout.php')
                .then(() => {
                    alert('Todos os dados de autenticação foram removidos.');
                    location.reload();
                })
                .catch(() => {
                    alert('Erro ao encerrar sessão.');
                    location.reload(); // Recarregar mesmo em caso de erro
                });
        });
        
        // Exibir dados do localStorage quando a página carregar
        document.addEventListener('DOMContentLoaded', mostrarDadosLocalStorage);
    </script>
</body>
</html>
