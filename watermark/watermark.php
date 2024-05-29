<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Crear la carpeta 'uploads' si no existe
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

echo "Directorio de uploads: " . $uploadDir . "<br>";
echo "Método de solicitud: " . $_SERVER['REQUEST_METHOD'] . "<br>";

echo "<pre>";
var_dump($_FILES);
echo "</pre>";

echo "<pre>";
var_dump($_POST);
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf']) && isset($_FILES['watermark'])) {
    $pdfPath = $uploadDir . basename($_FILES['pdf']['name']);
    $watermarkPath = $uploadDir . basename($_FILES['watermark']['name']);
    $outputPdfPath = $uploadDir . 'archivo_salida.pdf';

    if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK && $_FILES['watermark']['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath) && move_uploaded_file($_FILES['watermark']['tmp_name'], $watermarkPath)) {
            $command = "pdftk " . escapeshellarg($pdfPath) . " stamp " . escapeshellarg($watermarkPath) . " output " . escapeshellarg($outputPdfPath);
            $output = shell_exec($command . " 2>&1");

            echo "<pre>$command</pre>";
            echo "<pre>$output</pre>";

            if (file_exists($outputPdfPath)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($outputPdfPath) . '"');
                header('Content-Length: ' . filesize($outputPdfPath));
                readfile($outputPdfPath);

                unlink($pdfPath);
                unlink($watermarkPath);
                unlink($outputPdfPath);
                exit;
            } else {
                echo "Error al procesar el archivo PDF: $output<br>";
            }
        } else {
            echo "Error al mover los archivos subidos.<br>";
        }
    } else {
        echo "Error en la subida de archivos:<br>";
        echo "PDF error: " . $_FILES['pdf']['error'] . "<br>";
        echo "Watermark error: " . $_FILES['watermark']['error'] . "<br>";
    }
} else {
    echo "No se han enviado archivos correctamente.<br>";
}
?>
