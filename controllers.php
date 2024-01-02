<?php
require_once 'business.php';

function getImages($folderPath) {
    $imagesPath = $_SERVER['DOCUMENT_ROOT'] . $folderPath;
    $images = scandir($imagesPath);
    $images = array_diff($images, ['.', '..']);

    $images = array_filter($images, function ($file) {
        return !in_array($file, ['.DS_Store']);
    });

    return array_values($images);
}

function getPhotos($folderPath, $page, $photosPerPage) {
    $thumbnailPath = getImages($folderPath);
    $watermarkPath = getImages('/images/watermarks');
    $startIndex = ($page - 1) * $photosPerPage;
    $photos = [];

    for ($i = $startIndex; $i < min($startIndex + $photosPerPage, count($thumbnailPath)); $i++) {
        $thumbnail = $thumbnailPath[$i];
        $watermark = $watermarkPath[$i];

        $photos[] = [
            'thumbnail' => '/images/thumbnails/'. $thumbnail,
            'watermark' => '/images/watermarks/'. $watermark
        ];
    }
    
    return $photos;
}

function getTotalPages($folderPath, $photosPerPage) {
    $filesPath = $_SERVER['DOCUMENT_ROOT'].$folderPath;
    $files = scandir($filesPath);
    $files = array_diff($files, ['.', '..']);

    $files = array_filter($files, function($file) {
        return !in_array($file, ['.DS_Store']);
    });

    $files = array_values($files);

    return ceil(count($files) / $photosPerPage);
}

function galeria(&$model){
    $folderPath = '/images/thumbnails';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    $photos = getPhotos($folderPath, $page, 4);
    $totalPages = getTotalPages($folderPath, 4);

    $model['photos'] = $photos;
    $model['totalPages'] = $totalPages;
    
    return 'galeria_view';
}

function login(&$model){
    $errorMessage = '0';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['login']) && isset($_POST['haslo'])) {
            $login = $_POST['login'];
            $haslo = $_POST['haslo'];

            $user = get_user($login);
            if ($user != NULL) {
                if (password_verify($haslo, $user['haslo'])){
                    $_SESSION['user'] = $login;
                    $errorMessage = "Zalogowano";
                    $_SESSION['error'] = $errorMessage;
                    return 'redirect:galeria';
                }
                else $errorMessage = "Bledne haslo";
            }
            else {
                $errorMessage = "Taki użytkownik nie istnieje";
            }
        }
        else {
            $errorMessage = "Wszystkie pola sa obowiazkowe";
        }
    }
    $_SESSION['error'] = $errorMessage;
    return 'log_view';
}

function logout(&$model){
    session_unset();
    $_SESSION['error'] = "Wylogowano";
    return 'redirect:galeria';
}

function reg(&$model){
    $errorMessage = '0';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['login']) && isset($_POST['haslo']) && isset($_POST['haslo2']) && isset($_POST['email'])) {
            if ($_POST['haslo'] == $_POST['haslo2']){
                $login = $_POST['login'];
                $haslo = $_POST['haslo'];
                $email = $_POST['email'];
                $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);

                if (!get_user($login)) {
                    $user = ['login' => $login, 'email' => $email, 'haslo' => $haslo_hash];
                    save_user($user);
                    $_SESSION['user'] = $login;
                    $errorMessage = "Stworzono konto";
                    $_SESSION['error'] = $errorMessage;
                    return "redirect:galeria";
                }
                else {
                    $errorMessage = "Taki użytkownik już istnieje";
                }
            }
            else {
                $errorMessage = "Hasla powinny byc te same";
            }
        }
        else {
            $errorMessage = "Wszystkie pola sa obowiazkowe";
        }
    }
    $_SESSION['error'] = $errorMessage;
    return 'reg_view';
}

function createWatermark($uploadDir, $imgPath, $uniqueIdentifier, $fileExtension){

    $watermarkedPath = $uploadDir . '/watermarks/watermark_' . $uniqueIdentifier . '.' . $fileExtension;
    $imageInfo = getimagesize($imgPath);
    $watermarkText = $_POST['watermark'];

    if ($imageInfo !== false) {
        // Determine image type
        $imageType = $imageInfo[2];  // Corresponds to IMAGETYPE_ constants

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($imgPath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($imgPath);
                break;
            default:
                die("Unsupported image type.");
        }

        // Get the dimensions of the original image
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        $watermarkImage = imagecreatetruecolor($originalWidth, $originalHeight);

        // Set the background of the watermark to transparent
        $transparentColor = imagecolorallocatealpha($watermarkImage, 0, 0, 0, 127);
        imagefill($watermarkImage, 0, 0, $transparentColor);
        imagesavealpha($watermarkImage, true);

        imagecopy($watermarkImage, $originalImage, 0, 0, 0, 0, $originalWidth, $originalHeight);
        // Load a TrueType font for the watermark text
        $fontColor = imagecolorallocate($watermarkImage, 255, 255, 255); // Font color (white in this example)
        $borderColor = imagecolorallocate($watermarkImage, 0, 0, 0); // Border color (black in this example)
        $fontSize = 40;

        for ($x = -1; $x <= 1; $x++) {
            for ($y = -1; $y <= 1; $y++) {
                imagettftext($watermarkImage, $fontSize, 0, 10 + $x, $originalHeight - 10 + $y, $borderColor, '/var/www/dev/src/arial.ttf', $watermarkText);
            }
        }
        // Add the watermark text to the image
        imagettftext($watermarkImage, $fontSize, 0, 10, $originalHeight - 10, $fontColor, '/var/www/dev/src/arial.ttf', $watermarkText);

        // Merge the original image with the watermark
        imagecopy($originalImage, $watermarkImage, 0, 0, 0, 0, $originalWidth, $originalHeight);

        imagejpeg($watermarkImage, $watermarkedPath, 85); // Use 85 as the quality parameter (adjust as needed)
        // Free up memory
        imagedestroy($originalImage);
        imagedestroy($watermarkImage);

        return true;
    } else {
        return false;
    }
}

function createThumbnail($uploadDir, $imgPath, $uniqueIdentifier, $fileExtension){

    $thumbnailPath = $uploadDir . '/thumbnails/thumbnail_' . $uniqueIdentifier . '.' . $fileExtension;
    $imageInfo = getimagesize($imgPath);

    if ($imageInfo !== false) {
        // Determine image type
        $imageType = $imageInfo[2];  // Corresponds to IMAGETYPE_ constants

        // Load the original image based on its type
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($imgPath);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($imgPath);
                break;
            default:
                die("Unsupported image type.");
        }

        // Get the dimensions of the original image
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // Create an empty image for the thumbnail
        $thumbnailImage = imagecreatetruecolor(200, 125);

        // Resize and copy the original image to the thumbnail
        imagecopyresampled($thumbnailImage, $originalImage, 0, 0, 0, 0, 200, 125, $originalWidth, $originalHeight);

        // Save the thumbnail in JPG format
        imagejpeg($thumbnailImage, $thumbnailPath, 85);
        // Free up memory
        imagedestroy($originalImage);
        imagedestroy($thumbnailImage);

        return true;
    } else {
        return false;
    }
}

function img(&$model){
    $maxFileSize = 1 * 1024 * 1024;
    $errorMessage = '0';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if (isset($_POST['watermark']) && !empty($_POST['watermark'])) {
                $uniqueIdentifier = uniqid();
                    //directory
                $uploadDir = $_SERVER['DOCUMENT_ROOT'].'/images/';

                    //check extensions
                $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['png', 'jpg'];

                $orginalPath = $uploadDir . 'orginal_'.$uniqueIdentifier.'.'.$fileExtension;
                if ($_FILES['image']['size'] <= $maxFileSize && in_array($fileExtension, $allowedExtensions)){
                        // Move uploaded file to the desired directory
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $orginalPath)) {
                        createThumbnail($uploadDir, $orginalPath, $uniqueIdentifier, $fileExtension);
                        createWatermark($uploadDir, $orginalPath, $uniqueIdentifier, $fileExtension);
                        if (isset($_POST['autor']) && isset($_POST['tytul'])){
                            $autor = $_POST['autor'];
                            $tytul = $_POST['tytul'];
                            $nazwa = '/images/thumbnails/thumbnail_'.$uniqueIdentifier;;
                
                            $zdj = ['nazwa' => $nazwa, 'autor' => $autor, 'tytul' => $tytul];
                            save_zdj($zdj);
                        }
                        $errorMessage = "Plik zostal udostepniony";
                        $_SESSION['error'] = $errorMessage;
                        return 'redirect:galeria';
                    } 
                    else {
                        $errorMessage = "Nie udalo sie udostepnic pliku";
                    }
                }
                else {
                    if ($_FILES['image']['size'] > $maxFileSize && in_array($fileExtension, $allowedExtensions)){
                        $errorMessage = "Plik jest za duzy";
                    }
                    else if ($_FILES['image']['size'] <= $maxFileSize && !in_array($fileExtension, $allowedExtensions)){
                        $errorMessage = "Niepoprawny format pliku";
                    }
                    else {
                        $errorMessage = "Plik jest za duzy oraz niepoprawny format";
                    }
                }
            }
            else {
                $errorMessage = "Znak wodny jest obowiazkowy";
            }
        } 
        else {
            $errorMessage = "Brak pliku";
        }
    }
    $_SESSION['error'] = $errorMessage;
    return 'img_view';
}
