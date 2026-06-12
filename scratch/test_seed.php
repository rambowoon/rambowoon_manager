<?php
// Mocking the input payload for nguyenthithuyhang_0956726w
$projectName = 'nguyenthithuyhang_0956726w';
$mainKey = 'type-products';
$selectedSubs = [
    'dich-vu' => ['dummy.jpg']
];
$folderKey = 'project_images';
$seedCount = 5;
$seedCatCount = 3;
$useAiText = false;
$aiPromptExtra = '';

require_once dirname(__DIR__) . '/core/ProjectScanner.php';
require_once dirname(__DIR__) . '/core/AutoMediaPipeline.php';

use RamboWoon\AutoMediaPipeline;
use RamboWoon\ProjectScanner;

try {
    $baseDir = dirname(dirname(__DIR__));
    $scanner = new ProjectScanner($baseDir);
    $project = $scanner->getProjectByName($projectName);
    if (!$project) throw new Exception("Không tìm thấy dự án: $projectName");

    $laravelBase = AutoMediaPipeline::normalizePath($project['path']);
    $mainCfg = AutoMediaPipeline::resolveMainConfig($project['path'], $mainKey);
    if (!$mainCfg) throw new Exception("Không đọc được config/{$mainKey}.php");

    $pdo = AutoMediaPipeline::createPdoFromLaravelEnv($laravelBase);
    
    // Config file raw
    $configFilePath = AutoMediaPipeline::normalizePath($project['path'] . '/config/' . $mainKey . '.php');
    $rawConfig = is_file($configFilePath) ? (require $configFilePath) : [];

    $makeSlug = function($text) {
        if ($text != '') {
            $utf8 = array(
                'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
                'd' => 'đ|Đ',
                'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
                'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',
                'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
                'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
                'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',
                '' => '`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\“|\”|\:|\;|_',
            );
            foreach ($utf8 as $ascii => $uni) {
                $text = preg_replace("/($uni)/i", $ascii, $text);
            }
            $text = strtolower($text);
            $text = preg_replace("/[^\\p{L}0-9-\s]/u", "", $text);
            $text = preg_replace('/([\s]+)/', '-', $text);
            $text = str_replace(array('%20', ' '), '-', $text);
            $text = preg_replace("/-+/", "-", $text);
            $text = trim($text, '-');
            $text = trim($text);
        }
        return $text;
    };

    $pdo->beginTransaction();
    
    foreach ($selectedSubs as $subKey => $imageFiles) {
        $subCfg = $mainCfg['sub_types'][$subKey] ?? null;
        if (!$subCfg) {
            echo "Skipping $subKey - no sub config\n";
            continue;
        }

        $raw        = $rawConfig[$subKey] ?? [];
        $table      = $subCfg['table'];
        $typeCol    = $subCfg['type_column'];
        $typeVal    = $subCfg['type_value'];

        echo "SubKey: $subKey, Table: $table, TypeCol: $typeCol, TypeVal: $typeVal\n";

        $categoryLevels = ['list', 'cat', 'item', 'sub'];
        $categoriesConfig = $raw['categories'] ?? [];
        $generated = ['list' => [], 'cat' => [], 'item' => [], 'sub' => []];

        foreach ($categoryLevels as $lvlIdx => $level) {
            if (!isset($categoriesConfig[$level])) {
                echo "  Level $level is not set in config\n";
                continue;
            }
            $catTable = $table . '_' . $level;
            $stCheck = $pdo->query("SHOW TABLES LIKE '$catTable'");
            if ($stCheck->rowCount() === 0) {
                echo "  Table $catTable does not exist\n";
                continue;
            }

            echo "  Level: $level, CatTable: $catTable\n";

            $catColumns = array_column($pdo->query("SHOW COLUMNS FROM `$catTable`")->fetchAll(PDO::FETCH_ASSOC), 'Field');

            $stExist = $pdo->prepare("SELECT id FROM `$catTable` WHERE `$typeCol` = ?");
            $stExist->execute([$typeVal]);
            $existingIds = $stExist->fetchAll(PDO::FETCH_COLUMN);
            echo "    Existing IDs count: " . count($existingIds) . "\n";

            // Mock table_slug check
            $stSlugCheckTable = $pdo->query("SHOW TABLES LIKE 'table_slug'");
            $hasSlugTable = ($stSlugCheckTable->rowCount() > 0);
            echo "    Has table_slug: " . ($hasSlugTable ? 'Yes' : 'No') . "\n";

            if ($hasSlugTable && in_array('slugvi', $catColumns)) {
                $nameCol = in_array('namevi', $catColumns) ? 'namevi' : (in_array('name', $catColumns) ? 'name' : '');
                $slugCol = 'slugvi';

                if ($nameCol && !empty($existingIds)) {
                    // Try to execute the exact SQL we added
                    $queryStr = "SELECT id, `$nameCol` as name_val, `$slugCol` as slug_val FROM `$catTable` WHERE id IN (" . implode(',', array_map('intval', $existingIds)) . ")";
                    echo "    Querying existing slugs: $queryStr\n";
                    $stExistRows = $pdo->prepare($queryStr);
                    $stExistRows->execute();
                    $existRows = $stExistRows->fetchAll(PDO::FETCH_ASSOC);
                    echo "    Found existing rows: " . count($existRows) . "\n";
                }
            }

            if (count($existingIds) >= $seedCatCount) {
                echo "    Enough existing categories, mapping them\n";
                $generated[$level] = $existingIds;
            } else {
                $newIds = [];
                $parentLevel = $lvlIdx > 0 ? $categoryLevels[$lvlIdx - 1] : null;
                $parents = $parentLevel ? $generated[$parentLevel] : [0];
                $needed = $seedCatCount - count($existingIds);

                echo "    Generating $needed new categories. Parents count: " . count($parents) . "\n";

                for ($cIdx = 0; $cIdx < $needed; $cIdx++) {
                    $parentVal = !empty($parents) ? $parents[$cIdx % count($parents)] : 0;
                    $nameVal = ($raw['title_main'] ?? $subKey) . ' Danh Mục ' . ucfirst($level) . ' ' . (count($existingIds) + $cIdx + 1);
                    $slugVal = $makeSlug($nameVal);

                    $cols = [$typeCol];
                    $vals = [$typeVal];

                    if (in_array('namevi', $catColumns)) { $cols[] = 'namevi'; $vals[] = $nameVal; }
                    if (in_array('nameen', $catColumns)) { $cols[] = 'nameen'; $vals[] = $nameVal; }
                    if (in_array('name', $catColumns))   { $cols[] = 'name';   $vals[] = $nameVal; }

                    if (in_array('slugvi', $catColumns)) { $cols[] = 'slugvi'; $vals[] = $slugVal; }
                    if (in_array('slugen', $catColumns)) { $cols[] = 'slugen'; $vals[] = $slugVal; }

                    if (in_array('status', $catColumns)) { $cols[] = 'status'; $vals[] = 'hienthi'; }
                    if (in_array('numb', $catColumns)) { $cols[] = 'numb'; $vals[] = $cIdx + 1; }
                    if (in_array('date_created', $catColumns)) { $cols[] = 'date_created'; $vals[] = time(); }
                    if (in_array('created_at', $catColumns)) { $cols[] = 'created_at'; $vals[] = date('Y-m-d H:i:s'); }
                    if (in_array('updated_at', $catColumns)) { $cols[] = 'updated_at'; $vals[] = date('Y-m-d H:i:s'); }

                    // Gán quan hệ parent
                    if ($level === 'cat') {
                        if (in_array('id_list', $catColumns)) { $cols[] = 'id_list'; $vals[] = $parentVal; }
                    } elseif ($level === 'item') {
                        if (in_array('id_cat', $catColumns)) { $cols[] = 'id_cat'; $vals[] = $parentVal; }
                        if (in_array('id_list', $catColumns) && $parentVal) {
                            $stParent = $pdo->prepare("SELECT id_list FROM `{$table}_cat` WHERE id = ?");
                            $stParent->execute([$parentVal]);
                            $cols[] = 'id_list'; $vals[] = (int)$stParent->fetchColumn();
                        }
                    } elseif ($level === 'sub') {
                        if (in_array('id_item', $catColumns)) { $cols[] = 'id_item'; $vals[] = $parentVal; }
                        if ($parentVal) {
                            $stParent = $pdo->prepare("SELECT id_list, id_cat FROM `{$table}_item` WHERE id = ?");
                            $stParent->execute([$parentVal]);
                            $pInfo = $stParent->fetch();
                            if (in_array('id_list', $catColumns)) { $cols[] = 'id_list'; $vals[] = (int)($pInfo['id_list'] ?? 0); }
                            if (in_array('id_cat', $catColumns)) { $cols[] = 'id_cat'; $vals[] = (int)($pInfo['id_cat'] ?? 0); }
                        }
                    }

                    $ph = array_fill(0, count($cols), '?');
                    $insertSql = "INSERT INTO `$catTable` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $ph) . ")";
                    echo "      Insert SQL: $insertSql\n";
                    $st = $pdo->prepare($insertSql);
                    $st->execute($vals);
                    $newId = $pdo->lastInsertId();
                    echo "      Inserted ID: $newId\n";
                    $newIds[] = $newId;
                }
                $generated[$level] = array_merge($existingIds, $newIds);
            }
        }
    }

    $pdo->rollBack();
    echo "Done testing, transaction rolled back safely.\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "TRACE:\n" . $e->getTraceAsString() . "\n";
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
}
