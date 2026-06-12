<?php
$file = dirname(__DIR__) . '/seed_images.php';
$lines = file($file);

$replacedCount = 0;
for ($i = 0; $i < count($lines); $i++) {
    if ($i >= 335 && $i <= 505) {
        if (strpos($lines[$i], "'\\\\NASANICORE\\\\Models\\\\NewsModel'") !== false) {
            $lines[$i] = str_replace("'\\\\NASANICORE\\\\Models\\\\NewsModel'", "'\\\\NASANICORE\\\\Models\\\\News' . ucfirst(\$level) . 'Model'", $lines[$i]);
            $replacedCount++;
        }
        if (strpos($lines[$i], "'\\\\NASANICORE\\\\Models\\\\ProductModel'") !== false) {
            $lines[$i] = str_replace("'\\\\NASANICORE\\\\Models\\\\ProductModel'", "'\\\\NASANICORE\\\\Models\\\\Product' . ucfirst(\$level) . 'Model'", $lines[$i]);
            $replacedCount++;
        }
        if (strpos($lines[$i], "'\\\\NASANICORE\\\\Models\\\\PhotoModel'") !== false) {
            $lines[$i] = str_replace("'\\\\NASANICORE\\\\Models\\\\PhotoModel'", "'\\\\NASANICORE\\\\Models\\\\Photo' . ucfirst(\$level) . 'Model'", $lines[$i]);
            $replacedCount++;
        }
    }
}

if ($replacedCount > 0) {
    file_put_contents($file, implode('', $lines));
    echo "SUCCESS: Replaced $replacedCount category model names.\n";
} else {
    echo "ERROR: No targets found in the specified line range.\n";
}
