<?php
/**
 * Auto-Integration Script for AMP NASANI Suite
 * 
 * Variables injected by caller (api.php):
 *   $projectPath  - Absolute path to the target project
 *   $ampSourceDir - Absolute path to data/AMP_NASANI (the source of AMP files)
 *   $jobId        - (optional) job ID for log output
 */

if (!isset($projectPath) || !isset($ampSourceDir)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing $projectPath or $ampSourceDir']);
    exit;
}

$logs = [];

function amp_log($msg) {
    global $logs;
    $logs[] = $msg;
}

function amp_copy_dir($src, $dst) {
    if (!is_dir($src)) return;
    @mkdir($dst, 0777, true);
    $dir = opendir($src);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                amp_copy_dir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Helper: resolve path inside project
function pp($rel) {
    global $projectPath;
    return rtrim($projectPath, '/\\') . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $rel), '/\\');
}

// Helper: resolve path inside AMP source dir
function ap($rel) {
    global $ampSourceDir;
    return rtrim($ampSourceDir, '/\\') . DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $rel), '/\\');
}

// 1. Copy AMP Views and Layouts
amp_log("Copying views...");
if (is_dir(ap('src/Views/amp'))) {
    amp_copy_dir(ap('src/Views/amp'), pp('src/Views/amp'));
    amp_log("-> Copied src/Views/amp");
}
if (file_exists(ap('src/Views/layout/master_amp.blade.php'))) {
    @mkdir(pp('src/Views/layout'), 0777, true);
    copy(ap('src/Views/layout/master_amp.blade.php'), pp('src/Views/layout/master_amp.blade.php'));
    amp_log("-> Copied master_amp.blade.php");
}

// 2. Copy AMP Assets
amp_log("Copying assets...");
if (is_dir(ap('assets/amp'))) {
    amp_copy_dir(ap('assets/amp'), pp('assets/amp'));
    amp_log("-> Copied assets/amp");
}

// 3. Copy/Overwrite Helpers
amp_log("Updating BreadCrumbs helper...");
if (file_exists(ap('src/Helpers/BreadCrumbs.php'))) {
    @mkdir(pp('src/Helpers'), 0777, true);
    copy(ap('src/Helpers/BreadCrumbs.php'), pp('src/Helpers/BreadCrumbs.php'));
    amp_log("-> Copied BreadCrumbs.php");
}

// 4. Update config/app.php
amp_log("Updating config/app.php...");
if (file_exists(pp('config/app.php'))) {
    $appConfig = file_get_contents(pp('config/app.php'));
    if (strpos($appConfig, 'amp_prefix') === false) {
        $appConfig = preg_replace('/return\s*\[/', "return [\n    'amp_prefix' => (env('SITE_PATH') . 'amp'),", $appConfig);
        file_put_contents(pp('config/app.php'), $appConfig);
        amp_log("-> Added 'amp_prefix' to config/app.php");
    } else {
        amp_log("-> 'amp_prefix' already exists in config/app.php");
    }
}

// 5. Update config/view.php
amp_log("Updating config/view.php...");
if (file_exists(pp('config/view.php'))) {
    $viewConfig = file_get_contents(pp('config/view.php'));
    $modified = false;
    if (strpos($viewConfig, 'view_amp') === false) {
        $viewConfig = preg_replace('/return\s*\[/', "return [\n    'view_amp' => base_path('src/Views/amp'),", $viewConfig);
        $modified = true;
    }
    if (strpos($viewConfig, 'composer_amp') === false) {
        $viewConfig = preg_replace('/return\s*\[/', "return [\n    'composer_amp' => \\NASANICORE\\Controllers\\Web\\AllController::class,", $viewConfig);
        $modified = true;
    }
    if ($modified) {
        file_put_contents(pp('config/view.php'), $viewConfig);
        amp_log("-> Injected view_amp and composer_amp to config/view.php");
    } else {
        amp_log("-> config/view.php is already updated");
    }
}

// 6. Update src/Routes/web.php
amp_log("Updating src/Routes/web.php...");
if (file_exists(pp('src/Routes/web.php')) && file_exists(ap('src/Routes/web.php'))) {
    $webRoutes = file_get_contents(pp('src/Routes/web.php'));
    if (strpos($webRoutes, 'amp.slugweb') === false && strpos($webRoutes, 'amp.home') === false) {
        $ampRoutes = file_get_contents(ap('src/Routes/web.php'));
        $ampRoutes = str_replace('<?php', '', $ampRoutes);
        $ampRoutes = trim($ampRoutes);
        $webRoutes = preg_replace('/<\?php\s*/', "<?php\n\n" . $ampRoutes . "\n\n", $webRoutes, 1);
        file_put_contents(pp('src/Routes/web.php'), $webRoutes);
        amp_log("-> Added AMP routes to src/Routes/web.php");
    } else {
        amp_log("-> AMP routes already present in src/Routes/web.php");
    }
}

// 7. Inject AMP methods into src/Helpers/Func.php
amp_log("Merging AMP methods into src/Helpers/Func.php...");
if (file_exists(pp('src/Helpers/Func.php')) && file_exists(ap('src/Helpers/Func.php'))) {
    $funcContent = file_get_contents(pp('src/Helpers/Func.php'));
    if (strpos($funcContent, 'getCurrentPageURLAMP') === false) {
        $ampFuncContent = file_get_contents(ap('src/Helpers/Func.php'));
        if (preg_match('/class\s+Func\s*(?:extends\s+[^{\s]+)?\s*\{(.*)\}\s*$/s', $ampFuncContent, $matches)) {
            $ampMethods = trim($matches[1]);
            $pos = strrpos($funcContent, '}');
            if ($pos !== false) {
                $funcContent = substr_replace($funcContent, "\n\n    // === AMP INTEGRATED METHODS ===\n" . $ampMethods . "\n", $pos, 0);
                file_put_contents(pp('src/Helpers/Func.php'), $funcContent);
                amp_log("-> Injected AMP methods into src/Helpers/Func.php");
            }
        }
    } else {
        amp_log("-> AMP helper methods already exist in src/Helpers/Func.php");
    }
}

// 8. Update src/Controllers/Web/ApiController.php
amp_log("Updating ApiController.php...");
if (file_exists(pp('src/Controllers/Web/ApiController.php')) && file_exists(ap('src/Controllers/Web/ApiController.php'))) {
    $controllerContent = file_get_contents(pp('src/Controllers/Web/ApiController.php'));

    if (strpos($controllerContent, 'return match ($method)') === false && strpos($controllerContent, 'return match($method)') === false) {
        $controllerContent = str_replace('match ($method)', 'return match ($method)', $controllerContent);
        amp_log("-> Added return statement to index match block in ApiController.php");
    }

    if (strpos($controllerContent, 'is_amp') === false) {
        $backupController = file_get_contents(ap('src/Controllers/Web/ApiController.php'));
        if (preg_match('/public\s+function\s+NewsletterPost\s*\(.*?\)\s*\{(.*?)\n\s*\}\s*(?:public|private|protected|\})/s', $backupController, $matches)) {
            $newNewsletterBody = $matches[1];
            $controllerContent = preg_replace(
                '/public\s+function\s+NewsletterPost\s*\(.*?\)\s*\{(.*?)\n\s*\}/s',
                "public function NewsletterPost(Request \$request) {" . $newNewsletterBody . "\n    }",
                $controllerContent
            );
            file_put_contents(pp('src/Controllers/Web/ApiController.php'), $controllerContent);
            amp_log("-> Integrated AMP-ready NewsletterPost into ApiController.php");
        }
    } else {
        amp_log("-> ApiController.php already updated with AMP logic");
    }
}

// 9. Update head.blade.php files
amp_log("Verifying head.blade.php templates...");
$headFiles = [
    'src/Views/templates/layout/head.blade.php',
    'src/Views/mobile/layout/head.blade.php'
];
foreach ($headFiles as $headFile) {
    $absHead = pp($headFile);
    if (file_exists($absHead)) {
        $headContent = file_get_contents($absHead);
        if (strpos($headContent, 'getCurrentPageURLAMP') === false) {
            $ampLinkCode = "\n@if ((\$com ?? '') == 'trang-chu' || (\$com ?? '') == 'san-pham')\n    <link rel=\"amphtml\" href=\"{{ Func::getCurrentPageURLAMP() }}\" />\n@endif\n";
            if (strpos($headContent, '@canonical') !== false) {
                $headContent = str_replace('@canonical', $ampLinkCode . '@canonical', $headContent);
            } else {
                $headContent = str_replace('</head>', $ampLinkCode . '</head>', $headContent);
            }
            file_put_contents($absHead, $headContent);
            amp_log("-> Added amphtml link tag to $headFile");
        } else {
            amp_log("-> amphtml link tag already present in $headFile");
        }
    }
}

amp_log("AMP NASANI Integration completed successfully!");

return ['status' => 'success', 'logs' => $logs];
