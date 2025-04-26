<?php

class CertificationDB
{
    private PDOStatement $statementReadOneCertif;
    private PDOStatement $statementReadAllCertif;
    private PDOStatement $statementDeleteCertif;
    private PDOStatement $statementUpdateCertif;
    private PDOStatement $statementCreateCertif;

    function __construct(private PDO $pdo)
    {
        $this->statementReadOneCertif = $pdo->prepare('SELECT * FROM certifications WHERE id=:id');
        $this->statementReadAllCertif = $pdo->prepare('SELECT * FROM certifications ORDER BY date_learned DESC');
        $this->statementDeleteCertif = $pdo->prepare('DELETE FROM certifications WHERE id=:id');
        $this->statementUpdateCertif = $pdo->prepare('UPDATE certifications SET 
            title=:title,
            description=:description,
            date_learned=:date,
            image=:image,
            link=:link
            WHERE id=:id
        ');
        $this->statementCreateCertif = $pdo->prepare('INSERT INTO certifications (
            title,
            description,
            date_learned,
            image,
            link
            ) VALUES (
            :title,
            :description,
            :date,
            :image,
            :link
        )');
    }

    function getAllCertifications(): array
    {
        $this->statementReadAllCertif->execute();
        return $this->statementReadAllCertif->fetchAll();
    }

    function getCertificationById(int $id): array|false
    {
        $this->statementReadOneCertif->execute(['id' => $id]);
        return $this->statementReadOneCertif->fetch();
    }

    function createCertification(string $title, string $description, string $date, string $image, string $link): bool
    {
        return $this->statementCreateCertif->execute([
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'image' => $image,
            'link' => $link
        ]);
    }

    function updateCertification(int $id, string $title, string $description, string $date, string $image, string $link): bool
    {
        return $this->statementUpdateCertif->execute([
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'date' => $date,
            'image' => $image,
            'link' => $link
        ]);
    }

    function deleteCertification(int $id): bool
    {
        return $this->statementDeleteCertif->execute(['id' => $id]);
    }

    function saveImage(array $fileData, ?string $currentImage = null): array
    {
        $errors = '';
        $imageFileName = $currentImage;

        if (empty($fileData['name']) && empty($currentImage)) {
            return ['error' => 'L\'image est obligatoire', 'filename' => $imageFileName];
        }

        if (!empty($fileData['name'])) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileExtension = strtolower(pathinfo($fileData['name'], PATHINFO_EXTENSION));

            if (!in_array($fileExtension, $allowedExtensions)) {
                return ['error' => 'Seuls les formats JPG, JPEG, PNG, GIF et WEBP sont acceptés', 'filename' => $imageFileName];
            }

            if ($fileData['size'] > 2097152) {
                return ['error' => 'L\'image ne doit pas dépasser 2 Mo', 'filename' => $imageFileName];
            }

            $imageFileName = uniqid('img_', true) . '.' . $fileExtension;
            $uploadPath = 'assets/images-certifications/' . $imageFileName;

            if (!is_dir('assets/images-certifications')) {
                mkdir('assets/images-certifications', 0777, true);
            }

            if ($currentImage) {
                $oldImagePath = 'assets/images-certifications/' . basename($currentImage);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            if (move_uploaded_file($fileData['tmp_name'], $uploadPath)) {
                return ['error' => '', 'filename' => $imageFileName];
            }

            return ['error' => 'Erreur lors du téléchargement de l\'image', 'filename' => $imageFileName];
        }

        return ['error' => '', 'filename' => $imageFileName];
    }

    function validateCertificationData(string $title, string $description, string $date, string $link): array
    {
        $errors = [
            'title' => '',
            'description' => '',
            'date' => '',
            'image' => '',
            'link' => ''
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

        if (empty($link)) {
            $errors['link'] = 'Le lien est obligatoire';
        } elseif (!filter_var($link, FILTER_VALIDATE_URL)) {
            $errors['link'] = 'Le lien doit être une URL valide';
        }

        return $errors;
    }
}

return new CertificationDB($pdo);
