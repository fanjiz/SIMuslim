<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dokumen'])) {
    $namaLengkap = $_POST['nama-lengkap'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['dokumen']['name']);
    $targetFilePath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['dokumen']['tmp_name'], $targetFilePath)) {
        // Jalankan OCR pada file yang diunggah
        $output = shell_exec("tesseract $targetFilePath stdout");
        
        // Simpan data ke database
        $conn = new mysqli("localhost", "root", "", "doc_verification");

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO pendakwah (nama_lengkap, email, password, dokumen_text) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $namaLengkap, $email, $password, $output);

        if ($stmt->execute()) {
            echo "Registrasi berhasil!";
        } else {
            echo "Terjadi kesalahan: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Gagal mengunggah file.";
    }
}
?>
