<?php
date_default_timezone_set('Europe/Paris');

require __DIR__ . '/../vendor/autoload.php';

global $pdo;
$contactDB = require_once './database/models/ContactDB.php';

$errorsContact = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'subject' => '',
    'message' => ''
];

$firstname = '';
$lastname = '';
$email = '';
$subject = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addMessage'])) {
    $_POST = filter_input_array(INPUT_POST, [
        'firstname' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'lastname' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'email' => FILTER_SANITIZE_EMAIL,
        'subject' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'message' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    ]);

    $firstname = $_POST['firstname'] ?? '';
    $lastname = $_POST['lastname'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    $errorsContact = $contactDB->validateContactData($firstname, $lastname, $email, $subject, $message);

    if (empty(array_filter($errorsContact, fn($e) => $e !== ''))) {
        $contactDB->createMessage($firstname, $lastname, $email, $subject, $message);

        $emailSent = $contactDB->sendEmail($firstname, $lastname, $email, $subject, $message);

        if ($emailSent) {
            header('Location: /?message_sent=true#');
            exit;
        } else {
            $errorsContact['message'] = 'Une erreur s\'est produite lors de l\'envoi de l\'email. Veuillez réessayer plus tard.';
        }
    }
}
?>
<?php if (isset($_GET['message_sent']) && $_GET['message_sent'] === 'true') : ?>
    <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        <span>Votre message a été envoyé avec succès !</span>
    </div>
<?php endif; ?>

<section class="flex flex-col items-center justify-center py-[100px] gap-[50px]" id="contact">
    <h5 class="card text-[#eff0f3] text-[4rem] inter">Contact me</h5>
    <div class="card p-[25px] bg-[#1d1f25] w-[90%] md:w-[80%] rounded-[15px] flex justify-center items-center">
        <form action="" method="POST" class="flex flex-col justify-center w-full md:w-[50%]">
            <label for="firstname" class="text-[20px] font-light text-[#eff0f3] inter">First name :</label>
            <input type="text" id="firstname" name="firstname" placeholder="Enter your first name" value="<?= $firstname ?? '' ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[20px] <?= $errorsContact['firstname'] ? 'border-2 border-red-500 mb-[0px]' : '' ?>">
            <?php if ($errorsContact['firstname']) : ?>
                <span class="text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsContact['firstname'] ?></span>
            <?php endif; ?>

            <label for="lastName" class="text-[20px] font-light text-[#eff0f3] inter">Last name :</label>
            <input type="text" id="lastName" name="lastname" placeholder="Enter your last name" value="<?= $lastname ?? '' ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[20px] <?= $errorsContact['lastname'] ? 'border-2 border-red-500 mb-[0px]' : '' ?>">
            <?php if ($errorsContact['lastname']) : ?>
                <span class="text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsContact['lastname'] ?></span>
            <?php endif; ?>


            <label for="email" class="text-[20px] font-light text-[#eff0f3] inter">Email <span class="text-[red]">*</span> :</label>
            <input type="email" id="email" name="email" placeholder="example@domain.com" value="<?= $email ?? '' ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[20px] <?= $errorsContact['email'] ? 'border-2 border-red-500 mb-[0px]' : '' ?>">
            <?php if ($errorsContact['email']) : ?>
                <span class="text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsContact['email'] ?></span>
            <?php endif; ?>


            <label for="subject" class="text-[20px] font-light text-[#eff0f3] inter">Subject <span class="text-[red]">*</span> :</label>
            <input type="text" maxlength="255" id="subject" name="subject" placeholder="Subject of your message" value="<?= $subject ?? '' ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none mt-[8px] mb-[20px] <?= $errorsContact['subject'] ? 'border-2 border-red-500 mb-[0px]' : '' ?>">
            <?php if ($errorsContact['subject']) : ?>
                <span class="text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsContact['subject'] ?></span>
            <?php endif; ?>


            <label for="message" class="text-[20px] font-light text-[#eff0f3] inter">Message <span class="text-[red]">*</span> :</label>
            <textarea id="message" name="message" placeholder="Write your message here" class="inter text-[18px] p-[8px] rounded-[5px] outline-none min-h-[100px] mt-[8px] mb-[20px] <?= $errorsContact['message'] ? 'border-2 border-red-500 mb-[0px]' : '' ?>"><?= $message ?? '' ?></textarea>
            <?php if ($errorsContact['message']) : ?>
                <span class="text-red-500 text-[14px] mt-1 mb-[20px]"><?= $errorsContact['message'] ?></span>
            <?php endif; ?>


            <div class="flex justify-center items-center">
                <button type="submit" name="addMessage" class="px-[30px] py-[10px] rounded-full cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">Submit</button>
            </div>
        </form>
    </div>
</section>