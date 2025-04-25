<?php

class AuthDB
{
    private PDOStatement $statementReadSession;
    private PDOStatement $statementCreateSession;
    private PDOStatement $statementReadUser;
    private PDOStatement $statementReadUserFromEmail;
    private PDOStatement $stamtementDeleteSession;

    function __construct(private PDO $pdo)
    {
        $this->statementReadSession = $pdo->prepare('SELECT * FROM session WHERE id=:id');

        $this->statementReadUser = $pdo->prepare('SELECT * FROM user WHERE id=:id');
        $this->statementReadUserFromEmail = $pdo->prepare('SELECT * FROM user WHERE email=:email');
        $this->statementCreateSession = $pdo->prepare('INSERT INTO session VALUES (
            :sessionid,
            :userid
        )');
        $this->stamtementDeleteSession = $pdo->prepare('DELETE FROM session WHERE id=:id');
    }

    function login(string $userId): void
    {
        $sessionId = bin2hex(random_bytes(32));
        $this->statementCreateSession->bindParam(':userid', $userId);
        $this->statementCreateSession->bindParam(':sessionid', $sessionId);
        $this->statementCreateSession->execute();
        $signature = hash_hmac('sha256', $sessionId, SECRET_KEY);
        setcookie('session', $sessionId, [
            'expires' => time() + 60 * 60 * 24 * 14,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('signature', $signature, [
            'expires' => time() + 60 * 60 * 24 * 14,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        return;
    }

    function isLoggedIn(): array | false
    {
        $sessionId = $_COOKIE['session'] ?? '';
        $signature = $_COOKIE['signature'] ?? '';
        if ($sessionId && $signature) {
            $hash = hash_hmac('sha256', $sessionId, SECRET_KEY);
            if (hash_equals($hash, $signature)) {
                $this->statementReadSession->bindParam(':id', $sessionId);
                $this->statementReadSession->execute();
                $session = $this->statementReadSession->fetch();
                if ($session) {
                    $this->statementReadUser->bindParam(':id', $session['userid']);
                    $this->statementReadUser->execute();
                    $user = $this->statementReadUser->fetch();
                }
            }
        }
        return $user ?? false;
    }

    function logOut(string $sessionId): void
    {

        $this->stamtementDeleteSession->bindParam(':id', $sessionId);
        $this->stamtementDeleteSession->execute();
        setcookie('session', '', [
            'expires' => time() - 1,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        setcookie('signature', '', [
            'expires' => time() - 1,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        return;
    }

    function getUserFromEmail(string $email): array | false
    {
        $this->statementReadUserFromEmail->bindParam(':email', $email);
        $this->statementReadUserFromEmail->execute();
        return $this->statementReadUserFromEmail->fetch();
    }
}
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
define('SECRET_KEY', getenv('SECRET_KEY'));


return new AuthDB($pdo);
