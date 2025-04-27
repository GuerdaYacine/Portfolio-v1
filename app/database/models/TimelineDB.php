<?php

class TimelineDB
{
    private PDOStatement $statementReadOneEvent;
    private PDOStatement $statementReadAllEvents;
    private PDOStatement $statementDeleteEvent;
    private PDOStatement $statementUpdateEvent;
    private PDOStatement $statementCreateEvent;
    private string $uploadDir;
    private string $uploadDirRelative = 'assets/images-timeline/';

    function __construct(private PDO $pdo)
    {
        $this->statementReadOneEvent = $pdo->prepare('SELECT * FROM timeline_technos WHERE id=:id');
        $this->statementReadAllEvents = $pdo->prepare('SELECT * FROM timeline_technos ORDER BY date_learned DESC');
        $this->statementDeleteEvent = $pdo->prepare('DELETE FROM timeline_technos WHERE id=:id');
        $this->statementUpdateEvent = $pdo->prepare('UPDATE timeline_technos SET 
            title=:title,
            description=:description,
            date_learned=:date,
            image=:image
            WHERE id=:id
        ');
        $this->statementCreateEvent = $pdo->prepare('INSERT INTO timeline_technos (
            title,
            description,
            date_learned,
            image
            ) VALUES (
            :title,
            :description,
            :date,
            :image
        )');

        // Définir le chemin absolu du répertoire d'upload
        $this->uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $this->uploadDirRelative;

        // S'assurer que le chemin est correct même quand DOCUMENT_ROOT contient déjà un slash final
        $this->uploadDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $this->uploadDirRelative;
    }

    function getAllEvents(): array
    {
        $this->statementReadAllEvents->execute();
        return $this->statementReadAllEvents->fetchAll();
    }

    function getEventById(int $id): array|false
    {
        $this->statementReadOneEvent->execute(['id' => $id]);
        return $this->statementReadOneEvent->fetch();
    }

    function createEvent(string $title, string $description, string $date, string $image): bool
    {
        return $this->statementCreateEvent->execute([
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'image' => $image
        ]);
    }

    function updateEvent(int $id, string $title, string $description, string $date, string $image): bool
    {
        return $this->statementUpdateEvent->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'image' => $image
        ]);
    }

    function deleteEvent(int $id): bool
    {
        return $this->statementDeleteEvent->execute(['id' => $id]);
    }

    function saveImage(array $fileData, ?string $currentImage = null): array
    {
        $errors = '';
        $imageFileName = $currentImage;

        if (empty($fileData['name'])) {
            return ['error' => 'L\'image est obligatoire', 'filename' => $imageFileName];
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileExtension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

        if (!in_array($fileExtension, $allowedExtensions)) {
            return ['error' => 'Seuls les formats JPG, JPEG, PNG, GIF et WEBP sont acceptés', 'filename' => $imageFileName];
        }

        if ($fileData['size'] > 2097152) {
            return ['error' => 'L\'image ne doit pas dépasser 2 Mo', 'filename' => $imageFileName];
        }

        // Vérifier si le fichier temporaire existe et est un upload valide
        if (!file_exists($fileData['tmp_name']) || !is_uploaded_file($fileData['tmp_name'])) {
            return ['error' => 'Le fichier temporaire n\'existe pas ou n\'est pas un fichier uploadé', 'filename' => $imageFileName];
        }

        $imageFileName = uniqid('img_', true) . '.' . $fileExtension;
        $uploadPath = $this->uploadDir . $imageFileName;

        // Créer le répertoire d'upload si nécessaire
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                $error = error_get_last();
                return ['error' => 'Impossible de créer le répertoire d\'upload: ' . ($error['message'] ?? 'Raison inconnue'), 'filename' => $imageFileName];
            }
        }

        // Vérifier les permissions d'écriture
        if (!is_writable($this->uploadDir)) {
            return ['error' => 'Le répertoire d\'upload n\'est pas accessible en écriture', 'filename' => $imageFileName];
        }

        if ($currentImage) {
            $oldImagePath = $this->uploadDir . basename($currentImage);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        if (!move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
            $error = error_get_last();
            return ['error' => 'Erreur lors du téléchargement de l\'image: ' . ($error['message'] ?? 'Raison inconnue'), 'filename' => $imageFileName];
        }

        return ['error' => '', 'filename' => $imageFileName];
    }

    function getUploadDirRelative(): string
    {
        return $this->uploadDirRelative;
    }

    function validateEventData(string $title, string $description, string $date): array
    {
        $errors = [
            'title' => '',
            'description' => '',
            'date' => '',
            'image' => ''
        ];

        if (empty($title)) {
            $errors['title'] = 'Le titre est obligatoire';
        }

        if (empty($description)) {
            $errors['description'] = 'La description est obligatoire';
        }

        if (empty($date)) {
            $errors['date'] = 'La date est obligatoire';
        }

        return $errors;
    }
}

return new TimelineDB($pdo);
