<?php
include '../koneksi.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_pengguna = $_POST['id_pengguna'];
    $password = $_POST['password']; // Get raw password from POST
    
    // Verifikasi session
    if ($id_pengguna != $_SESSION['id_pengguna']) {
        $response['message'] = 'Akses tidak sah';
        echo json_encode($response);
        exit;
    }
    
    // Ambil password dari database
    $query = mysqli_query($koneksi, "SELECT password FROM pengguna WHERE id_pengguna = '$id_pengguna'");
    
    if (mysqli_num_rows($query) == 0) {
        $response['message'] = 'Pengguna tidak ditemukan';
        echo json_encode($response);
        exit;
    }
    
    $data = mysqli_fetch_assoc($query);
    
    // Verifikasi password
    if (md5($password) == $data['password']) {
        $response['success'] = true;
        $response['message'] = 'Password valid';
    } else {
        $response['message'] = 'Password salah';
    }
} else {
    $response['message'] = 'Metode request tidak valid';
}

echo json_encode($response);
?>