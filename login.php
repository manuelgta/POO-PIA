<?php
    include 'db.php';

    if (isset($_POST['login'])) {
        $correo = $_POST['correo'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM cliente WHERE correo = '$correo'";
        $resultado = mysqli_query($enlace, $sql);
        $usuario = mysqli_fetch_assoc($resultado);

        if ($usuario) {
            if (password_verify($password, $usuario['password'])) {
                header('location: servicios.php');
                exit();
            } else {
                echo "Contraseña incorrecta";
            }
        } else {
            echo "Correo no registrado";
        }
    }

    if(isset($_POST['signin'])) {
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $telefono = $_POST['telefono'];
        $password = $_POST['password'];
        $confirmar = $_POST['confirmar'];
    
        if ($password !== $confirmar) {
            echo "Las contraseñas no coinciden";
            exit();
        }

        $password = password_hash($password, PASSWORD_DEFAULT);
    
        $sql = "INSERT INTO cliente (nombre, correo, telefono, password) 
                VALUES ('$nombre', '$correo', '$telefono', '$password')";
    
        if (mysqli_query($enlace, $sql)) {
            echo "ok"; 
        } else {
            echo "error";
        }
    }

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIYSE - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo1.css">
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
                                        <div class="mb-3">
                                            <label for="signupPassword" class="form-label">Contraseña</label>
                                            <input type="password" name="password" class="form-control" id="signupPassword" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="signupConfirmPassword" class="form-label">Confirmar Contraseña</label>
                                            <input type="password" name="confirmar" class="form-control" id="signupConfirmPassword" required>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="javascript/script1.js"></script>
</body>
</html>