<?php

/**
 * Clase para abrir un archivo PDF en modo presentación utilizando pdfpc.
 */
class PdfPresenter
{
    /**
     * Abre un archivo PDF en modo presentación utilizando pdfpc.
     *
     * @param string $pdfFile Ruta del archivo PDF a presentar.
     * @return bool True si el comando se ejecutó correctamente, false en caso contrario.
     */
    public function presentPdf(string $pdfFile): bool
    {
        $command = escapeshellcmd("pdfpc " . escapeshellarg($pdfFile));
        $output = null;
        $return_var = null;
        exec($command, $output, $return_var);
        return $return_var === 0;
    }
}

// Verificar si el formulario ha sido enviado y si se ha proporcionado el archivo PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $uploadDir = __DIR__ . '/uploads/'; // Directorio de carga

    // Crear el directorio de carga si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Procesar el archivo PDF cargado
    if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $pdfFile = $uploadDir . basename($_FILES['pdf']['name']); // Establecer la ruta de destino
        // Mover el archivo cargado al directorio de destino
        if (move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfFile)) {
            $presenter = new PdfPresenter(); // Crear una instancia de la clase presentadora
            if ($presenter->presentPdf($pdfFile)) {
                echo "Presentación iniciada correctamente.";
            } else {
                echo "Error al iniciar la presentación.";
            }
        } else {
            echo "Error al mover el archivo: " . $_FILES['pdf']['name'] . "<br>";
        }
    } else {
        echo "Error al cargar el archivo PDF: " . $_FILES['pdf']['name'] . " - Código de error: " . $_FILES['pdf']['error'] . "<br>";
    }
} else {
    echo "No se ha enviado el archivo PDF.<br>";
}
?>
