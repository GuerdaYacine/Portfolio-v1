document.addEventListener('DOMContentLoaded', function() {// Sélectionne les éléments
const menuButton = document.getElementById('menuButton');
const menu = document.getElementById('menu');

// Affiche ou cache le menuButton au clic sur le menu
menu.addEventListener('click', function (event) {
    // Empêche la propagation de l'événement pour éviter que le clic sur le menuButton ferme immédiatement le menuButton
    event.stopPropagation();
    menuButton.classList.toggle('hidden');
});

// Cache le menuButton si l'utilisateur clique ailleurs sur la page
document.addEventListener('click', function (event) {
    if (!menu.contains(event.target) && !menuButton.contains(event.target)) {
        menuButton.classList.add('hidden');
    }
});

// Cacher le menuButton lorsque l'utilisateur clique sur un lien dans la div
const links = menuButton.querySelectorAll('a');  // Sélectionne tous les liens dans la div menuButton
links.forEach(link => {
    link.addEventListener('click', function (event) {
        // Empêche la propagation du clic pour éviter que le clic sur le lien ne déclenche d'autres événements
        event.stopPropagation();
        // Cache le menuButton après le clic sur un lien
        menuButton.classList.add('hidden');
    });
});


// Auto type
var typer = new Typed(".auto-type",{
    strings : ['Integration', 'Web developpement', 'Design'],
    typeSpeed : 100,
    backSpeed : 50,
    loop : true
});



// Slider

    const sliderWrapper = document.querySelector('.slider-wrapper');
    const dots = document.querySelectorAll('.slider-dot');
    const slidesCount = window.CERTIFICATIONS_COUNT || dots.length;
    let currentSlide = 0;
    let autoSlideInterval;

    // Function to display a specific slide
    function showSlide(index) {
        if (index < 0) {
            index = slidesCount - 1;
        } else if (index >= slidesCount) {
            index = 0;
        }
        
        currentSlide = index;
        
        // Calculate the exact height to translate
        const slideHeight = document.querySelector('.slide').offsetHeight;
        sliderWrapper.style.transform = `translateY(-${currentSlide * slideHeight}px)`;
        
        // Update indicators
        dots.forEach((dot, i) => {
            dot.classList.remove('bg-[#ccc]');
            dot.classList.add('bg-[#555]');
            if (i === currentSlide) {
                dot.classList.remove('bg-[#555]');
                dot.classList.add('bg-[#ccc]');
            }
        });
    }

    // Auto-scroll every 5 seconds
    function startAutoSlide() {
        autoSlideInterval = setInterval(() => {
            showSlide(currentSlide + 1);
        }, 8000); // 8 seconds
    }

    // Reset timer on user interaction
    function resetTimer() {
        clearInterval(autoSlideInterval);
        startAutoSlide();
    }

    // Add events to navigation dots
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            resetTimer();
        });
    });

    // Start auto-scroll
    startAutoSlide();
    
    // Add touch navigation (vertical swipe)
    let touchStartY = 0;
    let touchEndY = 0;
    
    sliderWrapper.addEventListener('touchstart', (e) => {
        touchStartY = e.changedTouches[0].screenY;
    });
    
    sliderWrapper.addEventListener('touchend', (e) => {
        touchEndY = e.changedTouches[0].screenY;
        handleSwipe();
    });
    
    function handleSwipe() {
        if (touchStartY - touchEndY > 50) {
            // Swipe up (next slide)
            showSlide(currentSlide + 1);
        } else if (touchEndY - touchStartY > 50) {
            // Swipe down (previous slide)
            showSlide(currentSlide - 1);
        }
        resetTimer();
    }
    
    
    // Initialize first slide
    showSlide(0);



    function handleToast() {
        // Si un paramètre de message est présent dans l'URL
        if (window.location.search.includes('message_sent=true') ||
            window.location.search.includes('event_deleted=true') ||
            window.location.search.includes('message_error=true') || 
            window.location.search.includes('event_added=true') ||
            window.location.search.includes('event_deleted=true') ||
            window.location.search.includes('event_updated=true') ||
            window.location.search.includes('project_created=true') ||
            window.location.search.includes('project_deleted=true') ||
            window.location.search.includes('project_updated=true') ||            
            window.location.search.includes('certif_created=true') ||
            window.location.search.includes('certif_deleted=true') ||
            window.location.search.includes('certif_updated=true')
        ) {
            
            // Nettoyer l'URL après un court délai (pour laisser le temps au toast de s'afficher)
            setTimeout(function() {
                // Conserver uniquement la partie hash de l'URL actuelle
                const newUrl = window.location.pathname + window.location.hash;
                
                // Remplacer l'URL actuelle sans recharger la page
                window.history.replaceState({}, document.title, newUrl);
            }, 1); // Un court délai pour s'assurer que le toast s'affiche d'abord
        }
    }
    
    // Exécuter au chargement de la page
    handleToast();

    const observer = new IntersectionObserver(entries => {
        const visibleEntries = entries.filter(entry => entry.isIntersecting);
        
        visibleEntries.forEach((entry, index) => {
            setTimeout(() => {
                entry.target.classList.add("show");
            }, index * 150);
        });
        
        entries.filter(entry => !entry.isIntersecting).forEach(entry => {
            entry.target.classList.remove("show");
        });
    }, {
        threshold: 0,
    });

    const standardCards = document.querySelectorAll(".card:not(.timeline-card)");
    standardCards.forEach(card => {
        observer.observe(card);
    });

    const timelineCards = document.querySelectorAll(".timeline-card");
    timelineCards.forEach(card => {
        observer.observe(card);
    });
    
    const projectCards = document.querySelectorAll(".project-card");
    projectCards.forEach(card => {
        observer.observe(card);
    });
});

