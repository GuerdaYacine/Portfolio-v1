<?php
global $pdo;
global $currentUser;
$certificationDB = require_once './database/models/CertificationDB.php';

if (!$currentUser && (isset($_GET['delete_certif']) || isset($_GET['certif_id']) || isset($_GET['addCertification']))) {
    header('Location: /');
    exit;
}

$certifications = $certificationDB->getAllCertifications();

$certifErrors = [
    'title' => '',
    'description' => '',
    'date' => '',
    'image' => '',
    'link' => '',
];

$certifId = null;
$certifTitle = '';
$certifDescription = '';
$certifDate = '';
$certifLink = '';
$certifImage = '';


$deleteCertifId = filter_input(INPUT_GET, 'delete_certif', FILTER_SANITIZE_NUMBER_INT);
if ($deleteCertifId && ctype_digit($deleteCertifId)) {
    $certifId = (int) $deleteCertifId;
    $certifToDelete = $certificationDB->getCertificationById($certifId);

    if ($certifToDelete) {
        $imagePath = 'assets/images-certifications/' . basename($certifToDelete['image']);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $certificationDB->deleteCertification($certifId);
        header('Location: /?certif_deleted=true#certifications');
        exit;
    }
}

$editCertifId = filter_input(INPUT_GET, 'certif_id', FILTER_SANITIZE_NUMBER_INT);
if ($editCertifId && ctype_digit($editCertifId)) {
    $certifId = (int) $editCertifId;
    $certification = $certificationDB->getCertificationById($certifId);

    if ($certification) {
        $certifTitle = $certification['title'];
        $certifDescription = $certification['description'];
        $certifDate = $certification['date_learned'];
        $certifLink = $certification['link'];
        $certifImage = $certification['image'];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addCertification'])) {
    $_POST = filter_input_array(INPUT_POST, [
        'certif-title' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'certif-description' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'certif-date' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'certif-link' => FILTER_SANITIZE_URL,
    ]);

    $certifTitle = $_POST['certif-title'] ?? '';
    $certifDescription = $_POST['certif-description'] ?? '';
    $certifDate = $_POST['certif-date'] ?? '';
    $certifLink = $_POST['certif-link'] ?? '';

    $certifErrors = $certificationDB->validateCertificationData($certifTitle, $certifDescription, $certifDate, $certifLink);

    $imageFileName = $certifImage;

    if (!empty($_FILES['certif-image']['name']) || ($certifId === null && empty($certifImage))) {
        $imageResult = $certificationDB->saveImage($_FILES['certif-image'], $certifImage);
        $certifErrors['image'] = $imageResult['error'];
        $imageFileName = $imageResult['filename'];
    }

    if (empty(array_filter($certifErrors, fn($e) => $e !== ''))) {
        if ($certifId) {
            $certificationDB->updateCertification($certifId, $certifTitle, $certifDescription, $certifDate, $imageFileName, $certifLink);
            header('Location: /?certif_updated=true#certifications');
        } else {
            $certificationDB->createCertification($certifTitle, $certifDescription, $certifDate, $imageFileName, $certifLink);
            header('Location: /?certif_created=true#certifications');
        }
        exit;
    }
}
?>
<?php if ($currentUser) : ?>
    <?php if (isset($_GET['certif_deleted']) && $_GET['certif_deleted'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>La certification à été supprimée avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['certif_updated']) && $_GET['certif_updated'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>La certification à été modifiée avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['certif_created']) && $_GET['certif_created'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>La certification à été crée avec succès !</span>
        </div>
    <?php endif; ?>
<?php endif; ?>

<section class=" flex flex-col items-center justify-center py-[100px] gap-[50px]" id="certifications">
    <h4 class="card text-[#eff0f3] text-[4rem] inter">My certifications</h4>

    <div class="slider-container card p-[25px] bg-[#1d1f25] w-[90%] md:w-[80%] rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] <? $certifications  ? '' : 'hidden' ?> ">
        <div class="slider-viewport w-full overflow-hidden">
            <div class="slider-wrapper flex flex-col w-full transition-transform duration-500 ease-in-out">
                <?php foreach ($certifications as $certification) : ?>
                    <div class="slide w-full flex-none flex flex-col md:flex-row items-center justify-around gap-[50px]">
                        <div class="w-full md:w-[45%] shadow-[0_0_1rem_rgba(0,0,0,0.2)]">
                            <img src="assets/images-certifications/<?= $certification['image'] ?>" loading="lazy" class="rounded-[15px] hover:scale-[1.01] cursor-pointer transition-all duration-300 ease-in-out w-full" alt="Image de certifications">
                        </div>
                        <article class="flex flex-col items-center justify-center gap-[20px] w-full md:w-[45%]">
                            <p class="text-[20px] font-light leading-[25px] font-light text-[#eff0f3] inter">
                                <?php
                                $date_obj = new DateTime($certification['date_learned']);
                                echo $date_obj->format('F j, Y');
                                ?>
                            </p>
                            <h5 class="text-center font-medium text-[32px] tracking-[1px] leading-[30px] text-[#eff0f3] inter"><?= $certification['title']; ?></h5>
                            <p class="text-justify text-last-center text-[20px] leading-[25px] font-light text-[#eff0f3] inter text-center "><?= $certification['description']; ?></p>
                            <a href="<?= $certification['link']; ?>" target="_blank" class="mt-[30px] px-[30px] py-[10px] rounded-full cursor-pointer gap-[10px] bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] ">See more</a>
                            <?php if ($currentUser) : ?>
                                <div class="flex items-center gap-[10px] mt-[20px]">
                                    <a href="?certif_id=<?= $certification['id'] ?>#certifications" class="upload-image-btn flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                                        <img src="assets/icons/editer.png" class="h-[18px]" alt="Update">Update
                                    </a>
                                    <a href="?delete_certif=<?= $certification['id'] ?>" class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                                        <img src="assets/icons/supprimer.png" class="h-[18px]" alt="Delete">Delete
                                    </a>
                                </div>
                            <?php endif; ?>

                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card navigation flex justify-center items-center w-full gap-[10px]">
        <?php for ($i = 0; $i < count($certifications); $i++) : ?>
            <span class="slider-dot cursor-pointer h-[10px] w-[10px] rounded-full transition-all duration-300 ease-in-out <?php echo $i === 0 ? 'bg-[#ccc]' : 'bg-[#555]'; ?>" data-index="<?php echo $i; ?>"></span>
        <?php endfor; ?>
    </div>
    <?php if ($currentUser) : ?>
        <button id="addCertificationBtn" class="card mt-[25px] flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
            <img src="assets/icons/ajouter.png" class="h-[18px]" alt="Add">Add a certification
        </button>

        <div id="certificationFormContainer" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 <?= (array_filter($certifErrors) || isset($_GET['certif_id'])) ? '' : 'hidden' ?>">
            <div class="no-scroll relative bg-[#1d1f25] rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] overflow-auto">
                <button id="closeCertificationModal" class="absolute top-4 right-4 text-[#eff0f3] text-[24px]">
                    &times;
                </button>

                <form action="" method="POST" enctype="multipart/form-data" class=" flex flex-col justify-center gap-4 p-[30px] w-full" id="certification-form">
                    <h3 class="text-[#eff0f3] text-[2.5rem] inter">
                        <?= $certifId ? 'Update Certification' : 'Add New Certification' ?>
                    </h3>

                    <div class="flex flex-col">
                        <label for="certif-title" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Name</label>
                        <input type="text" name="certif-title" id="certif-title" placeholder="Certification title"
                            value="<?= $certifTitle ?>"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($certifErrors['title']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($certifErrors['title'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $certifErrors['title'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="certif-description" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Description</label>
                        <textarea name="certif-description" id="certif-description" placeholder="Description of the certification"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none min-h-[100px] <?= !empty($certifErrors['description']) ? 'border-2 border-red-500' : '' ?>"><?= $certifDescription ?></textarea>
                        <?php if (!empty($certifErrors['description'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $certifErrors['description'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="certif-date" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Date</label>
                        <input type="date" name="certif-date" id="certif-date"
                            value="<?= $certifDate ? date('Y-m-d', strtotime($certifDate)) : '' ?>"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($certifErrors['date']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($certifErrors['date'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $certifErrors['date'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="certif-link" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Link</label>
                        <input type="url" name="certif-link" id="certif-link" placeholder="URL to the certification"
                            value="<?= $certifLink ?>"
                            class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($certifErrors['link']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($certifErrors['link'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $certifErrors['link'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="flex flex-col">
                        <label for="certif-image" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Image</label>
                        <input type="file" name="certif-image" id="certif-image" class="text-[#eff0f3] <?= !empty($certifErrors['image']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if ($certifId): ?>
                            <p class="text-[#eff0f3] text-[14px] mt-2">Leave empty to keep the current image</p>
                        <?php endif; ?>
                        <?php if (!empty($certifErrors['image'])): ?>
                            <span class="text-red-500 text-[14px] mt-1 "><?= $certifErrors['image'] ?></span>
                        <?php endif; ?>
                    </div>

                    <?php if ($certifId && !empty($certifImage)): ?>
                        <div class="mt-2">
                            <p class="text-[#eff0f3] text-[16px]">Current image:</p>
                            <img src="assets/images-certifications/<?= $certifImage ?>"
                                alt="Current certification image" class="max-h-[150px] mt-2">
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-center items-center gap-4 mt-[20px]">
                        <button type="submit" name="addCertification" class="px-[30px] py-[10px] rounded-full cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">
                            <?= $certifId ? 'Update' : 'Submit' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="deleteCertificationModal" class="h-screen fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 hidden">
            <div class="relative bg-[#1d1f25] flex justify-center items-center rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] min-h-[30vh] overflow-auto p-6">
                <div class="flex flex-col gap-6">
                    <h2 class="text-[3.5rem] text-[#eff0f3] font-bold text-center">Confirmation de suppression</h2>
                    <p class="text-[2rem] text-[#eff0f3] text-center">Êtes-vous sûr de vouloir supprimer la certification "<span id="certification-title-to-delete"></span>" ?</p>
                    <div class="flex justify-evenly mt-4">
                        <button id="cancel-delete-certification" class="flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                            Annuler
                        </button>
                        <a id="confirm-delete-certification-link" href="#" class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                            Confirmer
                        </a>
                    </div>
                </div>
                <button id="delete-certification-modal-close" class="absolute top-4 right-4 text-[#eff0f3]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>
</section>



<script>
    const CERTIFICATIONS_COUNT = <?php echo count($certifications); ?>;

    const certificationFormContainer = document.getElementById('certificationFormContainer');
    const addCertificationBtn = document.getElementById('addCertificationBtn');
    const closeCertificationModal = document.getElementById('closeCertificationModal');
    const formContent = certificationFormContainer ? certificationFormContainer.querySelector('.relative') : null;

    if (formContent && certificationFormContainer && !certificationFormContainer.classList.contains('hidden')) {
        formContent.style.opacity = '1';
        document.body.style.overflow = 'hidden';
    } else if (formContent) {
        formContent.style.opacity = '0';
    }

    if (formContent) {
        formContent.style.transition = 'opacity 0.3s ease-in-out';
    }

    if (certificationFormContainer && !certificationFormContainer.classList.contains('hidden')) {
        const firstErrorField = certificationFormContainer.querySelector('.border-red-500');
        if (firstErrorField) {
            firstErrorField.focus();
        } else {
            const firstInput = document.querySelector('#certif-title');
            if (firstInput) firstInput.focus();
        }
    }

    function openCertificationForm() {
        if (certificationFormContainer) {
            certificationFormContainer.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                if (formContent) {
                    formContent.style.opacity = '1';
                }
            }, 10);
        }
    }

    function closeCertificationForm() {
        if (formContent) {
            formContent.style.opacity = '0';

            setTimeout(() => {
                if (certificationFormContainer) {
                    certificationFormContainer.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }, 300);
        } else if (certificationFormContainer) {
            certificationFormContainer.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    if (addCertificationBtn) {
        addCertificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openCertificationForm();
        });
    }

    document.querySelectorAll('a[href*="certif_id="]').forEach(editLink => {
        editLink.addEventListener('click', function(e) {
            e.preventDefault();
            const editUrl = this.getAttribute('href');

            window.location.href = editUrl;
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('certif_id') && !document.querySelector('#certification-form .border-red-500')) {
        openCertificationForm();
    }

    if (closeCertificationModal) {
        closeCertificationModal.addEventListener('click', function(e) {
            e.preventDefault();
            closeCertificationForm();

            if (urlParams.has('certif_id')) {
                window.history.pushState({}, '', window.location.pathname + '#certifications');
            }
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === certificationFormContainer) {
            closeCertificationForm();

            if (urlParams.has('certif_id')) {
                window.history.pushState({}, '', window.location.pathname + '#certifications');
            }
        }
    });

    const deleteCertificationModal = document.getElementById('deleteCertificationModal');
    const deleteCertificationModalClose = document.getElementById('delete-certification-modal-close');
    const cancelDeleteCertification = document.getElementById('cancel-delete-certification');
    const confirmDeleteCertificationLink = document.getElementById('confirm-delete-certification-link');
    const certificationTitleToDelete = document.getElementById('certification-title-to-delete');
    const certifModalContent = deleteCertificationModal ? deleteCertificationModal.querySelector('.relative') : null;

    if (certifModalContent) {
        certifModalContent.style.opacity = '0';
        certifModalContent.style.transition = 'opacity 0.3s ease-in-out';
    }

    function openDeleteCertificationModal(certifId, certifTitle, deleteUrl) {
        if (deleteCertificationModal) {
            certificationTitleToDelete.textContent = certifTitle;
            confirmDeleteCertificationLink.href = deleteUrl;

            deleteCertificationModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                if (certifModalContent) {
                    certifModalContent.style.opacity = '1';
                }
            }, 10);
        }
    }

    function closeDeleteCertificationModal() {
        if (certifModalContent) {
            certifModalContent.style.opacity = '0';

            setTimeout(() => {
                if (deleteCertificationModal) {
                    deleteCertificationModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }, 300);
        } else if (deleteCertificationModal) {
            deleteCertificationModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    document.querySelectorAll('a[href^="?delete_certif="]').forEach(deleteLink => {
        deleteLink.addEventListener('click', function(e) {
            e.preventDefault();
            const deleteUrl = this.getAttribute('href');

            const parentSlide = this.closest('.slide');
            const parentArticle = this.closest('article');

            let certifTitle;
            if (parentArticle) {
                certifTitle = parentArticle.querySelector('h5')?.textContent;
            } else if (parentSlide) {
                certifTitle = parentSlide.querySelector('h5')?.textContent;
            }

            if (!certifTitle) {
                certifTitle = 'cette certification';
            }

            openDeleteCertificationModal(deleteUrl.split('=')[1], certifTitle, deleteUrl);
        });
    });

    if (deleteCertificationModalClose) {
        deleteCertificationModalClose.addEventListener('click', closeDeleteCertificationModal);
    }

    if (cancelDeleteCertification) {
        cancelDeleteCertification.addEventListener('click', function(e) {
            e.preventDefault();
            closeDeleteCertificationModal();
        });
    }

    window.addEventListener('click', function(event) {
        if (event.target === deleteCertificationModal) {
            closeDeleteCertificationModal();
        }
    });
</script>