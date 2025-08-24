<?php
// Remover artigos de exemplo
// Este script remove todos os artigos que foram adicionados como exemplos durante o desenvolvimento

// Incluir arquivo de configuração
require_once "backend/config.php";

echo "<h2>Removendo artigos de exemplo</h2>";

// Verificar se está confirmando a ação
if (!isset($_GET['confirmar']) || $_GET['confirmar'] !== 'sim') {
    echo "<div style='background-color: #fff3cd; padding: 15px; margin: 20px 0; border: 1px solid #ffeeba; border-radius: 4px;'>";
    echo "<p><strong>Atenção!</strong> Esta ação irá remover todos os artigos do site.</p>";
    echo "<p>Esta ação não pode ser desfeita. Tem certeza que deseja continuar?</p>";
    echo "<p><a href='?confirmar=sim' style='display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 4px;'>Sim, remover todos os artigos</a> &nbsp; ";
    echo "<a href='index.php' style='display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;'>Cancelar</a></p>";
    echo "</div>";
    exit;
}

// Se chegou aqui, está confirmando a remoção dos artigos

// Primeiro, vamos obter os IDs dos artigos para poder remover imagens e comentários relacionados
$sql_get_artigos = "SELECT id FROM artigos";
$result_artigos = mysqli_query($conn, $sql_get_artigos);

if ($result_artigos) {
    $artigos_ids = [];
    
    while ($row = mysqli_fetch_assoc($result_artigos)) {
        $artigos_ids[] = $row['id'];
    }
    
    // Remover comentários dos artigos
    if (!empty($artigos_ids)) {
        $sql_delete_comentarios = "DELETE FROM comentarios WHERE id_artigo IN (" . implode(',', $artigos_ids) . ")";
        
        if (mysqli_query($conn, $sql_delete_comentarios)) {
            $comentarios_removidos = mysqli_affected_rows($conn);
            echo "<p>Comentários removidos: {$comentarios_removidos}</p>";
        } else {
            echo "<p>Erro ao remover comentários: " . mysqli_error($conn) . "</p>";
        }
        
        // Remover imagens dos artigos
        $sql_delete_imagens = "DELETE FROM imagens_artigos WHERE id_artigo IN (" . implode(',', $artigos_ids) . ")";
        
        if (mysqli_query($conn, $sql_delete_imagens)) {
            $imagens_removidas = mysqli_affected_rows($conn);
            echo "<p>Imagens removidas: {$imagens_removidas}</p>";
        } else {
            echo "<p>Erro ao remover imagens: " . mysqli_error($conn) . "</p>";
        }
    }
    
    // Finalmente, remover os artigos
    $sql_delete_artigos = "DELETE FROM artigos";
    
    if (mysqli_query($conn, $sql_delete_artigos)) {
        $artigos_removidos = mysqli_affected_rows($conn);
        echo "<p>Artigos removidos: {$artigos_removidos}</p>";
        echo "<div style='background-color: #d4edda; padding: 15px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 4px;'>";
        echo "<p><strong>Sucesso!</strong> Todos os artigos foram removidos do site.</p>";
        echo "<p><a href='index.php' style='display: inline-block; margin-top: 10px; padding: 8px 15px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Voltar para a página inicial</a></p>";
        echo "</div>";
    } else {
        echo "<p>Erro ao remover artigos: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>Erro ao listar artigos: " . mysqli_error($conn) . "</p>";
}

// Limpar diretórios de uploads (opcional)
echo "<p>Nota: Os arquivos de imagem no servidor não foram removidos. Se desejar, você pode limpar manualmente os diretórios de uploads.</p>";

// Fechar conexão
mysqli_close($conn);
?>
