<?php
    session_start();
    include 'includes/require_db.php';
    // include 'php/createLog.php';

    if (isset($_POST['login'])) {
        $correo = $_POST['correo'] ?? NULL; // Comprobar que si existe $_POST['correo'], de lo contrario $correo sera igual a NULL
        $password = $_POST['password'] ?? NULL;

        $enlace->begin_transaction();

        try {

            if(is_null($correo) || is_null($password)) { // Comprobar que ambas variables existen
                throw new Exception("¡Algo salio mal!", -1); // Cancelar todo el proceso
            }

            $stmt = $enlace->prepare("SELECT * FROM cliente WHERE correo = ?");
            $stmt->bind_param("s", $correo);
            $stmt->execute();

            $usuario = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Conseguir todo en un array asociativo

            if (count($usuario) < 1) {
                throw new Exception("¡El correo no existe o la contraseña es incorrecta!", -2);
            }

            $usuario = $usuario[0];

            if (!password_verify($password, $usuario['password'])) {
                throw new Exception("¡El correo no existe o la contraseña es incorrecta!", -2);
            }
            
            $enlace->commit(); // Guardar todos los cambios hechos
            $_SESSION['success'] = "¡Has iniciado sesión correctamente!"; // Mensaje de exito que aparece en navbar.php
            $_SESSION['datosUsuario'] = [
                "id" => $usuario["id"],
                "nombre" => $usuario["nombre"],
                "correo" => $usuario["correo"]
            ];
            header('location: servicios.php');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}"; // Mensaje de error junto con el codigo
            if ($e->getCode() == -1) $_SESSION['error'] = "Mensaje custom por error custom"; // Para leer codigos de error custom
            $enlace->rollback(); // Deshacer cambios hechos en la base de datos en caso de error
        }
    }

    if(isset($_POST['signin'])) {
        $nombre = $_POST['nombre'] ?? NULL;
        $correo = $_POST['correo'] ?? NULL;
        $telefono = $_POST['telefono'] ?? NULL;
        $password = $_POST['password'] ?? NULL;
        $confirmar = $_POST['confirmar'] ?? NULL;

        
        $enlace->begin_transaction(); // Para tratar multiples ejecuciones a la base de datos

        try {

            if (in_array(NULL, [$nombre, $correo, $telefono, $password, $confirmar], true)) {
                throw new Exception("¡Algo salio mal!", -1);
            }

            if($password !== $confirmar) {
                throw new Exception("¡Las contraseñas no coinciden!", -2); // Mandar a catch
            }

            $password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $enlace->prepare("INSERT INTO cliente (nombre, correo, telefono, password)
                    VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $correo, $telefono, $password);
            $stmt->execute();

            $enlace->commit(); // Guardar todos los cambios hechos
            $_SESSION['success'] = "¡Te has registrado exitosamente! Prueba iniciar sesión."; // Mensaje de exito que aparece en navbar.php
        } catch (Exception $e) {
            $_SESSION['error'] = "Error {$e->getCode()}: {$e->getMessage()}"; // Mensaje de error junto con el codigo
            $enlace->rollback(); // Deshacer cambios hechos en la base de datos en caso de error
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Redirigir cuando se realize un POST
        $url = basename($_SERVER['PHP_SELF']); // Redirigir a la misma pagina
        header("location: $url");
        exit();
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Login</title>
    <?php include 'includes/head_includes.php'; ?>
</head>
<body>
    <header>
        <?php
            include 'includes/navbar.php';
        ?>
    </header>

    <section class="login-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow">
                        <div class="card-header bg-vino text-white">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" id="login-tab" data-bs-toggle="tab" href="#login">Iniciar Sesión</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="signup-tab" data-bs-toggle="tab" href="#signup">Registrarse</a>
                                </li>
                            
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- login cliente -->
                                <div class="tab-pane fade show active" id="login">
                                    <form id="loginForm" name="poo_project" method="post">
                                        <div class="mb-3">
                                            <label for="loginEmail" class="form-label">Correo Electrónico</label>
                                            <input type="email" name="correo" class="form-control" id="loginEmail" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="loginPassword" class="form-label">Contraseña</label>
                                            <input type="password" name="password" class="form-control" id="loginPassword" required>
                                        </div>
                                        <button type="submit" name="login" class="btn btn-vino w-100">Iniciar Sesión</button>
                                        <div id="loginError" style="display:none; color:red;"></div>
                                    </form>
                                </div>
                                
                                <!-- signup cliente -->
                                <div class="tab-pane fade" id="signup">
                                    <form id="signupForm" name="poo_project" method="post">
                                        <div class="mb-3">
                                            <label for="signupName" class="form-label">Nombre Completo</label>
                                            <input type="text" name="nombre" class="form-control" id="signupName" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="signupEmail" class="form-label">Correo Electrónico</label>
                                            <input type="email" name="correo" class="form-control" id="signupEmail" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="signupPhone" class="form-label">Teléfono</label>
                                            <input type="tel" name="telefono" class="form-control" id="signupPhone" required>
                                        </div>
                                        <div class="row">
                                            <small id="passError" class="text-danger"></small>
                                            <div class="col-6 mb-3">
                                                <label for="signupPassword" class="form-label">Contraseña</label>
                                                <div class="password-wrapper">
                                                    <input type="password" name="password" class="form-control" id="signupPassword" required
                                                    oncopy="return false" oncut="return false" onpaste="return false">
                                                    <button class="toggle-password" type="button" id="togglePass">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label for="signupConfirmPassword" class="form-label">Confirmar Contraseña</label>
                                                <input type="password" name="confirmar" class="form-control" id="signupConfirmPassword" required>
                                            </div>
                                        </div>
                                        <button type="submit" name="signin" class="btn btn-vino w-100">Registrarse</button>
                                    </form>
                                </div>
                                
      
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2025 CIYSE.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Contacto: contacto@ciyse.com | Tel: +52 81 8989 4539</p>
                </div>
            </div>
        </div>
    </footer>

    <?php include 'includes/body_includes.php'; ?>
    <script>
        $(document).ready(function () {
            $('#signupConfirmPassword').on('input', function () { // Comprobar que las contraseñas coincidan
                let pass = $('#signupPassword').val();
                let confirmPass = $(this).val();
                let errorText = $('#passError');

                if (pass !== confirmPass && confirmPass != '') {
                    $(errorText).text("Las contraseñas no coinciden");
                } else {
                    $(errorText).text("");
                }
            });

            $('#togglePass').on('click', function () { // Boton para mostrar la contraseña
                let passwordInput = $('#signupPassword');
                let icon = $('.toggle-password i');

                if ($(passwordInput).attr('type') === "password") {
                    $(passwordInput).attr('type', 'text');
                    $(icon).removeClass("bi-eye")
                           .addClass("bi-eye-slash");
                } else {
                    $(passwordInput).attr('type', 'password');
                    $(icon).removeClass("bi-eye-slash")
                           .addClass("bi-eye");
                }
            });

            $('#signupForm').on('submit', function (event) { // Funcion para evitar enviar si las contraseñas no coinciden
                let pass = $('#signupPassword').val();
                let confirmPass = $("#signupConfirmPassword").val();
                let errorText = $("#passError");

                if (pass !== confirmPass) {
                    $(errorText).text("Las contraseñas no coinciden");
                    event.preventDefault(); // Evita el envío del formulario
                } else {
                    $(errorText).text("");
                }
            });
        });
    </script>
</body>
</html>