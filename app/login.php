<?php
$pdo = require_once 'database/db.php';
$authDB = require_once __DIR__ . '/database/security.php';
$currentUser = $authDB->isLoggedIn();
if ($currentUser) {
    header('Location: /');
}
$errorsLogin = [
    'email' => '',
    'password' => '',
    'general' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = filter_input_array(INPUT_POST, [
        'user-email' => FILTER_SANITIZE_EMAIL,
    ]);
    $email = $input['user-email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $errorsLogin['email'] = 'L\'email est requise';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorsLogin['email'] = 'Veuillez saisir une email valide';
    }

    if (!$password) {
        $errorsLogin['password'] = 'Veuillez saisir un mot de passe';
    }


    if (empty(array_filter($errorsLogin, fn($e) => $e !== ''))) {
        $user = $authDB->getUserFromEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $errorsLogin['general'] = 'Email ou mot de passe incorrect';
        } else {
            $authDB->login($user['id']);
            header('Location: /');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="/assets/images/favicon.jpg" type="image/png">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900;1,14..32,100..900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <section class="flex flex-col items-center justify-center h-[100vh]">
        <div class=" px-[25px] bg-[#1d1f25] w-[90%] md:w-[50%] lg:w-[30%] rounded-[15px] flex justify-center items-center gap-[10px] flex-col h-[50vh]">
            <h5 class="text-[#eff0f3] text-[4rem] inter">Login</h5>

            <form action="login.php" method="POST" class="flex flex-col justify-center w-full md:w-[90%]">


                <div class="flex flex-col mb-[20px]">
                    <label for="user-email" class="text-[20px] font-light text-[#eff0f3] inter">Email</label>
                    <input type="email" id="user-email" name="user-email" placeholder="Entrez votre nom d'utilisateur" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[5px]" value="<?= $email ?? '' ?>">
                    <?php if ($errorsLogin['email']) : ?>
                        <span class="text-red-500 text-[14px] mt-1 "><?= $errorsLogin['email'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col mb-[20px]">
                    <label for="password" class="text-[20px] font-light text-[#eff0f3] inter">Password</label>
                    <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[5px]">
                    <?php if ($errorsLogin['password']) : ?>
                        <span class="text-red-500 text-[14px] mt-1 "><?= $errorsLogin['password'] ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($errorsLogin['general']) : ?>
                    <span class="text-center text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsLogin['general'] ?></span>
                <?php endif; ?>

                <div class="flex justify-center items-center">
                    <button class="px-[30px] py-[10px] rounded-full cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">Log in</button>
                </div>
            </form>
        </div>
    </section>
</body>

</html>