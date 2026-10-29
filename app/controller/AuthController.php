<?php
// app/controller/AuthController.php
require_once ROOT_PATH . 'app/model/User.php';

class AuthController {
    private $userModel;
    private $siteContentModel;

    public function __construct($connection) {
        $this->userModel = new User($connection);
        $this->siteContentModel = new SiteContent($connection);
    }

    public function login_gestion_2025() {
        $pageName = $this->siteContentModel->getValue('page_title');
        $copyrightText = $this->siteContentModel->getValue('copyright');
        $locationHeader = $this->siteContentModel->getValue('location_header');
        $alert = '';
        // Iniciar la sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Si ya hay una sesión activa, redirige al panel de administración
        if (!empty($_SESSION['active'])) {
            header('location: /admin/products');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (empty($_POST['usuario']) || empty($_POST['clave'])) {
                $alert = 'Ingrese su usuario y su clave';
            } else {
                $usuario = $_POST['usuario'];
                $clave = $_POST['clave']; // CORRECCIÓN: Se envía la clave sin encriptar

                $user = $this->userModel->getUserByCredentials($usuario, $clave);

                if ($user) {
                    // Credenciales correctas, se inicia la sesión
                    $_SESSION['active'] = true;
                    $_SESSION['idUser'] = $user['id_user']; 
                    $_SESSION['nombre'] = $user['name'];
                    $_SESSION['user'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];

                    // 1. Capturamos si el usuario marcó la casilla
                    $rememberMe = isset($_POST['remember_me']) && $_POST['remember_me'] == '1';

                    // 2. Guardamos la preferencia y la hora exacta del login
                    $_SESSION['remember_me'] = $rememberMe;
                    $_SESSION['last_activity'] = time();

                    // 3. Modificamos el tiempo de vida de la cookie de sesión
                    if ($rememberMe) {
                        // Si la quiere activa, le damos 30 días de vida a la cookie
                        $cookieLifetime = time() + (86400 * 30);
                    } else {
                        // Si no, le damos 6 horas (21600 segundos)
                        $cookieLifetime = time() + 21600;
                    }

                    // Reemplazar: setcookie(session_name(), session_id(), $cookieLifetime, "/");

                    // Por esto:
                    $params = session_get_cookie_params();
                    setcookie(session_name(), session_id(), $cookieLifetime, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                    
                    header('location: /admin/products');
                    exit;
                } else {
                    $alert = 'El usuario o la clave son incorrectos';
                    session_destroy();
                }
            }
        }

        // Carga la vista del formulario de login
        include ROOT_PATH . 'app/views/auth/login_gestion_2025.php';
    }

    public function logout() {
    // Forzamos la muerte de la cookie de sesión en el navegador
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruimos la sesión en el servidor
        session_unset();
        session_destroy();
        
        // Redirigimos al login exacto de administración
        header("Location: /auth/login_gestion_2025");
        exit();
    }
}
?>