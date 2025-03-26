<?php
// Include functions.php to use sanitize function
require_once __DIR__ . '/../includes/functions.php';

class FileHandler {
    /**
     * Upload file with better error handling
     * @param array $file The $_FILES array element
     * @param string $targetDir The target directory
     * @param array $allowedTypes Allowed file extensions
     * @return array Result with success/error status
     */
    public static function uploadFile($file, $targetDir, $allowedTypes = []) {
        // Check if target directory exists, create if it doesn't
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                return ['error' => 'Failed to create upload directory.'];
            }
        }
        
        // Make sure directory is writable
        if (!is_writable($targetDir)) {
            chmod($targetDir, 0777);
            if (!is_writable($targetDir)) {
                return ['error' => 'Upload directory is not writable. Please check permissions.'];
            }
        }

        if ($file['error'] != 0) {
            $errorMessages = [
                1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk',
                8 => 'A PHP extension stopped the file upload'
            ];
            
            $errorCode = $file['error'];
            $errorMessage = isset($errorMessages[$errorCode]) ? $errorMessages[$errorCode] : 'Unknown upload error';
            
            return ['error' => $errorMessage];
        }
        
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check file extension
        if (!empty($allowedTypes) && !in_array($fileExt, $allowedTypes)) {
            return ['error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)];
        }
        
        // Sanitize the filename before using it
        $sanitizedName = sanitize(basename($file['name']));
        
        // Generate unique filename to prevent overwrites
        $filename = uniqid() . '_' . $sanitizedName;
        $targetPath = $targetDir . '/' . $filename;
        
        // Move the file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['error' => 'Failed to upload file. Please try again.'];
        }
        
        // Return success with path
        return [
            'success' => true,
            'path' => $targetPath,
            'filename' => $filename
        ];
    }

    /**
     * Get MIME type for a file
     * @param string $filename Filename
     * @return string MIME type
     */
    public static function getMimeType($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'txt' => 'text/plain',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip' => 'application/zip',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Serve a file for download
     * @param string $filePath File path
     * @param string $fileName Filename
     */
    public static function serveFileDownload($filePath, $fileName) {
        if (!file_exists($filePath)) {
            header('HTTP/1.0 404 Not Found');
            echo "File not found";
            exit;
        }
        
        $mimeType = self::getMimeType($fileName);
        
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        
        readfile($filePath);
        exit;
    }
}
?>
