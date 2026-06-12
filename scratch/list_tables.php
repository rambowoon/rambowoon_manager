<?php
require_once dirname(__DIR__) . '/core/ProjectScanner.php';
require_once dirname(__DIR__) . '/core/AutoMediaPipeline.php';

use RamboWoon\AutoMediaPipeline;
use RamboWoon\ProjectScanner;

$baseDir = dirname(dirname(__DIR__));
$scanner = new ProjectScanner($baseDir);
$projects = $scanner->getProjects('all');

foreach ($projects as $p) {
    try {
        $laravelBase = AutoMediaPipeline::normalizePath($p['path']);
        if (!is_file($laravelBase . '/.env')) continue;
        
        $pdo = AutoMediaPipeline::createPdoFromLaravelEnv($laravelBase);
        echo "Project: " . $p['name'] . "\n";
        
        $st = $pdo->query("SELECT * FROM `table_slug` LIMIT 20");
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            echo "  - Slug: {$row['slugvi']}, Com: {$row['com']}, Act: {$row['act']}, ParentID: {$row['id_parent']}, Type: {$row['type']}\n";
        }
        break; // Only check one
    } catch (\Throwable $e) {
        echo "Project " . $p['name'] . " failed: " . $e->getMessage() . "\n";
    }
}
