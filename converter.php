<?php
/**
 * Image Converter Backend
 * Độc lập với luồng quản lý dự án
 */

// Tăng giới hạn xử lý
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');
@ini_set('post_max_size', '100M');
@ini_set('upload_max_filesize', '100M');
@ini_set('max_file_uploads', '100');

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'info') {
        header('Content-Type: application/json');
        echo json_encode([
            'max_file_uploads' => ini_get('max_file_uploads'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize')
        ]);
        exit;
    }

    if ($action === 'convert' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (empty($_FILES['images'])) {
            echo json_encode(['status' => 'error', 'message' => 'Không có file nào được tải lên']);
            exit;
        }

        $format = $_POST['format'] ?? 'webp';
        $quality = (int)($_POST['quality'] ?? 100);
        $deep = (bool)($_POST['deep'] ?? 0);
        
        $uploadDir = __DIR__ . '/temp_conv/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $results = [];
        $zipName = 'converted_images_' . time() . '.zip';
        $zipPath = $uploadDir . $zipName;
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
            echo json_encode(['status' => 'error', 'message' => 'Không thể tạo file Zip']);
            exit;
        }

        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            $originalName = $_FILES['images']['name'][$key];
            $nameOnly = pathinfo($originalName, PATHINFO_FILENAME);
            
            $img = null;
            $info = getimagesize($tmpName);
            if (!$info) continue;

            switch ($info[2]) {
                case IMAGETYPE_JPEG: $img = imagecreatefromjpeg($tmpName); break;
                case IMAGETYPE_PNG:  $img = imagecreatefrompng($tmpName); break;
                case IMAGETYPE_WEBP: $img = imagecreatefromwebp($tmpName); break;
            }

            if ($img) {
                $outExt = ($format === 'webp') ? '.webp' : '.jpg';
                $outName = $nameOnly . $outExt;
                $outPath = $uploadDir . $outName;

                if ($deep) {
                    @imagefilter($img, IMG_FILTER_SMOOTH, 5);
                }
                $qVal = $quality;
                if ($deep) {
                    $qVal = min(80, $quality);
                }
                if ($format === 'webp') {
                    if ($quality === 101) {
                        $qVal = defined('IMG_WEBP_LOSSLESS') ? IMG_WEBP_LOSSLESS : 101;
                    }
                    imagewebp($img, $outPath, $qVal);
                } else {
                    imagejpeg($img, $outPath, $qVal);
                }
                
                $zip->addFile($outPath, $outName);
                $results[] = $outName;
                imagedestroy($img);
            }
        }

        $zip->close();

        foreach ($results as $file) { @unlink($uploadDir . $file); }

        echo json_encode([
            'status' => 'success',
            'message' => 'Chuyển đổi thành công ' . count($results) . ' ảnh',
            'download_url' => 'converter.php?action=download&file=' . $zipName
        ]);
        exit;
    }

    if ($action === 'download' && isset($_GET['file'])) {
        $fileName = basename($_GET['file']);
        $filePath = __DIR__ . '/temp_conv/' . $fileName;

        if (file_exists($filePath) && strpos($fileName, 'converted_images_') === 0) {
            if (ob_get_level()) ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            
            readfile($filePath);
            exit;
        } else {
            die("File không tồn tại hoặc đã bị dọn dẹp.");
        }
    }
}

// Tự động dọn dẹp file cũ hơn 1 tiếng
if (is_dir(__DIR__ . '/temp_conv/')) {
    $files = glob(__DIR__ . '/temp_conv/*');
    foreach ($files as $file) {
        if (is_file($file) && (time() - filemtime($file) > 3600)) @unlink($file);
    }
}
