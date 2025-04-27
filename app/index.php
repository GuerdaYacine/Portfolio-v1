<?php
ob_start();
require __DIR__ . '/database/db.php';
$authDB = require_once __DIR__ . '/database/security.php';
$currentUser = $authDB->isLoggedIn();

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Hi, I'm Guerda Yacine, I'm studying computer science at Saint Vincent High School in Senlis. I'm actually learning in the way to be a Full-Stack Web Developer.">
  <title>Portfolio</title>
  <link rel="icon" href="/assets/images/favicon.jpg" type="image/png">
  <link rel="shortcut icon" href="/assets/images/favicon.ico" type="image/x-icon">
  <link rel="apple-touch-icon" href="/assets/images/apple-touch-icon.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
  <?php include 'includes/header.php'; ?>
  <main>
    <?php include 'pages/about.php'; ?>
    <!-- Elfsight Cookie Consent | Untitled Cookie Consent -->
    <script src="https://static.elfsight.com/platform/platform.js" async></script>
    <div class="elfsight-app-b862c82a-932d-4738-8472-2bdcdf08ab26" data-elfsight-app-lazy></div>
    <?php include 'pages/timeline.php'; ?>
    <div class="card border border-white/80 w-[70%] mx-auto"></div>
    <?php include 'pages/projects.php'; ?>
    <div class="card border border-white/80 w-[70%] mx-auto"></div>
    <?php include 'pages/certifications.php'; ?>
    <div class="card border border-white/80 w-[70%] mx-auto"></div>
    <?php include 'pages/contact.php'; ?>
  </main>
  <?php include 'includes/footer.php'; ?>
  <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
  <script src="assets/js/script.js"></script>
</body>

</html>

<?php
ob_end_flush();
?>