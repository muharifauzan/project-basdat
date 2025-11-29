
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'dbadmin.php'; 

header("Content-Type: application/json");

function valid_kontak($kontak) {
    return preg_match('/^[0-9+]{10,15}$/', $kontak);
}

$allowed_roles = [
    'SuperAdmin',
    'Admin Fakultas',
    'Admin Departemen',
    'Admin DPKU',
    'Admin DUI'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_contains($_SERVER['CONTENT_TYPE'], 'application/json')) {

    $input = json_decode(file_get_contents("php://input"), true);

    $nama_admin = trim($input['nama_admin'] ?? '');
    $kontak     = trim($input['kontak'] ?? '');
    $peran      = trim($input['peran'] ?? '');

    if ($nama_admin === '' || $kontak === '' || $peran === '') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Semua field wajib diisi"]);
        exit;
    }

    if (!valid_kontak($kontak)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Format kontak tidak valid (10–15 digit)"]);
        exit;
    }

    if (!in_array($peran, $allowed_roles)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Peran tidak valid"]);
        exit;
    }

    $query = "INSERT INTO admin (nama_admin, kontak, peran)
              VALUES ($1, $2, $3) RETURNING id_admin";

    $res = pg_query_params($conn, $query, [$nama_admin, $kontak, $peran]);

    if (!$res) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => pg_last_error($conn)]);
        exit;
    }

    $id_admin = pg_fetch_result($res, 0, 0);

    echo json_encode([
        "success" => true,
        "message" => "Admin berhasil dibuat",
        "data" => [
            "id_admin" => $id_admin,
            "nama_admin" => $nama_admin,
            "kontak" => $kontak,
            "peran" => $peran
        ]
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {

    $nama_admin = trim($_POST['nama_admin']);
    $kontak     = trim($_POST['kontak']);
    $peran      = trim($_POST['peran']);

    $error = "";

    if ($nama_admin === '' || $kontak === '' || $peran === '') {
        $error = "Semua field wajib diisi.";
    } elseif (!valid_kontak($kontak)) {
        $error = "Kontak harus 10–15 digit angka.";
    } elseif (!in_array($peran, $allowed_roles)) {
        $error = "Peran tidak valid.";
    }

    if ($error === "") {

        $query = "INSERT INTO admin (nama_admin, kontak, peran)
                  VALUES ($1, $2, $3) RETURNING id_admin";

        $res = pg_query_params($conn, $query, [$nama_admin, $kontak, $peran]);

        if ($res) {
            $id_admin = pg_fetch_result($res, 0, 0);
            $_SESSION['id_admin'] = $id_admin;

            header("Location: adminDashboard.php");
            exit;
        } else {
            $error = "Gagal insert data: " . pg_last_error($conn);
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Admin</title>
</head>
<body>
<h2>Tambah Admin Baru</h2>

<?php if (!empty($error)) { ?>
<p style="color:red;"><?= $error ?></p>
<?php } ?>

<form method="POST">
    <label>Nama Admin:</label><br>
    <input type="text" name="nama_admin" required><br><br>

    <label>Kontak:</label><br>
    <input type="text" name="kontak" required><br><br>

    <label>Peran:</label><br>
    <select name="peran" required>
        <option value="">--Pilih Peran--</option>
        <option value="SuperAdmin">SuperAdmin</option>
        <option value="Admin Fakultas">Admin Fakultas</option>
        <option value="Admin Departemen">Admin Departemen</option>
        <option value="Admin DPKU">Admin DPKU</option>
        <option value="Admin DUI">Admin DUI</option>
    </select>
    <br><br>

    <button name="create_admin">Tambah Admin</button>
</form>
</body>
</html>
