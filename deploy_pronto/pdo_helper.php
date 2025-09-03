<?php
/**
 * Funções de ajuda para PDO
 * Este arquivo contém funções para facilitar a migração de código mysqli para PDO
 */

/**
 * Executa uma consulta SQL e retorna todos os resultados como um array associativo
 * Equivalente a mysqli_query + mysqli_fetch_all(MYSQLI_ASSOC)
 * 
 * @param PDO $pdo Conexão PDO
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para prepared statement (opcional)
 * @return array|false Array de resultados ou false em caso de erro
 */
function pdo_query_all($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO: " . $e->getMessage());
        return false;
    }
}

/**
 * Executa uma consulta SQL e retorna a primeira linha como um array associativo
 * Equivalente a mysqli_query + mysqli_fetch_assoc
 * 
 * @param PDO $pdo Conexão PDO
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para prepared statement (opcional)
 * @return array|false Array associativo ou false em caso de erro ou sem resultados
 */
function pdo_query_first($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Erro PDO: " . $e->getMessage());
        return false;
    }
}

/**
 * Executa uma consulta SQL para uma única coluna e retorna o valor
 * Equivalente a mysqli_query + mysqli_fetch_row[0]
 * 
 * @param PDO $pdo Conexão PDO
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para prepared statement (opcional)
 * @return mixed|false Valor da coluna ou false em caso de erro
 */
function pdo_query_value($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Erro PDO: " . $e->getMessage());
        return false;
    }
}

/**
 * Executa uma consulta SQL sem retornar resultados (INSERT, UPDATE, DELETE, etc)
 * Equivalente a mysqli_query para consultas sem resultado
 * 
 * @param PDO $pdo Conexão PDO
 * @param string $sql Consulta SQL
 * @param array $params Parâmetros para prepared statement (opcional)
 * @return bool true em caso de sucesso, false em caso de erro
 */
function pdo_execute($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Erro PDO: " . $e->getMessage());
        return false;
    }
}

/**
 * Retorna o ID da última linha inserida
 * Equivalente a mysqli_insert_id
 * 
 * @param PDO $pdo Conexão PDO
 * @return string O ID da última inserção
 */
function pdo_insert_id($pdo) {
    return $pdo->lastInsertId();
}

/**
 * Retorna o número de linhas afetadas pela última operação
 * Equivalente a mysqli_affected_rows
 * 
 * @param PDOStatement $stmt Statement PDO após execução
 * @return int Número de linhas afetadas
 */
function pdo_affected_rows($stmt) {
    return $stmt->rowCount();
}

/**
 * Escapa uma string para uso em consultas SQL
 * Equivalente a mysqli_real_escape_string
 * Nota: PDO usa prepared statements, então isso normalmente não é necessário
 * 
 * @param string $str String a ser escapada
 * @return string String escapada
 */
function pdo_escape_string($str) {
    return substr(str_replace("'", "''", $str), 0);
}

/**
 * Verifica se uma consulta retornou algum resultado
 * 
 * @param PDOStatement $stmt Statement PDO após execução
 * @return bool true se houver resultados, false caso contrário
 */
function pdo_has_rows($stmt) {
    return $stmt->rowCount() > 0;
}
?>
