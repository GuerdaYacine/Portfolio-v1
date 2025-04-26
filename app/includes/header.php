<?php
$currentUser = $currentUser ?? false;
?>
<header class="flex w-full justify-evenly z-10 fixed items-center backdrop-blur-[3px]">
    <nav class="w-full flex items-center justify-between mx-[6%] py-[25px] lg:mx-[15%]">
        <a href="/" class="text-[#eff0f3] text-[4rem] rajdhani">Y<span class="text-[40px] text-[#145c9e]">.</span>G</a>
        <!-- Navbar large et medium -->
        <div class="hidden lg:flex items-center flex-row w-[896px]">
            <a href="#about" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">About me</a>
            <a href="#timeline" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">Timeline</a>
            <a href="#projects" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">Projects</a>
            <a href="#certifications" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">Certifications</a>
            <a href="#contact" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">Contact</a>
            <?php if ($currentUser) : ?>
                <button id="logout-modal-button" class="text-[1.8rem] text-[#eff0f3] gilmer mx-[3%] px-[24px] py-[8px] transition-all duration-300 ease-in-out hover:bg-[#1d1f25] hover:rounded-full">Logout</button>
                <!-- Modale de confirmation de déconnexion -->

            <?php endif; ?>
        </div>
        <!-- Navbar mobile -->
        <div class="lg:hidden">
            <div id="menu" class="cursor-pointer p-[10px]">
                <div class="w-[30px] h-[4px] bg-[#eff0f3] my-[6px]"></div>
                <div class="w-[30px] h-[4px] bg-[#eff0f3] my-[6px]"></div>
                <div class="w-[30px] h-[4px] bg-[#eff0f3] my-[6px]"></div>
            </div>
            <div id="menuButton" class="hidden rounded-[5px] absolute top-[70px] right-[70px] bg-[#0f131a] z-10 text-[18px] flex flex-col shadow-[0_0_1rem_rgba(0,0,0,0.2)]">
                <a href="#about"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">About me</span></a>
                <a href="#timeline"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">Timeline</span></a>
                <a href="#projects"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">Projects</span></a>
                <a href="#certifications"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">Certifications</span></a>
                <a href="#contact"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">Contact</span></a>
                <?php if ($currentUser) : ?>
                    <button id="logout-modal-button" class="text-left"><span class="text-[18px] text-[#eff0f3] px-[24px] py-[8px] rounded-[25px] inline-block transition-colors duration-300 hover:bg-[#1d1f25]">Logout</span></button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div id="logoutModal" class="h-screen fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 hidden">
        <div class="relative bg-[#1d1f25] flex justify-center items-center rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] min-h-[30vh] overflow-auto p-6">
            <div class="flex flex-col gap-6">
                <h2 class="text-[3.5rem] text-[#eff0f3] font-bold text-center">Confirmation de déconnexion</h2>
                <p class="text-[2rem] text-[#eff0f3] text-center">Êtes-vous sûr de vouloir vous déconnecter ?</p>
                <div class="flex justify-evenly mt-4">
                    <a href="/" class=" flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                        Annuler
                    </a>
                    <a href="/logout.php" class=" flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]" data-redirect="/logout.php">
                        Confirmer
                    </a>
                </div>
            </div>
            <button id="modal-close" class=" absolute top-4 right-4 text-[#eff0f3]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</header>

<script>
    // Sélectionner les éléments nécessaires
    const logoutModalButton = document.getElementById('logout-modal-button');
    const logoutModal = document.getElementById('logoutModal');
    const modalCloseButton = document.getElementById('modal-close');
    const modalContent = logoutModal ? logoutModal.querySelector('.relative') : null; // Contenu de la modale

    // Configurer l'état initial de l'animation en CSS
    if (modalContent) {
        modalContent.style.opacity = '0';
        modalContent.style.transition = 'opacity 0.3s ease-in-out';
    }

    // Fonction pour ouvrir la modale avec animation d'opacité
    function openLogoutModal() {
        if (logoutModal) {
            logoutModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Permettre au DOM de se mettre à jour avant de démarrer l'animation
            setTimeout(() => {
                if (modalContent) {
                    modalContent.style.opacity = '1';
                }
            }, 10);
        }
    }

    // Fonction pour fermer la modale avec animation d'opacité inverse
    function closeLogoutModal() {
        if (modalContent) {
            modalContent.style.opacity = '0';

            // Attendre la fin de l'animation avant de cacher la modale
            setTimeout(() => {
                if (logoutModal) {
                    logoutModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }, 300); // Durée égale à la transition CSS
        } else if (logoutModal) {
            // Fallback si le contenu n'est pas trouvé
            logoutModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Gérer les clics sur les boutons logout (tous les éléments ayant l'ID logout-modal-button)
    document.querySelectorAll('#logout-modal-button').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            openLogoutModal();
        });
    });

    // Ajouter un écouteur d'événement au bouton de fermeture (la croix)
    if (modalCloseButton) {
        modalCloseButton.addEventListener('click', closeLogoutModal);
    }

    // Ajouter un écouteur d'événement au bouton "Annuler"
    const cancelLogoutButton = logoutModal?.querySelector('a[href="/"]');
    if (cancelLogoutButton) {
        cancelLogoutButton.addEventListener('click', function(e) {
            e.preventDefault();
            closeLogoutModal();
        });
    }

    // Fermer la modale si l'utilisateur clique en dehors
    window.addEventListener('click', function(event) {
        if (event.target === logoutModal) {
            closeLogoutModal();
        }
    });
</script>