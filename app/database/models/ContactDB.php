<?php

use PHPMailer\PHPMailer\PHPMailer;

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv("$name=$value");
    }
}

define('SMTP_USERNAME', getenv('SMTP_USERNAME'));
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD'));

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
            $errors['firstname'] = 'First name must contain only letters, spaces, or hyphens.';
        }

        if ($lastname && !preg_match($regexName, $lastname)) {
            $errors['lastname'] = 'Last name must contain only letters, spaces, or hyphens.';
        }

        if (!$email) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (!$subject) {
            $errors['subject'] = 'Please enter a subject.';
        } elseif (strlen($subject) < 10) {
            $errors['subject'] = 'Subject must be at least 10 characters long.';
        }

        if (!$message) {
            $errors['message'] = 'Please enter a message.';
        } elseif (strlen($message) < 20) {
            $errors['message'] = 'Message must be at least 20 characters long.';
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
            $mail->Username = 'guerda.yacine60100@gmail.com';
            $mail->Password = 'vyhv buuz ytmv jneh';
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

return new ContactDB($pdo);
