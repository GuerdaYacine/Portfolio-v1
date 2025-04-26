<?php

global $pdo;
global $currentUser;
$timelineDB = require_once './database/models/TimelineDB.php';


if (!$currentUser && (isset($_GET['event_id']) || isset($_GET['delete_event']) || isset($_GET['addEvent']))) {
    header('Location: /');
    exit;
}

$events = $timelineDB->getAllEvents();

$timelineErrors = [
    'title' => '',
    'description' => '',
    'date' => '',
    'image' => '',
];

$eventId = null;
$timelineTitle = '';
$timelineDescription = '';
$timelineDate = '';
$currentImage = '';

if (isset($_GET['delete_event']) && ctype_digit($_GET['delete_event'])) {
    $eventId = (int) $_GET['delete_event'];
    $eventToDelete = $timelineDB->getEventById($eventId);

    if ($eventToDelete) {
        $imagePath = 'assets/images-timeline/' . basename($eventToDelete['image']);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $timelineDB->deleteEvent($eventId);
        header('Location: /?event_deleted=true#timeline');
        exit;
    }
}

if (isset($_GET['event_id']) && ctype_digit($_GET['event_id'])) {
    $eventId = (int) $_GET['event_id'];
    $event = $timelineDB->getEventById($eventId);

    if ($event) {
        $timelineTitle = $event['title'];
        $timelineDescription = $event['description'];
        $date_obj = new DateTime($event['date_learned']);
        $timelineDate = $date_obj->format('Y-m');
        $currentImage = $event['image'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addEvent'])) {
    $_POST = filter_input_array(INPUT_POST, [
        'event-title' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'event-description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'event-date' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
    ]);

    $timelineTitle = $_POST['event-title'] ?? '';
    $timelineDescription = $_POST['event-description'] ?? '';
    $timelineDate = $_POST['event-date'] ?? '';

    $timelineErrors = $timelineDB->validateEventData($timelineTitle, $timelineDescription, $timelineDate);

    $imageFileName = $currentImage;

    if (!empty($_FILES['event-image']['name']) || ($eventId === null && empty($currentImage))) {
        $imageResult = $timelineDB->saveImage($_FILES['event-image'], $currentImage);
        $timelineErrors['image'] = $imageResult['error'];
        $imageFileName = $imageResult['filename'];
    }

    if (empty(array_filter($timelineErrors, fn($e) => $e !== ''))) {
        $formattedDate = $timelineDate . '-01';

        if ($eventId) {
            $timelineDB->updateEvent($eventId, $timelineTitle, $timelineDescription, $formattedDate, $imageFileName);
            header('Location: /?event_updated=true#timeline');
        } else {
            $timelineDB->createEvent($timelineTitle, $timelineDescription, $formattedDate, $imageFileName);
            header('Location: /?event_added=true#timeline');
        }
        exit;
    }
}

?>
<?php if ($currentUser) : ?>
    <?php if (isset($_GET['event_deleted']) && $_GET['event_deleted'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>L'évènement à été supprimé avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['event_updated']) && $_GET['event_updated'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>L'évènement à été modifié avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['event_added']) && $_GET['event_added'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>L'évènement à été crée avec succès !</span>
        </div>
    <?php endif; ?>
<?php endif; ?>


<section class="flex flex-col items-center justify-center py-[100px]" id="timeline">
    <h2 class="card text-[#eff0f3] text-[4rem] inter">My Apprenticeship</h2>
    <div class="w-full flex items-center justify-center mt-[25px] md:mt-[50px]">
        <div class="w-[90%] md:w-[80%] max-w-[800px] my-0 mx-auto relative md:before:absolute md:before:h-[100%] md:before:w-[2px] md:before:bg-[gray] md:before:left-[50%] md:before:translate-x-[-50%]">
            <ul class="list-none">
                <?php $index = 0;
                ?>
                <?php foreach ($events as $event) : ?>
                    <li class="card timeline-card <?= $index % 2 == 0 ? 'timeline-left' : 'timeline-right' ?> shadow-[0_0_1rem_rgba(0,0,0,0.2)] p-[20px] bg-[#1d1f25] text-[#eff0f3] rounded-[10px] md:odd:rounded-[20px_0px_20px_20px] md:even:rounded-[0px_20px_20px_20px] mb-[20px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] relative md:w-[50%] md:mb-[50px] md:odd:float-left md:odd:clear-right md:even:float-right md:even:clear-left md:after:absolute md:after:h-[20px] md:after:w-[20px] md:after:bg-[#145C9E] md:after:rounded-full md:after:top-[-10px] md:odd:after:right-[-30px] md:odd:after:translate-x-1/2 md:even:after:left-[-40px] md:odd:after:translate-x-1/2">
                        <div>
                            <p class="text-[15px] mb-[10px] font-light tracking-[2px] md:absolute md:top-[-30px] ">
                                <?php
                                $date_obj = new DateTime($event['date_learned']);
                                echo $date_obj->format('F Y');
                                ?>
                            </p>
                            <h3 class="font-medium text-[26px] leading-[30px] inter"><?= $event['title'] ?></h3>
                            <p class="text-[20px] leading-[25px] font-light "><?= $event['description'] ?></p>
                            <?php if ($currentUser) : ?>
                                <div class="flex items-center gap-[10px] mt-[20px]">
                                    <div class="flex items-center gap-[10px]">
                                        <a href="?event_id=<?= $event['id'] ?>#timeline" class="flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                                            <img src="assets/icons/editer.png" class="h-[18px]" alt="Update">Update
                                        </a>
                                        <a href="?delete_event=<?= $event['id'] ?>" class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                                            <img src="assets/icons/supprimer.png" class="h-[18px]" alt="Delete">Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </li>
                    <?php $index++;
                    ?>
                <?php endforeach; ?>
            </ul>
            <div class="clear-both"></div>
        </div>
    </div>

    <?php if ($currentUser) : ?>
        <button id="addEventBtn" class="card mt-[25px] flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
            <img src="assets/icons/ajouter.png" class="h-[18px]" alt="Add">Add an event
        </button>

        <div id="eventFormContainer" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 <?= (array_filter($timelineErrors) || isset($_GET['event_id'])) ? '' : 'hidden' ?>">
            <div class="relative bg-[#1d1f25] rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] overflow-auto">
                <button id="closeEventModal" class="absolute top-4 right-4 text-[#eff0f3] text-[24px]">
                    &times;
                </button>

                <form action="" method="POST" enctype="multipart/form-data" class="flex flex-col justify-center gap-4 p-[30px] w-full" id="timeline-form">
                    <h3 class="text-[#eff0f3] text-[2.5rem] inter">
                        <?= $eventId ? 'Modifier un événement' : 'Ajouter un événement' ?>
                    </h3>

                    <div class="flex flex-col">
                        <label for="event-title" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Nom</label>
                        <input type="text" name="event-title" id="event-title" placeholder="Titre de l'événement"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($timelineErrors['title']) ? 'border-2 border-red-500' : '' ?>"
                            value="<?= $timelineTitle ?? '' ?>">
                        <?php if (!empty($timelineErrors['title'])): ?>
                            <span class="text-red-500 text-[14px] mt-1"><?= $timelineErrors['title'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="event-description" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Description</label>
                        <textarea name="event-description" id="event-description" placeholder="Description de l'événement"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none min-h-[100px] <?= !empty($timelineErrors['description']) ? 'border-2 border-red-500' : '' ?>"><?= $timelineDescription ?? '' ?></textarea>
                        <?php if (!empty($timelineErrors['description'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $timelineErrors['description'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="event-date" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Date (Mois/Année)</label>
                        <input type="month" name="event-date" id="event-date"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($timelineErrors['date']) ? 'border-2 border-red-500' : '' ?>"
                            value="<?= $timelineDate ?? '' ?>">
                        <?php if (!empty($timelineErrors['date'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $timelineErrors['date'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="event-image" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Image</label>
                        <input type="file" name="event-image" id="event-image"
                            class="text-[#eff0f3] <?= !empty($timelineErrors['image']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if ($eventId): ?>
                            <p class="text-[#eff0f3] text-[14px] mt-2">Laissez vide pour conserver l'image actuelle</p>
                        <?php endif; ?>
                        <?php if (!empty($timelineErrors['image'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $timelineErrors['image'] ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($eventId && !empty($currentImage)): ?>
                        <div class="mt-2">
                            <p class="text-[#eff0f3] text-[16px]">Image actuelle:</p>
                            <img src="assets/images-timeline/<?= $currentImage ?>"
                                alt="Image actuelle de l'événement" class="max-h-[150px] mt-2">
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-center items-center gap-4 mt-[20px]">
                        <button type="submit" name="addEvent" class="px-[30px] py-[10px] rounded-full cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">
                            <?= $eventId ? 'Mettre à jour' : 'Ajouter' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="deleteEventModal" class="h-screen fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 hidden">
            <div class="relative bg-[#1d1f25] flex justify-center items-center rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] min-h-[30vh] overflow-auto p-6">
                <div class="flex flex-col gap-6">
                    <h2 class="text-[3.5rem] text-[#eff0f3] font-bold text-center">Confirmation de suppression</h2>
                    <p class="text-[2rem] text-[#eff0f3] text-center">Êtes-vous sûr de vouloir supprimer l'événement "<span id="event-title-to-delete"></span>" ?</p>
                    <div class="flex justify-evenly mt-4">
                        <button id="cancel-delete" class="flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                            Annuler
                        </button>
                        <a id="confirm-delete-link" href="#" class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                            Confirmer
                        </a>
                    </div>
                </div>
                <button id="delete-modal-close" class="absolute top-4 right-4 text-[#eff0f3]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteEventModal = document.getElementById('deleteEventModal');
            const deleteModalClose = document.getElementById('delete-modal-close');
            const cancelDelete = document.getElementById('cancel-delete');
            const confirmDeleteLink = document.getElementById('confirm-delete-link');
            const eventTitleToDelete = document.getElementById('event-title-to-delete');
            const modalContent = deleteEventModal ? deleteEventModal.querySelector('.relative') : null;

            if (modalContent) {
                modalContent.style.opacity = '0';
                modalContent.style.transition = 'opacity 0.3s ease-in-out';
            }

            function openDeleteModal(eventId, eventTitle, deleteUrl) {
                if (deleteEventModal) {
                    eventTitleToDelete.textContent = eventTitle;
                    confirmDeleteLink.href = deleteUrl;

                    deleteEventModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    setTimeout(() => {
                        if (modalContent) {
                            modalContent.style.opacity = '1';
                        }
                    }, 10);
                }
            }

            function closeDeleteModal() {
                if (modalContent) {
                    modalContent.style.opacity = '0';

                    setTimeout(() => {
                        if (deleteEventModal) {
                            deleteEventModal.classList.add('hidden');
                            document.body.style.overflow = 'auto';
                        }
                    }, 300);
                } else if (deleteEventModal) {
                    deleteEventModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            document.querySelectorAll('a[href^="?delete_event="]').forEach(deleteLink => {
                deleteLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const deleteUrl = this.getAttribute('href');
                    const listItem = this.closest('li');
                    const eventTitle = listItem ? listItem.querySelector('h3').textContent : 'cet événement';

                    openDeleteModal(deleteUrl.split('=')[1], eventTitle, deleteUrl);
                });
            });

            if (deleteModalClose) {
                deleteModalClose.addEventListener('click', closeDeleteModal);
            }

            if (cancelDelete) {
                cancelDelete.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeDeleteModal();
                });
            }

            window.addEventListener('click', function(event) {
                if (event.target === deleteEventModal) {
                    closeDeleteModal();
                }
            });

            const eventFormContainer = document.getElementById('eventFormContainer');
            const addEventBtn = document.getElementById('addEventBtn');
            const closeEventModal = document.getElementById('closeEventModal');
            const formContent = eventFormContainer ? eventFormContainer.querySelector('.relative') : null;

            if (formContent && eventFormContainer && !eventFormContainer.classList.contains('hidden')) {
                formContent.style.opacity = '1';
                document.body.style.overflow = 'hidden';
            } else if (formContent) {
                formContent.style.opacity = '0';
            }

            if (formContent) {
                formContent.style.transition = 'opacity 0.3s ease-in-out';
            }

            if (eventFormContainer && !eventFormContainer.classList.contains('hidden')) {
                const firstErrorField = document.querySelector('.border-red-500');
                if (firstErrorField) {
                    firstErrorField.focus();
                } else {
                    const firstInput = document.querySelector('#event-title');
                    if (firstInput) firstInput.focus();
                }
            }


            function openEventForm() {
                if (eventFormContainer) {
                    eventFormContainer.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    setTimeout(() => {
                        if (formContent) {
                            formContent.style.opacity = '1';
                        }
                    }, 10);
                }
            }

            function closeEventForm() {
                if (formContent) {
                    formContent.style.opacity = '0';

                    setTimeout(() => {
                        if (eventFormContainer) {
                            eventFormContainer.classList.add('hidden');
                            document.body.style.overflow = 'auto';
                        }
                    }, 300);
                } else if (eventFormContainer) {
                    eventFormContainer.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            if (addEventBtn) {
                addEventBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openEventForm();
                });
            }

            document.querySelectorAll('a[href*="event_id="]').forEach(editLink => {
                editLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const editUrl = this.getAttribute('href');

                    window.location.href = editUrl;
                });
            });

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('event_id') && !document.querySelector('.border-red-500')) {
                openEventForm();
            }

            if (closeEventModal) {
                closeEventModal.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeEventForm();

                    if (urlParams.has('event_id')) {
                        window.history.pushState({}, '', window.location.pathname + '#timeline');
                    }
                });
            }

            window.addEventListener('click', function(event) {
                if (event.target === eventFormContainer) {
                    closeEventForm();

                    if (urlParams.has('event_id')) {
                        window.history.pushState({}, '', window.location.pathname + '#timeline');
                    }
                }
            });
        });
    </script>
</section>