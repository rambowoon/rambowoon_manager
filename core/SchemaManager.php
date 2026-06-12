<?php

class SchemaManager {
    /**
     * Load configuration from a PHP file
     */
    public static function load($projectPath, $fileName) {
        $filePath = $projectPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $fileName;
        if (!file_exists($filePath)) {
            return null;
        }
        return require $filePath;
    }

    /**
     * Save configuration to a PHP file using var_export with short array syntax
     */
    public static function save($projectPath, $fileName, $data) {
        $filePath = $projectPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . $fileName;
        
        // Backup old file
        // if (file_exists($filePath)) {
        //     copy($filePath, $filePath . '.bak');
        // }

        // Special logic for News/Static: No brand, and default tin-tuc active options
        $isNews = (strpos($fileName, 'news') !== false);
        $isStatic = (strpos($fileName, 'static') !== false);
        if ($isNews || $isStatic) {
            foreach ($data as $key => $val) {
                if (is_array($val) && isset($val['brand'])) {
                    unset($data[$key]['brand']);
                }
            }
            if ($isNews) {
                if (isset($data['hinh-thuc-thanh-toan'])) {
                    unset($data['hinh-thuc-thanh-toan']);
                }
                if (isset($data['tin-tuc']) && is_array($data['tin-tuc'])) {
                    $data['tin-tuc']['desc'] = true;
                    $data['tin-tuc']['content'] = true;
                    $data['tin-tuc']['content_cke'] = true;
                }
            }
        }

        // Generate PHP content
        $export = var_export($data, true);
        
        // Convert to short array syntax [] robustly using token_get_all
        $export = self::toShortArraySyntax($export);
        
        // Standardize indentation (4 spaces)
        $export = str_replace("  ", "    ", $export);

        // Inject mandatory logic for News before the final closing bracket
        if ($isNews) {
            $export = rtrim($export);
            if (substr($export, -1) === ']') {
                $export = substr($export, 0, -1);
            }

            $paymentCode = "\n    'hinh-thuc-thanh-toan' => (!empty(\$configSetting['order'])) ? [
        'title_main' => \"hinhthucthanhtoan\",
        'dropdown' => false,
        'copy' => true,
        'status' => [\"hienthi\" => \"hienthi\"],
        'images' => [
            'photo' => [
                'title' => 'anhdaidien',
                'width' => '200',
                'height' => '200',
                'thumb' => '200x200x2'
            ]
        ],
        'show_images' => true,
        'name' => true,
        'content' => true,
        'content_cke' => true,
    ] : []
]";
            $export .= $paymentCode;
        }

        $content = "<?php\n\nreturn " . $export . ";\n";

        return file_put_contents($filePath, $content) !== false;
    }

    public static function toShortArraySyntax($code) {
        $tokens = token_get_all('<?php ' . $code);
        $result = '';
        $arrayStack = [];

        if (count($tokens) > 0 && $tokens[0][0] === T_OPEN_TAG) {
            array_shift($tokens);
        }

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            if (is_array($token)) {
                $id = $token[0];
                $text = $token[1];

                if ($id === T_ARRAY) {
                    $nextIdx = $i + 1;
                    while ($nextIdx < $count && is_array($tokens[$nextIdx]) && $tokens[$nextIdx][0] === T_WHITESPACE) {
                        $nextIdx++;
                    }
                    if ($nextIdx < $count && $tokens[$nextIdx] === '(') {
                        $result .= '[';
                        $arrayStack[] = true;
                        $i = $nextIdx;
                        continue;
                    }
                }
                $result .= $text;
            } else {
                if ($token === ')' && !empty($arrayStack)) {
                    $result .= ']';
                    array_pop($arrayStack);
                } else {
                    $result .= $token;
                }
            }
        }
        return $result;
    }

    /**
     * Get list of type config files in a project
     */
    public static function listConfigFiles($projectPath) {
        $configDir = $projectPath . DIRECTORY_SEPARATOR . 'config';
        if (!is_dir($configDir)) return [];

        $files = scandir($configDir);
        $typeFiles = [];
        foreach ($files as $file) {
            if (strpos($file, 'type-') === 0 && strpos($file, '.php') !== false) {
                $typeFiles[] = $file;
            }
        }
        return $typeFiles;
    }
}
