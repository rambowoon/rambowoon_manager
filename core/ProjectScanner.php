<?php
namespace RamboWoon;

class ProjectScanner {
    private $baseDir;

    public function __construct($baseDir) {
        $this->baseDir = $baseDir;
    }

    public function getCategories($strictMonth = false) {
        $categories = [];
        if (!is_dir($this->baseDir)) return [];
        $items = scandir($this->baseDir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $this->baseDir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                if ($strictMonth) {
                    // Match YYYY_MM folders
                    if (preg_match('/^\d{4}_\d{2}$/', $item)) {
                        $categories[] = $item;
                    }
                } else {
                    // Match folders containing any digit
                    if (preg_match('/\d/', $item)) {
                        $categories[] = $item;
                    }
                }
            }
        }
        rsort($categories); // Show newest months first
        return $categories;
    }

    public function getProjectByName($projectName, $category = null) {
        if ($category) {
            $categories = [$category];
        } else {
            $categories = $this->getCategories();
            $categories[] = ''; // Also search the root directory
        }
        foreach ($categories as $cat) {
            $projects = $this->getProjects($cat);
            foreach ($projects as $p) {
                if ($p['name'] === $projectName) {
                    return $p;
                }
            }
        }
        return null;
    }

    public function getProjects($category = null) {
        if ($category === null) {
            $category = '';
        }
        
        $projects = [];
        $managerName = basename(dirname(__DIR__));
        
        if ($category === '' || $category === 'all') {
            // 1. Scan root directory
            $items = is_dir($this->baseDir) ? scandir($this->baseDir) : [];
            foreach ($items as $item) {
                if ($item == '.' || $item == '..' || $item == 'download') continue;
                $path = $this->baseDir . DIRECTORY_SEPARATOR . $item;
                if (is_dir($path)) {
                    $nameLower = strtolower($item);
                    if (in_array($nameLower, ['.git', '.github', '.idea', '.vscode', 'logs', 'temp_conv', 'backups', 'images', 'source_laravel', 'download', 'vendor', 'node_modules'])) {
                        continue;
                    }
                    if ($nameLower === strtolower($managerName)) {
                        continue;
                    }
                    if (preg_match('/^\d{4}_\d{2}$/', $item)) {
                        continue;
                    }
                    
                    $projects[] = [
                        'name' => $item,
                        'path' => $path,
                        'category' => '',
                        'relPath' => $item,
                        'type' => 'project'
                    ];
                }
            }
            
            // 2. Scan month subdirectories
            $categories = $this->getCategories();
            foreach ($categories as $cat) {
                $catDir = $this->baseDir . DIRECTORY_SEPARATOR . $cat;
                if (is_dir($catDir)) {
                    $catItems = scandir($catDir);
                    foreach ($catItems as $item) {
                        if ($item == '.' || $item == '..' || $item == 'download') continue;
                        $path = $catDir . DIRECTORY_SEPARATOR . $item;
                        if (is_dir($path)) {
                            $nameLower = strtolower($item);
                            if (in_array($nameLower, ['.git', '.github', '.idea', '.vscode', 'logs', 'temp_conv', 'backups', 'images', 'source_laravel', 'download', 'vendor', 'node_modules'])) {
                                continue;
                            }
                            
                            $projects[] = [
                                'name' => $item,
                                'path' => $path,
                                'category' => $cat,
                                'relPath' => $cat . '/' . $item,
                                'type' => 'project'
                            ];
                        }
                    }
                }
            }
        } else {
            // Scan specific category (month subdirectory)
            $dir = $this->baseDir . DIRECTORY_SEPARATOR . $category;
            if (is_dir($dir)) {
                $items = scandir($dir);
                foreach ($items as $item) {
                    if ($item == '.' || $item == '..' || $item == 'download') continue;
                    $path = $dir . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($path)) {
                        $nameLower = strtolower($item);
                        if (in_array($nameLower, ['.git', '.github', '.idea', '.vscode', 'logs', 'temp_conv', 'backups', 'images', 'source_laravel', 'download', 'vendor', 'node_modules'])) {
                            continue;
                        }
                        if ($nameLower === strtolower($managerName)) {
                            continue;
                        }
                        
                        $projects[] = [
                            'name' => $item,
                            'path' => $path,
                            'category' => $category,
                            'relPath' => $category . '/' . $item,
                            'type' => 'project'
                        ];
                    }
                }
            }
        }
        
        return $projects;
    }
}
