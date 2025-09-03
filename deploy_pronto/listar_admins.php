<?php
// Script para listar administradores
require_once 'backend/config.php';

// Consulta para selecionar administradores
$query = "SELECT id, nome, email, tipo FROM usuarios WHERE tipo = 'admin'";
$result = mysqli_query($conn, $query);

echo "<h2>Administradores Registrados:</h2>";

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nome']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['tipo']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum administrador encontrado.</p>";
}

mysqli_close($conn);
?>
