<?php
require_once "db.php";

$query = "
    SELECT 
        a.id_admin,
        a.nama_admin,
        a.kontak,
        a.peran
    FROM admin a
    ORDER BY a.id_admin;
";

$result = pg_query($conn, $query);

if (!$result) {
    die("Query gagal: " . pg_last_error());
}

echo "<h2>Daftar Admin</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr>
        <th>ID Admin</th>
        <th>Nama Admin</th>
        <th>Kontak</th>
        <th>Peran</th>
      </tr>";

while ($row = pg_fetch_assoc($result)) {
    echo "<tr>
            <td>{$row['id_admin']}</td>
            <td>{$row['nama_admin']}</td>
            <td>{$row['kontak']}</td>
            <td>{$row['peran']}</td>
          </tr>";
}

echo "</table>";
?>
