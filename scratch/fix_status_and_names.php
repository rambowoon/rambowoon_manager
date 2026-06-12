<?php
$file = dirname(__DIR__) . '/seed_images.php';
$content = file_get_contents($file);

// Normalize line endings
$normContent = str_replace("\r\n", "\n", $content);

// 1. Target for status replacement using single quotes
$targetStatus = '                    if (array_key_exists(\'status\', $raw)) {
                        $statusOptions = is_array($raw[\'status\']) ? array_keys($raw[\'status\']) : [\'hienthi\'];
                        if (in_array(\'status\', $columns)) {
                            $cols[] = \'status\'; $vals[] = $statusOptions[0];
                        }
                    }';

$replacementStatus = '                    if (in_array(\'status\', $columns)) {
                        $cols[] = \'status\'; $vals[] = \'hienthi\';
                    }';

$normTargetStatus = str_replace("\r\n", "\n", $targetStatus);
$normReplacementStatus = str_replace("\r\n", "\n", $replacementStatus);

$replacedStatus = false;

if (strpos($normContent, $normTargetStatus) !== false) {
    $normContent = str_replace($normTargetStatus, $normReplacementStatus, $normContent);
    $replacedStatus = true;
}

if ($replacedStatus) {
    $finalContent = str_replace("\n", "\r\n", $normContent);
    file_put_contents($file, $finalContent);
    echo "SUCCESS: Replaced status logic (Status: Yes)\n";
} else {
    echo "ERROR: Target not found.\n";
}
