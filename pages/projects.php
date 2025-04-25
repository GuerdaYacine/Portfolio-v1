<?php
global $pdo;
global $currentUser;
$projectDB = require_once './database/models/ProjectDB.php';

// Initialize variables
$errors = [
    'image' => '',
    'icon' => '',
    'title' => '',
    'date' => '',
    'description' => '',
    'context' => '',
    'technologies' => '',
    'link' => '',
    'category' => '',
    'general' => ''
];

$projectId = false;
$projectTitle = '';
$projectDate = '';
$projectDescription = '';
$projectContext = '';
$projectLink = '';
$projectCategory = '';
$projectTechnologies = [];
$projectImage = '';
$projectIcon = '';

// Fetch all categories and technologies
$categories = $projectDB->getAllCategories();
$technologies = $projectDB->getAllTechnologies();

// Handle project deletion
if (isset($_GET['delete_project']) && ctype_digit($_GET['delete_project']) && $currentUser) {
    $projectId = (int) $_GET['delete_project'];

    if ($projectDB->deleteProject($projectId)) {
        header("Location: /?project_deleted=true#projects");
        exit;
    }
}

// Handle project editing - fetch project details
if (isset($_GET['project_id']) && ctype_digit($_GET['project_id']) && $currentUser) {
    $projectId = (int) $_GET['project_id'];
    $project = $projectDB->getProjectById($projectId);

    if ($project) {
        $projectTitle = $project['title'];
        $date_obj = new DateTime($project['date_of_realisation']);
        $projectDate = $date_obj->format('Y-m');
        $projectDescription = $project['description'];
        $projectContext = $project['context'];
        $projectLink = $project['link'];
        $projectCategory = $project['category_id'];
        $projectImage = $project['image'];
        $projectIcon = $project['icon'];

        // Get associated technologies IDs
        $projectTechnologies = [];
        foreach ($project['technologies'] as $tech) {
            $projectTechnologies[] = $tech['id'];
        }
    }
}

// Handle form submission for create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addProject']) && $currentUser) {
    // Sanitize input data
    $projectTitle = filter_input(INPUT_POST, 'project-title', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $projectDate = filter_input(INPUT_POST, 'project-date', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $projectDescription = filter_input(INPUT_POST, 'project-description', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $projectContext = filter_input(INPUT_POST, 'project-context', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $projectLink = filter_input(INPUT_POST, 'project-link', FILTER_SANITIZE_URL) ?? '';
    $projectCategory = filter_input(INPUT_POST, 'project-category', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
    $projectTechnologies = isset($_POST['technologies']) ? (array)$_POST['technologies'] : [];

    // Sanitize array values
    foreach ($projectTechnologies as $key => $value) {
        $projectTechnologies[$key] = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    // Prepare data for validation
    $projectData = [
        'title' => $projectTitle,
        'date' => $projectDate,
        'description' => $projectDescription,
        'context' => $projectContext,
        'link' => $projectLink,
        'category_id' => $projectCategory,
        'technologies' => $projectTechnologies,
        'current_image' => $projectImage,
        'current_icon' => $projectIcon
    ];

    // Validate the data
    $errors = $projectDB->validateProjectData($projectData, [
        'image' => $_FILES['project-image'] ?? [],
        'icon' => $_FILES['project-icon'] ?? []
    ]);

    // Process images if no validation errors
    $imageFileName = $projectImage;
    $iconFileName = $projectIcon;

    if (!array_filter($errors)) {
        // Process image
        if (!empty($_FILES['project-image']['name']) && $_FILES['project-image']['error'] === 0) {
            $imageResult = $projectDB->saveImage($_FILES['project-image'], 'images', $projectImage);
            if (!empty($imageResult['error'])) {
                $errors['image'] = $imageResult['error'];
            } else {
                $imageFileName = $imageResult['filename'];
            }
        }

        // Process icon
        if (!empty($_FILES['project-icon']['name']) && $_FILES['project-icon']['error'] === 0) {
            $iconResult = $projectDB->saveImage($_FILES['project-icon'], 'icons', $projectIcon);
            if (!empty($iconResult['error'])) {
                $errors['icon'] = $iconResult['error'];
            } else {
                $iconFileName = $iconResult['filename'];
            }
        }
    }

    // Process if no errors
    if (!array_filter($errors)) {
        $projectData = [
            'title' => $projectTitle,
            'date' => $projectDate . '-01', // Ensure date has day component
            'description' => $projectDescription,
            'context' => $projectContext,
            'image' => $imageFileName,
            'icon' => $iconFileName,
            'link' => $projectLink,
            'category_id' => $projectCategory
        ];

        if ($projectId) {
            // Update existing project
            if ($projectDB->updateProject($projectId, $projectData, $projectTechnologies)) {
                header("Location: /?project_updated=true#projects");
                exit;
            }
        } else {
            // Create new project
            $newProjectId = $projectDB->createProject($projectData, $projectTechnologies);
            if ($newProjectId) {
                header("Location: /?project_created=true#projects");
                exit;
            }
        }
    }
}

// Fetch all projects
$projects = $projectDB->getAllProjects();
?>
<?php if ($currentUser) : ?>
    <?php if (isset($_GET['project_deleted']) && $_GET['project_deleted'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>Le projet à été supprimé avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['project_updated']) && $_GET['project_updated'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>Le projet à été modifié avec succès !</span>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['project_created']) && $_GET['project_created'] === 'true') : ?>
        <div id="toast-success" class="fixed bottom-5 right-5 bg-green-500 text-white p-[15px] text-[18px] rounded-lg shadow-lg z-50 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            <span>Le projet à été créé avec succès !</span>
        </div>
    <?php endif; ?>
<?php endif; ?>


<section class="flex flex-col items-center justify-center py-[100px] gap-[50px]" id="projects">
    <h3 class="card text-[#eff0f3] text-[4rem] inter">My projects</h3>

    <!-- Categories filter buttons -->
    <div class="flex justify-center flex-wrap w-full">
        <ul class="card flex flex-wrap justify-evenly gap-[20px] items-center w-[90%] md:w-[80%]">
            <li>
                <button class="category-filter px-[20px] py-[10px] rounded-full cursor-pointer flex justify-center items-center gap-[10px] bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]" data-category="all">
                    All Projects
                </button>
            </li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <button class="category-filter px-[20px] py-[10px] rounded-full cursor-pointer flex justify-center items-center gap-[10px] bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]" data-category="<?= htmlspecialchars($category['id']) ?>">
                        <?php if (!empty($category['icon'])): ?>
                            <img src="<?= htmlspecialchars($category['icon']) ?>" alt="Icon" class="h-[24px]">
                        <?php endif; ?>
                        <?= htmlspecialchars($category['name']) ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Projects display -->
    <div class="flex flex-wrap items-center justify-evenly gap-[60px] w-[90%] md:w-[80%]">
        <?php foreach ($projects as $project) : ?>
            <article class="card project-card p-[30px] bg-[#1d1f25] rounded-[15px] flex flex-col items-center gap-[25px] flexeur shadow-[0_0_1rem_rgba(0,0,0,0.2)]"
                data-project-id="<?= $project['id'] ?>"
                data-category="<?= $project['category_id'] ?>">

                <!-- Project Image and Icon -->
                <span class="flex justify-center mb-[45px] items-center relative self-center mx-auto min-w-[105px] max-w-[15vh] min-h-[80px] max-h-[25vw] aspect-square rounded-full bg-cover bg-center"
                    style="background-image: url('<?= $project["image"] ?>');">
                    <img loading="lazy"
                        src="<?= $project["icon"] ?>"
                        alt="<?= $project["title"] ?> Icon"
                        class="hover:scale-[1.1] hover:drop-shadow-[5px_5px_10px_rgba(0,0,0,0.604)] transition-all duration-200 ease-in-out filter brightness-100 drop-shadow-[5px_5px_5px_rgba(34,34,34,0.436)] transition-all duration-200 ease-in-out relative w-[7vh] max-w-[200px] opacity-100 min-w-[1px] right-[50px] top-[50px] z-[2]">
                </span>

                <!-- Project Title -->
                <h4 class="text-center font-bold text-[4rem] leading-[30px] text-[#eff0f3] inter"><?= $project["title"] ?></h4>

                <!-- Project Date -->
                <p class="text-[20px] leading-[25px] font-light text-[#eff0f3] inter">
                    <?php
                    $date_obj = new DateTime($project['date_of_realisation']);
                    echo $date_obj->format('F Y');
                    ?>
                </p>

                <!-- Project Context -->
                <p class="h-[100px] overflow-auto text-last-center text-[20px] leading-[25px] max-w-[500px] font-light text-[#eff0f3] inter text-center">
                    <?= $project["context"] ?>
                </p>

                <!-- View Details Button -->
                <button class="open-modal-btn px-[30px] py-[10px] rounded-[10px] cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]"
                    data-project-id="<?= $project['id'] ?>">
                    See more
                </button>

                <!-- Admin options -->
                <?php if ($currentUser) : ?>
                    <div class="flex items-center gap-[10px]">
                        <a href="?project_id=<?= $project['id'] ?>#projects"
                            class="flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                            <img src="assets/icons/editer.png" class="h-[18px]" alt="Update">Update
                        </a>
                        <a href="?delete_project=<?= $project['id'] ?>"
                            class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                            <img src="assets/icons/supprimer.png" class="h-[18px]" alt="Delete">Delete
                        </a>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Project Details Modal -->
    <div id="projectModal" class="project-modal-container">
        <div class="project-modal-content no-scroll">
            <button id="closeProjectModal" class="absolute z-3 top-[10px] right-[10px] text-[#eff0f3] text-[2rem] w-[30px] h-[30px] flex items-center justify-center rounded-full hover:bg-gray-500 hover:p-[8px] transition-all duration-200 ease-in-out">
                <i class="fas fa-times"></i>
            </button>
            <div class="w-fit flex items-center">
                <img id="projectModalImage" src="" class="w-[calc(100%/1)] md:max-w-[600px]" alt="">
            </div>
            <div class="lg:w-[50%] flex flex-col justify-center items-center gap-[20px]">
                <h4 id="projectModalTitle" class="text-center font-bold text-[4rem] text-[#eff0f3]"></h4>
                <p id="projectModalDate" class="text-[20px] font-light text-[#eff0f3]"></p>
                <p id="projectModalDescription" class="text-[20px] text-justify font-light text-[#eff0f3]"></p>
                <p class="text-[26px] font-medium font-light text-[#eff0f3]">Made using :</p>
                <div id="projectModalTechnologies" class="flex flex-wrap justify-center items-center gap-[30px] overflow-x-auto">
                    <!-- Technologies will be populated by JavaScript -->
                </div>
                <a id="projectModalLink" href="" target="_blank" class="px-[30px] py-[10px] rounded-[10px] cursor-pointer gap-[10px] bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">Source code</a>
            </div>
        </div>
    </div>
    <!-- Add Project Button (admin only) -->
    <?php if ($currentUser) : ?>
        <button id="addProjectBtn" class="card mt-[25px] flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[5px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
            <img src="assets/icons/ajouter.png" class="h-[18px]" alt="Add">Add a project
        </button>

        <!-- Project Form (for add/edit) -->
        <div id="projectFormContainer" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 <?= (array_filter($errors) || isset($_GET['project_id'])) ? '' : 'hidden' ?>">
            <div class="no-scroll relative bg-[#1d1f25] rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] overflow-auto">
                <button id="closeProjectFormModal" class="absolute top-4 right-4 text-[#eff0f3] text-[24px]">
                    &times;
                </button>
                <form action="" method="post" enctype="multipart/form-data" id="project-form" class="flex flex-col justify-center gap-4 p-[30px] w-full">
                    <h3 class="text-[#eff0f3] text-[2.5rem] inter">
                        <?= $projectId ? 'Modifier un projet' : 'Ajouter un projet' ?>
                    </h3>

                    <!-- Image field -->
                    <div class="flex flex-col">
                        <label for="project-image" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Image du projet</label>
                        <input type="file" id="project-image" name="project-image" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['image']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['image'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['image'] ?></span>
                        <?php endif; ?>
                        <?php if ($projectId && !empty($projectImage)) : ?>
                            <p class="text-[#eff0f3] text-[14px] mt-2">Image actuelle:</p>
                            <img src="<?= $projectImage ?>" alt="<?= htmlspecialchars($project["title"]) ?> image" class="max-h-[100px] max-w-[100px] mt-2">
                        <?php endif; ?>
                    </div>

                    <!-- Icon field -->
                    <div class="flex flex-col">
                        <label for="project-icon" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Icone du projet</label>
                        <input type="file" name="project-icon" id="project-icon" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['icon']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['icon'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['icon'] ?></span>
                        <?php endif; ?>
                        <?php if ($projectId && !empty($projectIcon)) : ?>
                            <p class="text-[#eff0f3] text-[14px] mt-2">Icône actuelle:</p>
                            <img src="<?= $projectIcon ?>" alt="<?= $project["title"] ?> Icon" class="max-h-[100px] max-w-[100px] mt-2">
                        <?php endif; ?>
                    </div>

                    <!-- Title field -->
                    <div class="flex flex-col">
                        <label for="project-title" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Titre du projet</label>
                        <input type="text" name="project-title" id="project-title" placeholder="Title" value="<?= $projectTitle ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['title']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['title'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['title'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Date field -->
                    <div class="flex flex-col">
                        <label for="project-date" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Date (Mois/Année)</label>
                        <input type="month" name="project-date" id="project-date" value="<?= $projectDate ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['date']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['date'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['date'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Context field -->
                    <div class="flex flex-col">
                        <label for="project-context" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Contexte du projet</label>
                        <input type="text" name="project-context" id="project-context" placeholder="Context" value="<?= $projectContext ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['context']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['context'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['context'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Description field -->
                    <div class="flex flex-col">
                        <label for="project-description" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Description du projet</label>
                        <textarea name="project-description" id="project-description" placeholder="Description" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['description']) ? 'border-2 border-red-500' : '' ?> min-h-[100px]"><?= $projectDescription ?></textarea>
                        <?php if (!empty($errors['description'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['description'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Technologies field -->
                    <div class="flex flex-col">
                        <fieldset class="<?= !empty($errors['technologies']) ? '' : 'mb-[20px]' ?>">
                            <legend class="inter text-[18px] text-[2rem] text-[#eff0f3]">Technologies utilisées :</legend>
                            <div class="grid grid-cols-2 gap-2 md:grid-cols-3 lg:grid-cols-4">
                                <?php foreach ($technologies as $technology) : ?>
                                    <div class="flex justify-start items-center">
                                        <label class="flex gap-[5px] inter text-[18px] text-[#eff0f3] transition-all duration-300 ease-in-out hover:text-[#145C9E] cursor-pointer">
                                            <input type="checkbox" name="technologies[]" class="cursor-pointer" value="<?= $technology['id'] ?>" <?= in_array($technology['id'], $projectTechnologies) ? 'checked' : '' ?>>
                                            <?= $technology['title'] ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </fieldset>
                        <?php if (!empty($errors['technologies'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['technologies'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Link field -->
                    <div class="flex flex-col">
                        <label for="project-link" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Lien du projet</label>
                        <input type="url" name="project-link" id="project-link" placeholder="Link" value="<?= $projectLink ?>" class="inter text-[18px] p-[8px] rounded-[5px] outline-none <?= !empty($errors['link']) ? 'border-2 border-red-500' : '' ?>">
                        <?php if (!empty($errors['link'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['link'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Category field -->
                    <div class="flex flex-col">
                        <label for="category" class="inter text-[18px] text-[2rem] text-[#eff0f3]">Categorie du projet</label>
                        <select name="project-category" id="category" class="h-[45px] inter text-[18px] p-[8px] rounded-[5px] outline-none">
                            <option value="">Sélectionnez une catégorie</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $projectCategory == $category['id'] ? 'selected' : '' ?>>
                                    <?= $category['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errors['category'])) : ?>
                            <span class="text-red-500 text-[16px] mt-1"><?= $errors['category'] ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Submit button -->
                    <div class="flex justify-center items-center mt-[20px]">
                        <button type="submit" name="addProject" id="addProject" class="px-[30px] py-[10px] rounded-full cursor-pointer bg-[#145C9E] text-[#eff0f3] text-[1.8rem] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F]">
                            <?= $projectId ? 'Mettre à jour' : 'Ajouter' ?>
                        </button>
                    </div>

                    <!-- Display general errors if any -->
                    <?php if (!empty($errors['general'])) : ?>
                        <div class="text-red-500 text-center mt-4"><?= $errors['general'] ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div id="deleteProjectModal" class="h-screen fixed inset-0 bg-black bg-opacity-50 z-50 flex justify-center items-center p-4 hidden">
            <div class="relative bg-[#1d1f25] flex justify-center items-center rounded-[15px] shadow-[0_0_1rem_rgba(0,0,0,0.2)] w-[90%] max-w-[600px] max-h-[90vh] min-h-[30vh] overflow-auto p-6">
                <div class="flex flex-col gap-6">
                    <h2 class="text-[3.5rem] text-[#eff0f3] font-bold text-center">Confirmation de suppression</h2>
                    <p class="text-[2rem] text-[#eff0f3] text-center">Êtes-vous sûr de vouloir supprimer le projet "<span id="project-title-to-delete"></span>" ?</p>
                    <div class="flex justify-evenly mt-4">
                        <button id="cancel-project-delete" class="flex justify-center items-center py-[10px] px-[20px] bg-[#145C9E] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#1E3A5F] gap-[5px]">
                            Annuler
                        </button>
                        <a id="confirm-delete-project-link" href="#" class="flex justify-center items-center py-[10px] px-[20px] bg-[#c62828] text-[#eff0f3] text-[20px] rounded-[10px] transition-all duration-300 ease-in-out hover:bg-[#a01717] gap-[5px]">
                            Confirmer
                        </a>
                    </div>
                </div>
                <button id="delete-project-modal-close" class="absolute top-4 right-4 text-[#eff0f3]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <script>
        // Wait for the DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Get project modal elements with specific names
            const projectModal = document.getElementById('projectModal');
            const closeProjectModalBtn = document.getElementById('closeProjectModal');

            // Get all "See more" buttons
            const openProjectModalBtns = document.querySelectorAll('.open-modal-btn');

            // Function to decode HTML entities
            function decodeHTMLEntities(text) {
                const textArea = document.createElement('textarea');
                textArea.innerHTML = text;
                return textArea.value;
            }

            // Create projects data array
            const projectsData = <?php
                                    // Convert PHP array to JSON
                                    echo json_encode(array_map(function ($project) {
                                        // Format date
                                        $date_obj = new DateTime($project['date_of_realisation']);
                                        $formattedDate = $date_obj->format('F Y');

                                        return [
                                            'id' => $project['id'],
                                            'title' => $project['title'],
                                            'date' => $formattedDate,
                                            'description' => $project['description'],
                                            'context' => $project['context'],
                                            'image' => $project['image'],
                                            'link' => $project['link'],
                                            'technologies' => array_map(function ($tech) {
                                                return [
                                                    'id' => $tech['id'],
                                                    'title' => $tech['title'],
                                                    'image' => $tech['image']
                                                ];
                                            }, $project['technologies'])
                                        ];
                                    }, $projects));
                                    ?>;

            // Function to open project modal with animation
            function openProjectModal(projectId) {
                const project = projectsData.find(p => p.id == projectId);

                function formatTextWithLineBreaks(text) {
                    if (!text) return '';

                    // Étape 1: Décodez les entités HTML
                    const decodedText = decodeHTMLEntities(text);

                    // Étape 2: Remplacez les "\n" littéraux par des <br>
                    let formattedText = decodedText.replace(/\\n/g, '<br>');

                    // Étape 3: Remplacez aussi les vrais retours à la ligne par des <br>
                    formattedText = formattedText.replace(/\n/g, '<br>');

                    return formattedText;
                }
                if (project) {
                    // Set project information in the modal
                    document.getElementById('projectModalImage').src = project.image;
                    document.getElementById('projectModalTitle').textContent = decodeHTMLEntities(project.title);
                    document.getElementById('projectModalDate').textContent = project.date;

                    // Formater la description avec les sauts de ligne
                    document.getElementById('projectModalDescription').innerHTML = formatTextWithLineBreaks(project.description);

                    document.getElementById('projectModalLink').href = project.link;

                    // Handle technologies
                    const techContainer = document.getElementById('projectModalTechnologies');
                    techContainer.innerHTML = ''; // Clear existing content

                    if (project.technologies && project.technologies.length > 0) {
                        project.technologies.forEach(tech => {
                            const techDiv = document.createElement('div');
                            techDiv.className = 'flex flex-col justify-center items-center gap-[10px]';
                            techDiv.innerHTML = `
                    <img src="assets/images-timeline/${tech.image}" class="h-[60px] w-[60px]" alt="${decodeHTMLEntities(tech.title)}">
                    <p class="text-[20px] font-light text-[#eff0f3]">${decodeHTMLEntities(tech.title)}</p>
                `;
                            techContainer.appendChild(techDiv);
                        });
                    } else {
                        techContainer.innerHTML = '<p class="text-[20px] font-light text-[#eff0f3]">Aucune technologie associée</p>';
                    }

                    // Show the modal with animation
                    projectModal.classList.add('show');

                    // Prevent scrolling on the body
                    document.body.style.overflow = 'hidden';
                }
            }

            // Function to close project modal with animation
            function closeProjectModal() {
                projectModal.classList.remove('show');

                // Re-enable scrolling on the body
                document.body.style.overflow = '';
            }

            // Set up click events on each "See more" button
            openProjectModalBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const projectId = this.getAttribute('data-project-id');
                    openProjectModal(projectId);
                });
            });

            // Close modal when clicking the close button
            if (closeProjectModalBtn) {
                closeProjectModalBtn.addEventListener('click', function() {
                    closeProjectModal();
                });
            }

            // Close modal when clicking outside of the content
            projectModal.addEventListener('click', function(event) {
                if (event.target === projectModal) {
                    closeProjectModal();
                }
            });

            // Handle category filters
            const filterButtons = document.querySelectorAll('.category-filter');
            const projectCards = document.querySelectorAll('.project-card');

            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category');

                    // First, remove show class from ALL cards to reset animation state
                    projectCards.forEach(card => {
                        card.classList.remove('show');
                    });

                    // Force browser reflow to ensure animation state is reset
                    void document.documentElement.offsetHeight;

                    // Show/hide cards based on category and trigger animation for visible ones
                    projectCards.forEach((card, index) => {
                        if (categoryId === 'all' || card.getAttribute('data-category') === categoryId) {
                            card.style.display = 'flex';

                            // Add staggered animations
                            setTimeout(() => {
                                card.classList.add('show');
                            }, index * 150);
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    // Update active state for filter buttons
                    filterButtons.forEach(btn => btn.classList.remove('bg-[#1E3A5F]'));
                    this.classList.add('bg-[#1E3A5F]');
                });
            });
            const projectFormContainer = document.getElementById('projectFormContainer');
            const addProjectBtn = document.getElementById('addProjectBtn');
            const closeProjectFormModal = document.getElementById('closeProjectFormModal');
            const formContent = projectFormContainer ? projectFormContainer.querySelector('.relative') : null;

            // Configure initial state of animations in CSS
            if (formContent && projectFormContainer && !projectFormContainer.classList.contains('hidden')) {
                // If form is visible on load (due to errors or edit mode)
                formContent.style.opacity = '1';
                document.body.style.overflow = 'hidden';
            } else if (formContent) {
                formContent.style.opacity = '0';
            }

            // Add transition effect
            if (formContent) {
                formContent.style.transition = 'opacity 0.3s ease-in-out';
            }

            // Handle focus on form when there are errors
            if (projectFormContainer && !projectFormContainer.classList.contains('hidden')) {
                // Focus on the first field with error or the first field of the form
                const firstErrorField = document.querySelector('.border-red-500');
                if (firstErrorField) {
                    firstErrorField.focus();
                } else {
                    const firstInput = document.querySelector('#project-title');
                    if (firstInput) firstInput.focus();
                }
            }

            // Function to open project form with animation
            function openProjectForm() {
                if (projectFormContainer) {
                    projectFormContainer.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    // Allow DOM to update before starting animation
                    setTimeout(() => {
                        if (formContent) {
                            formContent.style.opacity = '1';
                        }
                    }, 10);
                }
            }

            // Function to close project form with animation
            function closeProjectForm() {
                if (formContent) {
                    formContent.style.opacity = '0';

                    // Wait for animation to finish before hiding the form
                    setTimeout(() => {
                        if (projectFormContainer) {
                            projectFormContainer.classList.add('hidden');
                            document.body.style.overflow = 'auto';
                        }
                    }, 300); // Duration equal to CSS transition
                } else if (projectFormContainer) {
                    // Fallback if content is not found
                    projectFormContainer.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Add event listener to "Add a project" button
            if (addProjectBtn) {
                addProjectBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    openProjectForm();
                });
            }

            // Add event listener to edit buttons
            document.querySelectorAll('a[href*="project_id="]').forEach(editLink => {
                editLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const editUrl = this.getAttribute('href');

                    // Redirect to the page with the project ID then open the form
                    window.location.href = editUrl;
                });
            });

            // Check if we're on a page with a project_id parameter or errors
            const urlParams = new URLSearchParams(window.location.search);
            // PHP takes care of displaying the form with errors, no need to open via JS
            if (urlParams.has('project_id') && !document.querySelector('.border-red-500')) {
                // Automatically open the form only if in edit mode without errors
                openProjectForm();
            }

            // Add event listener to form close button
            if (closeProjectFormModal) {
                closeProjectFormModal.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeProjectForm();

                    // If we were in edit mode, clean the URL
                    if (urlParams.has('project_id')) {
                        // Redirect to the page without the project_id parameter
                        window.history.pushState({}, '', window.location.pathname + '#projects');
                    }
                });
            }

            // Close the form if user clicks outside
            if (projectFormContainer) {
                projectFormContainer.addEventListener('click', function(event) {
                    if (event.target === projectFormContainer) {
                        closeProjectForm();

                        // If we were in edit mode, clean the URL
                        if (urlParams.has('project_id')) {
                            // Redirect to the page without the project_id parameter
                            window.history.pushState({}, '', window.location.pathname + '#projects');
                        }
                    }
                });
            }

            // Delete Project Modal functionality
            const deleteProjectModal = document.getElementById('deleteProjectModal');
            const deleteModalClose = document.getElementById('delete-project-modal-close');
            const cancelDelete = document.getElementById('cancel-project-delete');
            const confirmDeleteLink = document.getElementById('confirm-delete-project-link');
            const projectTitleToDelete = document.getElementById('project-title-to-delete');
            const modalContent = deleteProjectModal ? deleteProjectModal.querySelector('.relative') : null;

            // Configure initial state of the animation in CSS
            if (modalContent) {
                modalContent.style.opacity = '0';
                modalContent.style.transition = 'opacity 0.3s ease-in-out';
            }

            // Function to open the delete modal with animation
            function openDeleteModal(projectId, projectTitle, deleteUrl) {
                if (deleteProjectModal) {
                    // Update the project title and confirmation link
                    projectTitleToDelete.textContent = decodeHTMLEntities(projectTitle);
                    confirmDeleteLink.href = deleteUrl;

                    deleteProjectModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    // Allow DOM to update before starting animation
                    setTimeout(() => {
                        if (modalContent) {
                            modalContent.style.opacity = '1';
                        }
                    }, 10);
                }
            }

            // Function to close the delete modal with animation
            function closeDeleteModal() {
                if (modalContent) {
                    modalContent.style.opacity = '0';

                    // Wait for animation to finish before hiding the modal
                    setTimeout(() => {
                        if (deleteProjectModal) {
                            deleteProjectModal.classList.add('hidden');
                            document.body.style.overflow = 'auto';
                        }
                    }, 300); // Duration equal to CSS transition
                } else if (deleteProjectModal) {
                    // Fallback if content is not found
                    deleteProjectModal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }
            }

            // Intercept clicks on delete links
            document.querySelectorAll('a[href^="?delete_project="]').forEach(deleteLink => {
                deleteLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const deleteUrl = this.getAttribute('href');
                    // Find the project title (by going up to the parent article then finding h4)
                    const projectCard = this.closest('article');
                    const projectTitle = projectCard ? projectCard.querySelector('h4').textContent : 'ce projet';

                    openDeleteModal(deleteUrl.split('=')[1], projectTitle, deleteUrl);
                });
            });

            // Add event listeners to close the modal
            if (deleteModalClose) {
                deleteModalClose.addEventListener('click', function() {
                    closeDeleteModal();
                });
            }

            if (cancelDelete) {
                cancelDelete.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeDeleteModal();
                });
            }

            // Close the modal if the user clicks outside
            if (deleteProjectModal) {
                deleteProjectModal.addEventListener('click', function(event) {
                    if (event.target === deleteProjectModal) {
                        closeDeleteModal();
                    }
                });
            }

        });
    </script>
</section>