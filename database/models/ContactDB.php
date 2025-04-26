<?php

use PHPMailer\PHPMailer\PHPMailer;

class ContactDB
{
    private PDOStatement $statementCreateContact;

    function __construct(private PDO $pdo)
    {
        $this->statementCreateContact = $pdo->prepare('INSERT INTO messages (
            firstname,
            lastname,
            email,
            subject,
            message,
            sent_at
        ) VALUES (
            :firstname,
            :lastname,
            :email,
            :subject,
            :message,
            :date
        )');
    }

    function createMessage(string $firstname, string $lastname, string $email, string $subject, string $message): bool
    {
        $sent_at = date('Y-m-d H:i:s');

        return $this->statementCreateContact->execute([
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'date' => $sent_at
        ]);
    }

    function validateContactData(string $firstname, string $lastname, string $email, string $subject, string $message): array
    {
        $errors = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'subject' => '',
            'message' => ''
        ];

        $regexName = "/^[a-zA-ZÀ-ÿ\- ]+$/u";

        if ($firstname && !preg_match($regexName, $firstname)) {
            $errors['firstname'] = 'Le prénom ne doit contenir que des lettres, espaces ou tirets.';
        }

        if ($lastname && !preg_match($regexName, $lastname)) {
            $errors['lastname'] = 'Le nom ne doit contenir que des lettres, espaces ou tirets.';
        }

        if (!$email) {
            $errors['email'] = 'L\'email est requise';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Veuillez saisir une adresse email valide';
        }

        if (!$subject) {
            $errors['subject'] = 'Veuillez saisir un sujet';
        } elseif (strlen($subject) < 10) {
            $errors['subject'] = 'Le sujet doit faire au moins 10 caractères';
        }

        if (!$message) {
            $errors['message'] = 'Veuillez saisir le contenu du message';
        } elseif (strlen($message) < 20) {
            $errors['message'] = 'Le message doit faire au moins 20 caractères';
        }

        return $errors;
    }

    function sendEmail(string $firstname, string $lastname, string $email, string $subject, string $message): bool
    {
        try {
            $sent_at = date('Y-m-d H:i:s');

            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('guerda.yacine60100@gmail.com', 'Formulaire de contact');
            $mail->addAddress('guerda.yacine60100@gmail.com', 'Yacine Guerda');
            $mail->addReplyTo($email, $firstname . ' ' . $lastname);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Nouveau message de contact !';

            $mail->Body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #145C9E; color: white; padding: 10px; text-align: center; }
                        .content { padding: 20px; background-color: #f9f9f9; }
                        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Nouveau message de contact</h2>
                        </div>
                        <div class='content'>
                            <p><strong>Prénom:</strong> " . $firstname . "</p>
                            <p><strong>Nom:</strong> " . $lastname . "</p>
                            <p><strong>Email:</strong> " . $email . "</p>
                            <p><strong>Sujet:</strong> " . $subject . "</p>
                            <p><strong>Message:</strong></p>
                            <p>" . nl2br($message) . "</p>
                            <p><strong>Date d'envoi:</strong> " . $sent_at . "</p>
                        </div>
                        <div class='footer'>
                            <p>Ce message a été envoyé depuis le formulaire de contact de votre site web.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log('Erreur d\'envoi d\'email: ' . $e->getMessage());
            return false;
        }
    }
}
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}

define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));
return new ContactDB($pdo);
