<?php

/**
 * Clase para convertir imágenes a un archivo PDF.
 */
class ImageToPdfConverter
{
    /**
     * Convierte un conjunto de imágenes a un archivo PDF.
     *
     * @param array $imagePaths Rutas de las imágenes a convertir.
     * @param string $outputPdf Ruta del archivo PDF de salida.
     * @return string Ruta del archivo PDF creado.
     */
    public function convertToPdf(array $imagePaths, string $outputPdf): string
    {
        // Crear una nueva instancia de Imagick
        $pdf = new Imagick();
        $pdf->setCompressionQuality(100); // Establecer la calidad de compresión al 100%

        // Iterar sobre cada ruta de imagen
        foreach ($imagePaths as $imagePath) {
            $image = new Imagick($imagePath); // Cargar la imagen
            $pdf->addImage($image); // Agregar la imagen al documento PDF
            $pdf->setImageFormat('pdf'); // Establecer el formato de la imagen como PDF
        }

        // Escribir todas las imágenes en un solo archivo PDF
        $pdf->writeImages($outputPdf, true);

        return $outputPdf; // Devolver la ruta del archivo PDF creado
    }
}

// Verificar si el formulario ha sido enviado y si se han cargado imágenes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $imagePaths = [];
    $uploadDir = __DIR__ . '/uploads/'; // Directorio de carga

    // Crear el directorio de carga si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Procesar cada imagen cargada
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        // Verificar si hubo errores al cargar la imagen
        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) {
            echo "Error al cargar el archivo: " . $_FILES['images']['name'][$key] . " - Código de error: " . $_FILES['images']['error'][$key] . "<br>";
            continue;
        }

        $filePath = $uploadDir . basename($_FILES['images']['name'][$key]); // Establecer la ruta de destino
        // Mover el archivo cargado al directorio de destino
        if (move_uploaded_file($tmpName, $filePath)) {
            $imagePaths[] = $filePath; // Agregar la ruta del archivo al array de rutas de imágenes
        } else {
            echo "Error al mover el archivo: " . $_FILES['images']['name'][$key] . "<br>";
        }
    }

    // Verificar si se han cargado imágenes
    if (!empty($imagePaths)) {
        $outputPdf = $uploadDir . 'archivo_salida.pdf'; // Ruta del archivo PDF de salida
        $converter = new ImageToPdfConverter(); // Crear una instancia de la clase convertidora
        $pdfPath = $converter->convertToPdf($imagePaths, $outputPdf); // Convertir las imágenes a PDF

        // Forzar la descarga del archivo PDF
        if (file_exists($pdfPath)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($pdfPath) . '"');
            header('Content-Length: ' . filesize($pdfPath));
            readfile($pdfPath);

            // Eliminar archivos temporales
            foreach ($imagePaths as $imagePath) {
                unlink($imagePath);
            }
            unlink($pdfPath);
            exit;
        } else {
            echo "Error al crear el archivo PDF.<br>";
        }
    } else {
        echo "No se han subido imágenes correctamente.<br>";
    }
} else {
    echo "No se han enviado imágenes.<br>";
}
?>
