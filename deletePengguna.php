<?php
require_once "db.php";

if (!isset($_GET['id'])) {
    die("ID Pengguna diperlukan");
}

$id = $_GET['id'];

$cek = pg_query_params($conn, "SELECT * FROM pengguna WHERE id_pengguna = $1", array($id));
if (pg_num_rows($cek) === 0) {
    die("Pengguna tidak ditemukan");
}

pg_query_params($conn, "DELETE FROM mahasiswa WHERE id_pengguna = $1", array($id));
pg_query_params($conn, "DELETE FROM dosen WHERE id_pengguna = $1", array($id));

$query = "DELETE FROM pengguna WHERE id_pengguna = $1";
$result = pg_query_params($conn, $query, array($id));

if ($result) {
    echo "Pengguna berhasil dihapus.";
} else {
    echo "Gagal menghapus pengguna: " . pg_last_error($conn);
}
?>
