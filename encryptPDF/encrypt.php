<?php

/**
 * Clase para encriptar un archivo PDF utilizando qpdf.
 */
class PdfEncrypter
{
    /**
     * Encripta un archivo PDF utilizando qpdf.
     *
     * @param string $inputPdf Ruta del archivo PDF de entrada.
     * @param string $outputPdf Ruta del archivo PDF de salida.
     * @param string $password Contraseña para la encriptación.
     * @return bool True si la encriptación fue exitosa, false en caso contrario.
     */
    public function encryptPdf(string $inputPdf, string $outputPdf, string $password): bool
    {
        $command = escapeshellcmd("qpdf --encrypt $password $password 256 -- $inputPdf $outputPdf");
        $output = null;
        $return_var = null;
        exec($command, $output, $return_var);
        return $return_var === 0;
    }
}

// Verificar si el formulario ha sido enviado y si se ha proporcionado el archivo PDF y la contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf']) && isset($_POST['password'])) {
    $uploadDir = __DIR__ . '/uploads/'; // Directorio de carga
    $password = $_POST['password']; // Contraseña para la encriptación

    // Crear el directorio de carga si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Procesar el archivo PDF cargado
    if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $inputPdf = $uploadDir . basename($_FILES['pdf']['name']); // Establecer la ruta de destino
        // Mover el archivo cargado al directorio de destino
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $inputPdf)) {
            $outputPdf = $uploadDir . 'archivo_encriptado.pdf'; // Ruta del archivo PDF encriptado
            $encrypter = new PdfEncrypter(); // Crear una instancia de la clase encriptadora
            if ($encrypter->encryptPdf($inputPdf, $outputPdf, $password)) {
                // Forzar la descarga del archivo PDF encriptado
                if (file_exists($outputPdf)) {
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . basename($outputPdf) . '"');
                    header('Content-Length: ' . filesize($outputPdf));
                    readfile($outputPdf);

                    // Eliminar archivos temporales
                    unlink($inputPdf);
                    unlink($outputPdf);
                    exit;
                } else {
                    echo "Error al crear el archivo PDF encriptado.<br>";
                }
            } else {
                echo "Error al encriptar el archivo PDF.<br>";
            }
        } else {
            echo "Error al mover el archivo: " . $_FILES['pdf']['name'] . "<br>";
        }
    } else {
        echo "Error al cargar el archivo PDF: " . $_FILES['pdf']['name'] . " - Código de error: " . $_FILES['pdf']['error'] . "<br>";
    }
} else {
    echo "No se han enviado el archivo PDF o la contraseña.<br>";
}
?>
