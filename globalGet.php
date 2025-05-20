<?php
    include 'includes/require_db.php';

    header('Content-Type: application/json');

    function failed($message = "Algo salio mal") {
        echo json_encode(["success" => false, "error" => "$message"]);
        exit;
    }

    $getConfig = [ // Configuracion customizada para casos especiales
        "urlRoles" => [
            "columns" => "DISTINCT u.urlId",
            "page" => "urls u",
            "join" => "JOIN role_urls ru ON ru.urlId = u.urlId",
            "where" => "WHERE roleId = ?"
        ]
    ];

    function returnRows($page, $id, $enlace, $config = []) {

        $whitelist = ["users", "urls", "urlRoles", "products", "services", "icons"]; // Lista blanca

        if (in_array($page, $whitelist, true)) {

            $idCol = isset($config['customIdCol']) ? $config['customIdCol'] : substr($page, 0, strlen($page) - 1) . "Id";
            $columns = $config['columns'] ?? "placeholder.*";
            $page = $config['page'] ?? "$page placeholder";
            $join = $config['join'] ?? "";
            $where = $config['where'] ?? "WHERE placeholder.$idCol = ?
                    AND placeholder.isDeleted = 0";
            $types = $config['types'] ?? "i";
            $params = $config['params'] ?? [$id];

            $stmt = $enlace->prepare("SELECT $columns FROM $page
                    $join
                    $where");
            /*
                Si no hay nada de configuracion customizada la query se vera algo asi
                SELECT placeholder.* FROM [tabla de ejemplo, como "users"] placeholder
                WHERE placeholder.userId = ?
                AND placeholder.isDeleted = 0
            */
            if (!$stmt) {
                failed("Error al preparar la consulta");
            }
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                failed("Error al ejecutar la consulta");
            }
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } else {
            failed("ID invalida");
        }
    }

    if (isset($_POST['page']) && isset($_POST['id'])) {
        $page = $_POST['page'];
        $id = $_POST['id'];
        $config = $getConfig[$page] ?? [];

        $rows = returnRows($page, $id, $enlace, $config);
        echo json_encode($rows);
    } else {
        failed();
    }
?>