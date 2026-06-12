<?php
$file = dirname(__DIR__) . '/seed_images.php';
$lines = file($file);

$foundAct = false;
for ($i = 0; $i < count($lines); $i++) {
    if (trim($lines[$i]) === "':act' => \$level,") {
        $lines[$i] = str_replace("':act' => \$level,", "':act' => 'save',", $lines[$i]);
        $foundAct = true;
        
        // Now find the closest $stSlug above this line
        $stSlugIndex = -1;
        for ($j = $i - 1; $j >= 0; $j--) {
            if (strpos($lines[$j], '$stSlug = $pdo->prepare') !== false) {
                $stSlugIndex = $j;
                break;
            }
        }
        
        if ($stSlugIndex !== -1) {
            // Insert $comVal = $prefix . '-' . $level; before $stSlug
            // But first, let's find the matching indentation
            preg_match('/^(\s*)/', $lines[$stSlugIndex], $matches);
            $indent = $matches[1] ?? '                                    ';
            array_splice($lines, $stSlugIndex, 0, [$indent . "\$comVal = \$prefix . '-' . \$level;\r\n"]);
            
            // Adjust indices because we spliced
            $i++;
            
            // Now rename $comVal = 'news' etc. to $prefix = 'news' in the lines between start of this category block and $stSlug
            for ($k = $stSlugIndex; $k >= $stSlugIndex - 20; $k--) {
                if ($k < 0) break;
                if (strpos($lines[$k], "\$comVal = 'news'") !== false) {
                    $lines[$k] = str_replace("\$comVal = 'news'", "\$prefix = 'news'", $lines[$k]);
                }
                if (strpos($lines[$k], "\$comVal = 'product'") !== false) {
                    $lines[$k] = str_replace("\$comVal = 'product'", "\$prefix = 'product'", $lines[$k]);
                }
                if (strpos($lines[$k], "\$comVal = 'photo'") !== false) {
                    $lines[$k] = str_replace("\$comVal = 'photo'", "\$prefix = 'photo'", $lines[$k]);
                }
            }
        }
        break; // Only modify the second occurrence
    }
}

if ($foundAct) {
    file_put_contents($file, implode('', $lines));
    echo "SUCCESS: Modified category slug variables and act parameter.\n";
} else {
    echo "ERROR: ':act' => \$level, not found.\n";
}
