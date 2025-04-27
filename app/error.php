<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Une erreur est survenue.">
    <title>Erreur</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-[#0f0e17] text-[#eff0f3] min-h-screen flex flex-col">

    <section class="flex items-center justify-center flex-grow px-4">
        <div class="container mx-auto flex flex-col lg:flex-row items-center justify-center gap-8 py-16">
            <div class="w-full lg:w-1/2 flex justify-center">
                <div class="relative">
                    <h1 id="error-code" class="text-[12rem] font-extrabold text-[#145C9E] opacity-80 animate-pulse">Oops !</h1>

                    <div class="absolute top-1/4 -left-10 w-20 h-20 bg-[#145C9E]/20 rounded-full blur-xl"></div>
                    <div class="absolute bottom-1/4 -right-10 w-32 h-32 bg-[#145C9E]/20 rounded-full blur-xl"></div>
                </div>
            </div>

            <!-- Error message -->
            <div class="w-full lg:w-1/2 text-center lg:text-left">
                <h2 class="text-[28px] font-extrabold text-[#145C9E] mb-6" id="error-title">Une erreur est survenue</h2>

                <p class="text-[20px] font-semibold opacity-90 mb-8" id="error-message">
                    Nous rencontrons un problème. Veuillez réessayer plus tard ou revenir à la page d'accueil.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="/" class="px-8 py-3 bg-[#145C9E] text-white rounded-md font-semibold text-[20px] hover:bg-[#0f4c81] transition-colors duration-300">
                        Retourner à l'accueil
                    </a>
                </div>
            </div>
        </div>
    </section>
</body>

</html>