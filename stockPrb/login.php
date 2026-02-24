<?php
session_start();
if (!empty($_SESSION['logueo'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config.php';
    $usuario = isset($_POST['usuario']) ? $_POST['usuario'] : '';
    $pass = isset($_POST['palabra_secreta']) ? $_POST['palabra_secreta'] : '';
    if (!is_string($usuario) || !is_string($pass)) {
        http_response_code(400);
        exit('Entrada inválida');
    }
    $usuario = trim($usuario);
    $pass = trim($pass);
    if ($usuario === '' || $pass === '' || strlen($pass) > 128) {
        $error = 'El usuario o la contraseña son incorrectos';
    } else {
        $query = "Exec Z_LoguinWeb2 '$usuario','$pass'";
        $result = sqlsrv_query($conn, $query);
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        if (sqlsrv_has_rows($result)) {
            $_SESSION['logueo'] = true;
            while ($fila = sqlsrv_fetch_array($result)) {
                $_SESSION['vendedor'] = $fila['id'];
            }
            header('Location: index.php');
            exit;
        } else {
            $error = 'El usuario o la contraseña son incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="estilos.css" />
    <link href="/imagenes/favicon.webp" rel="icon" type="image/svg" sizes="16x16">
</head>
<body>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="h4 mb-3 text-center">Punto Deportivo</h1>
                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
                        <?php endif; ?>
                        <form method="post" action="login.php">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="palabra_secreta" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="palabra_secreta" name="palabra_secreta" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>