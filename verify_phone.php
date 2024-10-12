<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye el autoload de Composer al principio
require __DIR__ . '/vendor/autoload.php';  // Para usar Twilio SDK

use Twilio\Rest\Client;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'] ?? null;
    $method = $_POST['method'] ?? null;

    if ($phone && $method) {
        // Verifica si Twilio Client está disponible
        if (class_exists('Twilio\Rest\Client')) {
            echo "Autoload funcionando correctamente.<br>";
        } else {
            echo "Error cargando dependencias.<br>";
        }

        // Configura Twilio
        $twilio_sid = 'ACa7ee92cde5059552a6dba15d4e4efc06';
        $twilio_token = 'c6a90b02f0c435cf4d7ff6b590935772';
        $twilio_phone = '+18645394182';

        $client = new Client($twilio_sid, $twilio_token);

        // Genera el código de verificación
        $code = rand(100000, 999999);

        try {
            if ($method == 'sms') {
                // Enviar código por SMS
                $client->messages->create(
                    $phone,
                    [
                        'from' => $twilio_phone,
                        'body' => "Tu código de verificación es: $code"
                    ]
                );
            } elseif ($method == 'whatsapp') {
                // Enviar código por WhatsApp
                $client->messages->create(
                    "whatsapp:$phone",
                    [
                        'from' => "whatsapp:$twilio_phone",
                        'body' => "Tu código de verificación es: $code"
                    ]
                );
            }

            // Almacenar el número y código en la base de datos
            $mysqli = new mysqli('localhost', 'root', '', 'telefono_verificacion');
            if ($mysqli->connect_error) {
                die('Error de conexión a la base de datos: ' . $mysqli->connect_error);
            }

            $stmt = $mysqli->prepare("INSERT INTO verificacion (numero_telefono, codigo) VALUES (?, ?)");
            $stmt->bind_param("ss", $phone, $code);
            $stmt->execute();

            echo "Código de verificación enviado. Revisa tu $method.";

        } catch (Exception $e) {
            echo "Error al enviar el mensaje: " . $e->getMessage();
        }

    } else {
        echo "No se han recibido los datos del formulario.";
    }
} else {
    echo "Método no permitido.";
}
?>
