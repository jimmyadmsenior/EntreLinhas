<?php
/**
 * Adaptador de Conexão com Banco de Dados
 * 
 * Este arquivo cria um adaptador para permitir que código que usa mysqli
 * possa funcionar com a nova conexão PDO. Isso é uma solução temporária
 * até que todo o código seja migrado para PDO.
 */

// Se já existe uma conexão PDO mas não existe o objeto mysqli, cria um compatível
if (isset($conn) && $conn instanceof PDO && !isset($mysqli_conn)) {
    
    // Criar uma conexão mysqli tradicional como backup
    $mysqli_conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if (!$mysqli_conn) {
        // Registra o erro para diagnóstico, mas não mata a execução
        error_log("Falha ao criar conexão mysqli de backup: " . mysqli_connect_error());
    } else {
        // Configurar charset
        mysqli_set_charset($mysqli_conn, "utf8mb4");
        
        // Alias para compatibilidade com código existente
        // Muitos arquivos usam $conn como mysqli
        if (!function_exists('mysqli_prepare') && !defined('MYSQLI_ADAPTER_LOADED')) {
            define('MYSQLI_ADAPTER_LOADED', true);
            
            /**
             * Função de adaptador para mysqli_prepare
             */
            function mysqli_prepare($link, $query) {
                global $conn; // A conexão PDO
                
                try {
                    $stmt = $conn->prepare($query);
                    return new MysqliStatementAdapter($stmt, $query);
                } catch (PDOException $e) {
                    error_log("Erro ao preparar consulta PDO: " . $e->getMessage());
                    return false;
                }
            }
            
            /**
             * Classe adaptadora para compatibilizar mysqli_stmt com PDO
             */
            class MysqliStatementAdapter {
                private $pdoStatement;
                private $query;
                private $boundParams = [];
                private $boundResults = [];
                private $affectedRows = 0;
                private $insertId = 0;
                private $resultData = [];
                private $numRows = 0;
                
                public function __construct($pdoStatement, $query) {
                    $this->pdoStatement = $pdoStatement;
                    $this->query = $query;
                }
                
                public function bind_param($types, ...$params) {
                    $i = 0;
                    foreach ($params as $param) {
                        $type = $types[$i] ?? 's';
                        $this->boundParams[] = $param;
                        $paramType = PDO::PARAM_STR;
                        
                        switch ($type) {
                            case 'i': $paramType = PDO::PARAM_INT; break;
                            case 'd': $paramType = PDO::PARAM_STR; break;
                            case 'b': $paramType = PDO::PARAM_LOB; break;
                        }
                        
                        $this->pdoStatement->bindValue($i + 1, $param, $paramType);
                        $i++;
                    }
                    return true;
                }
                
                public function bind_result(&...$vars) {
                    $this->boundResults = &$vars;
                    return true;
                }
                
                public function execute() {
                    try {
                        $result = $this->pdoStatement->execute();
                        $this->affectedRows = $this->pdoStatement->rowCount();
                        
                        global $conn;
                        if ($conn instanceof PDO) {
                            // Tenta obter o último ID inserido
                            $this->insertId = $conn->lastInsertId();
                        }
                        
                        return $result;
                    } catch (PDOException $e) {
                        error_log("Erro ao executar consulta PDO: " . $e->getMessage());
                        return false;
                    }
                }
                
                public function store_result() {
                    try {
                        $this->resultData = $this->pdoStatement->fetchAll(PDO::FETCH_NUM);
                        $this->numRows = count($this->resultData);
                        return true;
                    } catch (PDOException $e) {
                        error_log("Erro ao armazenar resultado PDO: " . $e->getMessage());
                        return false;
                    }
                }
                
                public function num_rows() {
                    return $this->numRows;
                }
                
                public function fetch() {
                    if (empty($this->resultData)) {
                        try {
                            $row = $this->pdoStatement->fetch(PDO::FETCH_NUM);
                            if ($row) {
                                foreach ($row as $i => $value) {
                                    if (isset($this->boundResults[$i])) {
                                        $this->boundResults[$i] = $value;
                                    }
                                }
                                return true;
                            }
                            return false;
                        } catch (PDOException $e) {
                            error_log("Erro ao buscar linha PDO: " . $e->getMessage());
                            return false;
                        }
                    } else {
                        if (count($this->resultData) > 0) {
                            $row = array_shift($this->resultData);
                            foreach ($row as $i => $value) {
                                if (isset($this->boundResults[$i])) {
                                    $this->boundResults[$i] = $value;
                                }
                            }
                            return true;
                        }
                        return false;
                    }
                }
                
                public function close() {
                    $this->pdoStatement = null;
                    $this->boundParams = [];
                    $this->boundResults = [];
                    return true;
                }
                
                public function get_result() {
                    return new MysqliResultAdapter($this->pdoStatement->fetchAll(PDO::FETCH_ASSOC));
                }
                
                public function affected_rows() {
                    return $this->affectedRows;
                }
                
                public function insert_id() {
                    return $this->insertId;
                }
            }
            
            /**
             * Adaptador de resultado mysqli para PDO
             */
            class MysqliResultAdapter {
                private $data;
                private $position = 0;
                
                public function __construct($data) {
                    $this->data = $data;
                }
                
                public function fetch_assoc() {
                    if ($this->position >= count($this->data)) {
                        return null;
                    }
                    return $this->data[$this->position++];
                }
                
                public function fetch_all(int $resulttype = MYSQLI_NUM) {
                    return $this->data;
                }
                
                public function num_rows() {
                    return count($this->data);
                }
                
                public function free() {
                    $this->data = [];
                    $this->position = 0;
                }
            }
        }
    }
}
?>
