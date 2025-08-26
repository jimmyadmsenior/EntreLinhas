<?php
// Verificar includes para a página enviar-artigo.php

echo "Verificando includes para enviar-artigo.php...\n\n";

// Verificar se header.php existe
echo "Verificando header.php: ";
$header_path = __DIR__ . '/PAGES/includes/header.php';
if (file_exists($header_path)) {
    echo "OK - Arquivo existe\n";
} else {
    echo "ERRO - Arquivo não encontrado\n";
}

// Verificar se footer.php existe
echo "Verificando footer.php: ";
$footer_path = __DIR__ . '/PAGES/includes/footer.php';
if (file_exists($footer_path)) {
    echo "OK - Arquivo existe\n";
} else {
    echo "ERRO - Arquivo não encontrado\n";
}

// Verificar usuario_helper.php
echo "Verificando usuario_helper.php: ";
$helper_path = __DIR__ . '/backend/usuario_helper.php';
if (file_exists($helper_path)) {
    echo "OK - Arquivo existe\n";
} else {
    echo "ERRO - Arquivo não encontrado\n";
}

// Verificar config.php
echo "Verificando config.php: ";
$config_path = __DIR__ . '/backend/config.php';
if (file_exists($config_path)) {
    echo "OK - Arquivo existe\n";
} else {
    echo "ERRO - Arquivo não encontrado\n";
}

// Verificar se os arquivos CSS existem
echo "\nVerificando arquivos CSS:\n";
$css_files = [
    'style.css',
    'user-menu.css',
    'alerts.css'
];

foreach ($css_files as $css_file) {
    $css_path = __DIR__ . '/assets/css/' . $css_file;
    echo "Verificando $css_file: ";
    if (file_exists($css_path)) {
        echo "OK - Arquivo existe\n";
    } else {
        echo "ERRO - Arquivo não encontrado\n";
    }
}

// Verificar arquivos JS
echo "\nVerificando arquivos JS:\n";
$js_files = [
    'main.js',
    'auth-cookies.js',
    'verificar-sincronizar-login.js',
    'user-menu.js',
    'dropdown-menu.js'
];

foreach ($js_files as $js_file) {
    $js_path = __DIR__ . '/assets/js/' . $js_file;
    echo "Verificando $js_file: ";
    if (file_exists($js_path)) {
        echo "OK - Arquivo existe\n";
    } else {
        echo "ERRO - Arquivo não encontrado\n";
    }
}

// Verificar diretório de uploads
echo "\nVerificando diretório de uploads:\n";
$upload_dir = __DIR__ . '/uploads/artigos/';
echo "Verificando diretório de uploads para artigos: ";
if (file_exists($upload_dir)) {
    echo "OK - Diretório existe\n";
} else {
    echo "AVISO - Diretório não encontrado (será criado quando necessário)\n";
    // Tentar criar o diretório
    if (@mkdir($upload_dir, 0755, true)) {
        echo "INFO - Diretório de uploads criado com sucesso\n";
    } else {
        echo "ERRO - Não foi possível criar o diretório de uploads\n";
    }
}

echo "\nVerificação concluída!\n";
?>
