<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


require_once __DIR__ . '../../../vendor/autoload.php';
require_once __DIR__ . '../../../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '../../../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '../../../vendor/phpmailer/phpmailer/src/PHPMailer.php';


$mail = new PHPMailer(true);

try {
    // Configurações do servidor SMTP
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;// Mostra logs detalhados
    $mail->isSMTP();
    $mail->Host = 'smtp.office365.com';  // Ex: smtp.gmail.com
    $mail->SMTPAuth = true;
    $mail->Username = \\; // Seu e-mail
    $mail->Password = "|;           // Sua senha
    $mail->SMTPSecure = 'tls';                  // ou 'ssl'
    $mail->Port = 465;                    // ou 465 para SSL

    // Remetente e destinatário
    $mail->setFrom('brunorodriguesbsr@outlook.com', 'Bruno Rodrigues'); // Remetente
    $mail->addAddress('brunorodriguesbsr@gmail.com', 'Bruno Rodrigues'); // Destinatário

    // Conteúdo do e-mail
    $mail->isHTML(true);
    $mail->Subject = 'Assunto do E-mail';
    $mail->Body = '<b>teste</b>';
    $mail->AltBody = 'texto de teste';

    if ($mail->send()) {
        echo "E-mail enviado com sucesso!";
    } else {
        echo "Falha ao enviar e-mail.";
    }

} catch (Exception $e) {
    echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}


?>
