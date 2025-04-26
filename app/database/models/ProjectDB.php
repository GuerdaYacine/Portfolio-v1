<?php

class ProjectDB
{
    private PDOStatement $statementReadOneProject;
    private PDOStatement $statementReadAllProjects;
    private PDOStatement $statementReadAllCategories;
    private PDOStatement $statementReadAllTechnologies;
    private PDOStatement $statementReadProjectTechnologies;
    private PDOStatement $statementDeleteProject;
    private PDOStatement $statementDeleteProjectTechnologies;
    private PDOStatement $statementUpdateProject;
    private PDOStatement $statementCreateProject;
    private PDOStatement $statementCreateProjectTechnology;

    function __construct(private PDO $pdo)
    {
        $this->statementReadOneProject = $pdo->prepare('
            SELECT p.*, c.name as category_name 
            FROM projects p
            JOIN categories_projects c ON p.category_id = c.id
            WHERE p.id = :id
        ');

        $this->statementReadAllProjects = $pdo->prepare('
            SELECT p.*, c.name as category_name 
            FROM projects p
            JOIN categories_projects c ON p.category_id = c.id
            ORDER BY p.date_of_realisation DESC
        ');

        $this->statementReadAllCategories = $pdo->prepare('SELECT * FROM categories_projects');

        $this->statementReadAllTechnologies = $pdo->prepare('SELECT * FROM timeline_technos');

        $this->statementReadProjectTechnologies = $pdo->prepare('
            SELECT techno_id FROM project_technos WHERE project_id = :project_id
        ');

        $this->statementDeleteProject = $pdo->prepare('DELETE FROM projects WHERE id = :id');

        $this->statementDeleteProjectTechnologies = $pdo->prepare('
            DELETE FROM project_technos WHERE project_id = :project_id
        ');

        $this->statementUpdateProject = $pdo->prepare('
            UPDATE projects 
            SET title = :title, 
                date_of_realisation = :date, 
                description = :description, 
                context = :context, 
                image = :image, 
                icon = :icon, 
                link = :link, 
                category_id = :category_id
            WHERE id = :id
        ');

        $this->statementCreateProject = $pdo->prepare('
            INSERT INTO projects (
                title, 
                date_of_realisation, 
                description, 
                context, 
                image, 
                icon, 
                link, 
                category_id
            )
            VALUES (
                :title, 
                :date, 
                :description, 
                :context, 
                :image, 
                :icon, 
                :link, 
                :category_id
            )
        ');

        $this->statementCreateProjectTechnology = $pdo->prepare('
            INSERT INTO project_technos (project_id, techno_id)
            VALUES (:project_id, :techno_id)
        ');
    }

    function getProjectById(int $id): array|false
    {
        $this->statementReadOneProject->execute(['id' => $id]);
        $project = $this->statementReadOneProject->fetch();

        if ($project) {
            $project['technologies'] = $this->getProjectTechnologies($id);
        }

        return $project;
    }

    function getAllProjects(): array
    {
        $this->statementReadAllProjects->execute();
        $projects = $this->statementReadAllProjects->fetchAll();

        foreach ($projects as &$project) {
            $project['technologies'] = $this->getProjectTechnologies($project['id']);
        }

        return $projects;
    }

    function getAllCategories(): array
    {
        $this->statementReadAllCategories->execute();
        return $this->statementReadAllCategories->fetchAll();
    }

    function getAllTechnologies(): array
    {
        $this->statementReadAllTechnologies->execute();
        return $this->statementReadAllTechnologies->fetchAll();
    }

    function getProjectTechnologies(int $projectId): array
    {
        $this->statementReadProjectTechnologies->execute(['project_id' => $projectId]);
        $technoIds = $this->statementReadProjectTechnologies->fetchAll(PDO::FETCH_COLUMN);

        if (empty($technoIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($technoIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT id, title, image 
            FROM timeline_technos 
            WHERE id IN ($placeholders)
        ");

        $stmt->execute($technoIds);
        return $stmt->fetchAll();
    }

    function deleteProject(int $id): bool
    {
        $project = $this->getProjectById($id);

        if (!$project) {
            return false;
        }

        if (!empty($project['image']) && file_exists($project['image'])) {
            unlink($project['image']);
        }

        if (!empty($project['icon']) && file_exists($project['icon'])) {
            unlink($project['icon']);
        }

        $this->statementDeleteProjectTechnologies->execute(['project_id' => $id]);

        return $this->statementDeleteProject->execute(['id' => $id]);
    }

    function createProject(array $data, array $technologies): int
    {
        $this->statementCreateProject->execute([
            'title' => $data['title'],
            'date' => $data['date'],
            'description' => $data['description'],
            'context' => $data['context'],
            'image' => $data['image'],
            'icon' => $data['icon'],
            'link' => $data['link'],
            'category_id' => $data['category_id']
        ]);

        $projectId = $this->pdo->lastInsertId();

        foreach ($technologies as $technoId) {
            $this->statementCreateProjectTechnology->execute([
                'project_id' => $projectId,
                'techno_id' => $technoId
            ]);
        }

        return $projectId;
    }

    function updateProject(int $id, array $data, array $technologies): bool
    {
        $result = $this->statementUpdateProject->execute([
            'id' => $id,
            'title' => $data['title'],
            'date' => $data['date'],
            'description' => $data['description'],
            'context' => $data['context'],
            'image' => $data['image'],
            'icon' => $data['icon'],
            'link' => $data['link'],
            'category_id' => $data['category_id']
        ]);

        if ($result) {
            $this->statementDeleteProjectTechnologies->execute(['project_id' => $id]);

            foreach ($technologies as $technoId) {
                $this->statementCreateProjectTechnology->execute([
                    'project_id' => $id,
                    'techno_id' => $technoId
                ]);
            }
        }

        return $result;
    }

    function saveImage(array $fileData, string $directory, ?string $currentImage = null): array
    {
        $errors = '';
        $imageFileName = $currentImage ?? '';

        if (empty($fileData['name']) && empty($currentImage)) {
            return ['error' => 'L\'image est obligatoire', 'filename' => $imageFileName];
        }

        if (!empty($fileData['name'])) {
            $imageInfo = pathinfo($fileData['name']);
            $imageExtension = strtolower($imageInfo['extension']);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if ($directory === 'icons') {
                $allowedExtensions[] = 'svg';
            }

            if (!in_array($imageExtension, $allowedExtensions)) {
                return ['error' => 'Format d\'image non autorisé. Utilisez ' . implode(', ', $allowedExtensions), 'filename' => $imageFileName];
            }

            if ($fileData['size'] > 2097152) {
                return ['error' => 'L\'image ne doit pas dépasser 2 Mo', 'filename' => $imageFileName];
            }

            $prefix = ($directory === 'icons') ? 'icon_' : 'img_';
            $uniqueFileName = uniqid($prefix) . '.' . $imageExtension;
            $uploadPath = 'assets/projects/' . $directory . '/' . $uniqueFileName;
            $imageFileName = $uploadPath;

            if (!is_dir('assets/projects/' . $directory)) {
                mkdir('assets/projects/' . $directory, 0777, true);
            }

            if ($currentImage && file_exists($currentImage)) {
                unlink($currentImage);
            }

            if (move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
                return ['error' => '', 'filename' => $imageFileName];
            }

            return ['error' => 'Erreur lors du téléchargement de l\'image', 'filename' => $imageFileName];
        }

        return ['error' => '', 'filename' => $imageFileName];
    }

    function validateProjectData(array $data, array $files): array
    {
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

        if (empty($data['title'])) {
            $errors['title'] = 'Veuillez saisir un titre';
        }

        if (empty($data['date'])) {
            $errors['date'] = 'Veuillez saisir une date';
        } else {
            if (preg_match('/^\d{4}-\d{2}$/', $data['date'])) {
                $data['date'] = $data['date'] . '-01';
            }

            $dateTime = DateTime::createFromFormat('Y-m-d', $data['date']);
            if (!$dateTime || $dateTime->format('Y-m-d') !== $data['date']) {
                $errors['date'] = 'Format de date invalide';
            }
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Veuillez saisir une description';
        }

        if (empty($data['context'])) {
            $errors['context'] = 'Veuillez saisir un context';
        }

        if (empty($data['technologies'])) {
            $errors['technologies'] = 'Veuillez sélectionner au moins une technologie';
        }

        if (empty($data['link'])) {
            $errors['link'] = 'Veuillez saisir un lien';
        }

        if (empty($data['category_id'])) {
            $errors['category'] = 'Veuillez sélectionner une catégorie';
        }

        if (empty($data['current_image']) && (empty($files['image']['name']) || $files['image']['error'] !== 0)) {
            $errors['image'] = 'Veuillez ajouter une image pour le projet';
        }

        if (empty($data['current_icon']) && (empty($files['icon']['name']) || $files['icon']['error'] !== 0)) {
            $errors['icon'] = 'Veuillez ajouter une icône pour le projet';
        }

        return $errors;
    }
}

return new ProjectDB($pdo);
