<?php
    session_start();
    include 'includes/require_db.php';
    include 'includes/urlRestrictions.php';
    include 'php/createLog.php';

    if (isset($_POST['roleNew'])) {
        $roleName = $_POST['roleName'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($roleName)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("INSERT INTO roles (roleName) VALUES (?)");
            $stmt->bind_param("s", $roleName);
            $stmt->execute();
            $regId = $enlace->insert_id;

            if (!insertLog($enlace, 'roles', $regId, $_SESSION['user']['id'], $roleName)) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Rol creado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if (isset($_POST['roleAssign'])) {
        $roleId = $_POST['roleId'] ?? NULL;
        $userId = $_POST['userId'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($roleId) || is_null($userId)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT r.roleName FROM roles r
                    JOIN users u ON u.roleId = r.roleId
                    WHERE u.userId = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $roleOld = $result[0]['roleName'];

            $stmt = $enlace->prepare("SELECT roleName FROM roles
                    WHERE roleId = ?");
            $stmt->bind_param("i", $roleId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $roleNew = $result[0]['roleName'];

            if ($roleOld == $roleNew) {
                throw new Exception("", 1062);
            }

            if (!updateLog($enlace, 'users', $userId, $_SESSION['user']['id'], "role: $roleOld", "role: $roleNew")) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $stmt = $enlace->prepare("UPDATE users SET roleId = ? WHERE userId = ?");
            $stmt->bind_param("ii", $roleId, $userId);
            $stmt->execute();

            $enlace->commit();
            $_SESSION['success'] = "¡Rol asignado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "¡Ese usuario ya tiene ese rol!";
            }
            $enlace->rollback();
        }
    }

    if (isset($_POST['urlNew'])) {
        $urlAddress = $_POST['urlAddress'] ?? NULL;
        $urlTitle = $_POST['urlName'] ?? NULL;
        $iconId = $_POST['iconId'] ?? NULL;
        $roles = $_POST['role'] ?? [];

        $enlace->begin_transaction();

        try {

            if (is_null($urlAddress) || is_null($urlTitle) || is_null($iconId)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            if (empty($roles)) {
                throw new Exception("¡Debes elegir al menos un rol!", -2);
            }

            $stmt = $enlace->prepare("SELECT * FROM urls WHERE isDeleted = 0");
            $stmt->execute();
            $amount = count($stmt->get_result()->fetch_all(MYSQLI_ASSOC)) + 1;

            if (isset($roles[-1])) {
                $stmt = $enlace->prepare("INSERT INTO urls (iconId, urlAddress, urlTitle, showOrder, allowAll)
                VALUES (?, ?, ?, ?, 1)");
                $stmt->bind_param("issi", $iconId, $urlAddress, $urlTitle, $amount);
                $stmt->execute();
                $regId = $enlace->insert_id;

                if (!insertLog($enlace, 'urls', $regId, $_SESSION['user']['id'], $urlTitle)) {
                    throw new Exception("¡Algo salio mal!", -3);
                }
            } else {
                $stmt = $enlace->prepare("INSERT INTO urls (iconId, urlAddress, urlTitle, showOrder)
                VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $iconId, $urlAddress, $urlTitle, $amount);
                $stmt->execute();
                $regId = $enlace->insert_id;
                $added = "Roles: ";

                foreach ($roles as $key => $role) {
                    $stmt = $enlace->prepare("INSERT INTO role_urls (roleId, urlId)
                    VALUES (?, ?)");
                    $stmt->bind_param("ii", $key, $regId);
                    $stmt->execute();
                    $added .= "$key ";
                }

                if (!insertLog($enlace, 'urls', $regId, $_SESSION['user']['id'], "URL: $urlTitle. $added")) {
                    throw new Exception("¡Algo salio mal!", -3);
                }
            }

            $enlace->commit();
            $_SESSION['success'] = "¡URL creado con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if (isset($_POST['urlPermissions'])) {
        $role = $_POST['roleId'] ?? NULL;
        $urls = $_POST['url'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if (is_null($role) || is_null($urls)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            unset($urls[0]);

            $stmt = $enlace->prepare("SELECT * FROM role_urls
                    WHERE roleId = ?");
            $stmt->bind_param("i", $role);
            $stmt->execute();
            $oldUrls = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $oldValues = "Rol: $role. Url:";
            $newValues = "Rol: $role. Url:";

            foreach ($oldUrls as $oldUrl) {
                if (!isset($urls[$oldUrl['urlId']])) {
                    $stmt = $enlace->prepare("DELETE FROM role_urls
                            WHERE urlId = ?
                            AND roleId = ?");
                    $stmt->bind_param("ii", $oldUrl['urlId'], $role);
                    $stmt->execute();

                    $oldValues .= " {$oldUrl['urlId']}";
                } else {
                    unset($urls[$oldUrl['urlId']]);
                }
            }

            if ($oldValues != "Rol: $role. Url:") {
                if (!deleteLog($enlace, 'roles', $role, $_SESSION['user']['id'], $oldValues)) {
                    throw new Exception("¡Algo salio mal!", -2);
                }
            }

            foreach ($urls as $key => $url) {
                $stmt = $enlace->prepare("INSERT INTO role_urls (roleId, urlId)
                        VALUES (?, ?)");
                $stmt->bind_param("ii", $role, $key);
                $stmt->execute();
                
                $newValues .= " $key";
            }

            if ($newValues != "Rol: $role. Url:") {
                if (!insertLog($enlace, 'roles', $role, $_SESSION['user']['id'], $newValues)) {
                    throw new Exception("¡Algo salio mal!", -3);
                }
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Permisos editados con exito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if (isset($_POST['urlEdit'])) {
        $urlId = $_POST['urlId'] ?? NULL;
        $iconId = $_POST['iconId'] ?? NULL;
        $urlTitle = $_POST['urlTitle'] ?? NULL;
        $urlAddress = $_POST['urlAddress'] ?? NULL;
        $allowAll = isset($_POST['allowAll']) ? 1 : 0;
        $isDisabled = isset($_POST['isDisabled']) ? 1 : 0;
        $urlToCheck = [
            'urlId' => $urlId, 
            'iconId' => $iconId, 
            'urlTitle' => $urlTitle, 
            'urlAddress' => $urlAddress, 
            'allowAll' => $allowAll, 
            'isDisabled' => $isDisabled
        ];

        $enlace->begin_transaction();

        try {

            if(in_array(NULL, [$urlId, $iconId, $urlTitle, $urlAddress], true)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT * FROM urls WHERE urlId = ?");
            $stmt->bind_param("i", $urlId);
            $stmt->execute();
            $url = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $url = $url[0];

            $oldValues = '';
            $newValues = '';
            foreach ($url as $key => $value) {
                if (isset($urlToCheck[$key]) && $value != $urlToCheck[$key]) {
                    $oldValues .= "$key: $value. ";
                    $newValues .= "$key: {$urlToCheck[$key]}. ";
                }
            }

            $stmt = $enlace->prepare("UPDATE urls
                    SET iconId = ?, urlTitle = ?, urlAddress = ?, allowAll = ?, isDisabled = ?
                    WHERE urlId = ?");
            $stmt->bind_param("issiii", $iconId, $urlTitle, $urlAddress, $allowAll, $isDisabled, $urlId);
            $stmt->execute();

            if (!updateLog($enlace, 'urls', $urlId, $_SESSION['user']['id'], $oldValues, $newValues)) {
                throw new Exception("¡Algo salio mal!", -2);
            }

            $enlace->commit();
            $_SESSION['success'] = "¡URL editada con éxito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if (isset($_POST['saveOrder'])) {
        $orders = $_POST['showOrder'];

        $enlace->begin_transaction();

        try {

            if (empty($orders) || in_array(" ", $orders, true)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            $stmt = $enlace->prepare("SELECT showOrder, urlId FROM urls WHERE isDeleted = 0");
            $stmt->execute();
            $urls = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $temp = [];
            foreach ($urls as $url) {
                $temp[$url['urlId']] = $url['showOrder'];
            }
            $urls = $temp;

            foreach ($urls as $id => $order) {
                if (isset($orders[$id]) && $order != $orders[$id]) {
                    $oldValues = "Order $id: $order. ";
                    $newValues = "Order $id: {$orders[$id]}. ";
                    if (!updateLog($enlace, 'urls', $id, $_SESSION['user']['id'], $oldValues, $newValues)) {
                        throw new Exception("¡Algo salio mal!", -2);
                    }
                } else {
                    unset($orders[$id]);
                }
            }

            // Crear un valor temporal para evitar conflictos
            $tempOrders = [];
            foreach ($urls as $id => $showOrder) {
                $tempOrders[$id] = $showOrder;
            }

            // Fase 1: Asignar valores temporales negativos para evitar conflictos
            foreach ($orders as $id => $showOrder) {
                $tempOrder = -1 * ($showOrder + 1); // Número temporal negativo
                $stmt = $enlace->prepare("UPDATE urls SET showOrder = ? WHERE urlId = ?");
                $stmt->bind_param("ii", $tempOrder, $id);
                $stmt->execute();
            }

            // Fase 2: Asignar los valores correctos
            foreach ($orders as $id => $showOrder) {
                $stmt = $enlace->prepare("UPDATE urls SET showOrder = ? WHERE urlId = ?");
                $stmt->bind_param("ii", $showOrder, $id);
                $stmt->execute();
            }

            $enlace->commit();
            $_SESSION['success'] = "¡Orden alterado con éxito!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}";
            $enlace->rollback();
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $url = basename($_SERVER['PHP_SELF']);
        unset($_SESSION['urls']);
        header("location: $url");
        exit();
    }

    $stmt = $enlace->prepare("SELECT *
            FROM users
            WHERE isDeleted = 0
            AND roleId <> 1");
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $enlace->prepare("SELECT u.*, i.iconBi
            FROM urls u
            JOIN icons i ON i.iconId = u.iconId
            WHERE u.isDeleted = 0
            ORDER BY showOrder ASC");
    $stmt->execute();
    $urls = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $enlace->prepare("SELECT *
            FROM roles
            WHERE isDeleted = 0");
    $stmt->execute();
    $roles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $stmt = $enlace->prepare("SELECT iconBi as bi, iconId as id FROM icons WHERE isDeleted = 0");
    $stmt->execute();

    $icons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - URLs y Roles</title>
    <?php include 'includes/head_includes.php'; ?>
    <style>
        .dragList-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: grab;
        }

        .dragList-item:active {
            cursor: grabbing;
        }
    </style>
</head>
<body>
    <header>
        <?php include 'includes/admin_navbar.php'; ?>
    </header>

    <div class="admin-wrapper">
        <div class="admin-main">
            <nav class="admin-topbar navbar navbar-expand navbar-light bg-light">
                <div class="container-fluid">
                    <button class="btn btn-sm btn-vino ms-auto">
                        <i class="bi bi-person-circle me-2"></i>Administrador
                    </button>
                </div>
            </nav>
            <div class="admin-content p-4">
                <div class="row d-flex justify-content-center">
                    <h2 class="mb-4">Roles</h2>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Crear</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <label class="input-label">Nombre del rol</label>
                                    <input type="text" name="roleName" class="form-control" required>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="roleNew">¡Crear rol!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Asignar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <label class="input-label">Rol</label>
                                    <select name="roleId" class="form-select" required>
                                        <option value="" selected disabled>-- Selecciona una opcion --</option>
                                        <?php
                                            foreach ($roles as $role) {
                                                echo "
                                                <option value='{$role['roleId']}'>{$role['roleName']}</option>";
                                            }
                                        ?>
                                    </select>
                                    <label class="input-label">Usuario</label>
                                    <select name="userId" class="form-select" required>
                                        <option value="" selected disabled>-- Selecciona una opcion --</option>
                                        <?php
                                            foreach ($users as $user) {
                                                echo "
                                                <option value='{$user['userId']}'>{$user['userName']}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="roleAssign">¡Asignar rol!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row d-flex justify-content-center">
                    <h2 class="mb-4">URLs</h2>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Agregar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <div class="row">
                                        <h4 class="text-center">Datos de la URL</h4>
                                        <div class="col-6">
                                            <label class="input-label">Direccion</label>
                                            <input type="text" name="urlAddress" class="form-control" required placeholder="index.php" autocomplete="off">
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Titulo de navbar</label>
                                            <input type="text" name="urlName" class="form-control" required placeholder="Inicio" autocomplete="off">
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Icono</label>
                                            <div class="input-group">
                                                <select class="form-select col-8" id="iconId1" name="iconId" required>
                                                    <option value="" selected>-- Elije --</option>
                                                    <?php
                                                        foreach ($icons as $icon) {
                                                            echo "<option value='{$icon['id']}'>{$icon['bi']}</option>";
                                                        }
                                                    ?>
                                                </select>
                                                <span id="preview1" class="input-group-text col-4"><i class="bi bi- me-2"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <h4 class="text-center">Roles con acceso</h4>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="checkAll" name="role[-1]">
                                            <label class="form-check-label" for="checkAll">Permitido a todos</label>
                                        </div>
            
                                        <?php foreach ($roles as $role) { ?>
                                            <div class="col">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input role-checkbox" type="checkbox" name="role[<?= $role['roleId'] ?>]" role="switch">
                                                    <label class="form-check-label"><?= $role['roleName'] ?></label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="urlNew">¡Agregar URL!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Editar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <label class="input-label">URL</label>
                                    <select id="urlId" name="urlId" class="form-select" required>
                                        <option value="" disabled selected>-- Elije una opcion --</option>
                                        <?php
                                            foreach ($urls as $url) {
                                                echo "
                                                <option value='{$url['urlId']}'>{$url['urlTitle']}</option>";
                                            }
                                        ?>
                                    </select>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="input-label">Icono</label>
                                            <div class="input-group">
                                                <select class="form-select col-8" id="iconId" name="iconId" required disabled>
                                                    <option value="" disabled selected>-- Elije --</option>
                                                    <?php
                                                        foreach ($icons as $icon) {
                                                            echo "<option value='{$icon['id']}'>{$icon['bi']}</option>";
                                                        }
                                                    ?>
                                                </select>
                                                <span id="preview" class="input-group-text col-4"><i class="bi bi- me-2"></i></span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Titulo</label>
                                            <input type="text" class="form-control" id="urlTitle" name="urlTitle" disabled>
                                        </div>
                                        <div class="col-6">
                                            <label class="input-label">Direccion URL</label>
                                            <input type="text" class="form-control" id="urlAddress" name="urlAddress" disabled>
                                        </div>
                                        <div class="col-6 mt-4">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="allowAll" name="allowAll" disabled>
                                                <label class="form-check-label">Permitido a todos</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="isDisabled" name="isDisabled" disabled>
                                                <label class="form-check-label">Desabilitar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="urlEdit">¡Guardar!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Permisos</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <div class="row mt-3">
                                        <input type="hidden" name="url[0]" value="1">
                                        <label class="input-label">Rol</label>
                                        <select id="roleId" name="roleId" class="form-select mb-2" required>
                                            <option value="" disabled selected>-- Elije una opcion --</option>
                                            <?php
                                                foreach ($roles as $role) {
                                                    echo "
                                                    <option value='{$role['roleId']}'>{$role['roleName']}</option>";
                                                }
                                            ?>
                                        </select>
            
                                        <?php foreach ($urls as $url) { ?>
                                            <div class="col">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input url-checkbox" type="checkbox" name="url[<?= $url['urlId'] ?>]" role="switch">
                                                    <label class="form-check-label"><?= $url['urlTitle'] ?></label>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="urlPermissions">¡Guardar!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-1">
                        <div class="card shadow">
                            <div class="card-header bg-vino text-white">
                                <h5 class="mb-0">Ordenar</h5>
                            </div>
                            <form action="" method="post">
                                <div class="card-body">
                                    <ul id="dragList" class="list-group">
                                        <?php
                                            foreach ($urls as $url) {
                                                echo "
                                                <li class='list-group-item dragList-item'>
                                                    <span><i class='bi bi-{$url['iconBi']}'></i> {$url['urlTitle']} - {$url['urlAddress']}</span>
                                                    <input type='hidden' name='showOrder[{$url['urlId']}]' value='{$url['showOrder']}'>
                                                </li>";
                                            }
                                        ?>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-vino" name="saveOrder">¡Guardar orden!</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/body_includes.php'; ?>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#iconId1').on('change', function () {
                $('#preview1 i').attr('class', 'bi bi-' + $('#iconId1 option:selected').text() + ' me-2');
            });

            $('#iconId').on('change', function () {
                $('#preview i').attr('class', 'bi bi-' + $('#iconId option:selected').text() + ' me-2');
            });

            $('#checkAll').on('change', function () { 
                let isChecked = $(this).prop('checked'); // Obtiene el estado del switch principal
                $('.role-checkbox').prop('checked', isChecked).trigger('change'); // Aplica a todos los switches
            });

            $('.role-checkbox').on('change', function () {
                if (!$(this).prop('checked')) {
                    $('#checkAll').prop('checked', false);
                } else if ($('.role-checkbox:checked').length === $('.role-checkbox').length) {
                    $('#checkAll').prop('checked', true);
                }
            });

            $("#urlId").change(function () {
                var urlId = $(this).val();
                
                if (urlId) {
                    $.ajax({
                        url: "globalGet.php",
                        type: "POST",
                        data: {
                            id: urlId,
                            page: "urls"
                        },
                        dataType: "json",
                        success: function (response) {
                            if (response.length > 0) {
                                url = response[0];
                                $("#iconId").val(url.iconId)
                                            .attr('disabled', false);
                                $('#preview i').attr('class', 'bi bi-' + $('#iconId option:selected').text() + ' me-2');
                                $('#urlTitle').val(url.urlTitle)
                                              .attr('disabled', false);
                                $('#urlAddress').val(url.urlAddress)
                                                .attr('disabled', false);
                                if (url.allowAll == 1) {
                                    $('#allowAll').prop('checked', true)
                                                  .attr('disabled', false);
                                } else $('#allowAll').prop('checked', false)
                                                     .attr('disabled', false);
                                if (url.isDisabled == 1) {
                                    $('#isDisabled').prop('checked', true)
                                                  .attr('disabled', false);
                                } else $('#isDisabled').prop('checked', false)
                                                       .attr('disabled', false);
                            } else {
                                console.log('none');
                            }
                        },
                        error: function () {
                            console.error("Error AJAX:", status, error);
                            console.log("Respuesta completa:", xhr.responseText);
                            alert("Error al obtener los grupos.");
                        }
                    });
                }
            });

            $("#roleId").change(function () {
                var roleId = $(this).val();
                
                if (roleId) {
                    $.ajax({
                        url: "globalGet.php",
                        type: "POST",
                        data: {
                            id: roleId,
                            page: "urlRoles"
                        },
                        dataType: "json",
                        success: function (response) {
                            $('.url-checkbox').prop('checked', false);
                            
                            if (Array.isArray(response)) {
                                response.forEach(function(url) {
                                    if (url.urlId) {
                                        $('input[name="url[' + url.urlId + ']"].url-checkbox')
                                            .prop('checked', true);
                                    }
                                });
                            } else {
                                console.warn("Respuesta inesperada:", response);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("Error AJAX:", status, error);
                            console.log("Respuesta completa:", xhr.responseText);
                            alert("Error al obtener los grupos.");
                        }
                    });
                }
            });
            
            // Hacer la lista arrastrable y actualizar posiciones
            $("#dragList").sortable({
                update: function() {
                    updatePositions();
                }
            });

            // Función para actualizar los data-position en todos los elementos
            function updatePositions() {
                $("#dragList .dragList-item input").each(function(index) {
                    let newPosition = index + 1;
                    $(this).val(newPosition);
                });
            }
        });
    </script>
</body>
</html>