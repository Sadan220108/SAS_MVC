<?php
include_once __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../models/AdminModel.php';
include_once __DIR__ . '/../models/ParkirModel.php';

class AdminController {

    private $AdminModel;
    private $ParkirModel;
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->AdminModel = new AdminModel($this->db);
        $this->ParkirModel = new ParkirModel($this->db);
    }

    // halaman login
    public function index() {
        if (isset($_SESSION['admin_id'])) {
            header('Location: index.php?act=dashboard');
            exit;
        }
        include __DIR__ . '/../views/auth/index.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $admin = $this->AdminModel->login($username, $password);

        if ($admin) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['username'] = $admin['username'];
            header('Location: index.php?act=dashboard');
            exit;
        }

        $error = 'Username atau password salah!';
        include __DIR__ . '/../views/auth/index.php';
    }

    public function dashboard() {
        if (!isset($_SESSION['admin_id'])) {
            header('Location: index.php');
            exit;
        }

        $stat = $this->ParkirModel->statistik();
        $dataParkir = $this->ParkirModel->getAktif();

        include __DIR__ . '/../views/dashboard/index.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
