<?php

namespace RamboWoon;

class ProjectDeployer
{
    private $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Copy a directory recursively - Optimized for Windows
     */
    public function copyRecursive($src, $dst)
    {
        if (!is_dir($src)) return false;
        if (!is_dir($dst)) @mkdir($dst, 0777, true);

        // Use native Windows xcopy for much faster speed than PHP loops
        // /E: Copy subdirectories, including empty ones.
        // /I: If destination does not exist and copying more than one file, assumes that destination must be a directory.
        // /H: Copy hidden and system files also.
        // /Y: Suppress prompting to confirm you want to overwrite an existing destination file.
        $srcPath = str_replace('/', DIRECTORY_SEPARATOR, $src);
        $dstPath = str_replace('/', DIRECTORY_SEPARATOR, $dst);
        
        $cmd = "xcopy \"$srcPath\" \"$dstPath\" /E /I /H /Y 2>&1";
        exec($cmd, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception("Lỗi xcopy (Code $returnVar): " . implode("\n", $output));
        }
        
        return true;
    }

    /**
     * Extract a ZIP file - Highly recommended for speed
     */
    public function extractZip($zipPath, $dst)
    {
        if (!file_exists($zipPath)) return false;
        if (!is_dir($dst)) @mkdir($dst, 0777, true);

        $zipPath = str_replace('/', DIRECTORY_SEPARATOR, $zipPath);
        $dstPath = str_replace('/', DIRECTORY_SEPARATOR, $dst);

        // Windows 10+ has tar command built-in that handles .zip
        $cmd = "tar -xf \"$zipPath\" -C \"$dstPath\" 2>&1";
        exec($cmd, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception("Lỗi giải nén (Code $returnVar): " . implode("\n", $output));
        }

        return true;
    }

    /**
     * Create a new database on localhost using mysqli
     */
    public function createDatabase($dbName, $host = 'localhost', $user = 'root', $pass = '')
    {
        $mysqli = new \mysqli($host, $user, $pass);
        if ($mysqli->connect_error) {
            throw new \Exception("Kết nối MySQL thất bại: " . $mysqli->connect_error);
        }

        $dbSafeName = $mysqli->real_escape_string($dbName);
        $sql = "CREATE DATABASE IF NOT EXISTS `$dbSafeName`";
        
        if (!$mysqli->query($sql)) {
            $error = $mysqli->error;
            $mysqli->close();
            throw new \Exception("Lỗi tạo database: " . $error);
        }

        $mysqli->close();
        return true;
    }

    /**
     * Import SQL file to database - Fast Multi-Query Mode
     */
    public function importSql($dbName, $sqlFile, $host = 'localhost', $user = 'root', $pass = '')
    {
        if (!file_exists($sqlFile)) {
            throw new \Exception("File SQL không tồn tại: $sqlFile");
        }

        $mysqli = new \mysqli($host, $user, $pass, $dbName);
        if ($mysqli->connect_error) {
            throw new \Exception("Kết nối MySQL thất bại: " . $mysqli->connect_error);
        }
        $mysqli->set_charset("utf8mb4");

        // 1. Read and Sanitize SQL (Remove system commands for stability)
        $originalSql = file_get_contents($sqlFile);
        $sanitizedSql = preg_replace('/^SET .*;$/mi', '-- Removed SET', $originalSql);
        $sanitizedSql = preg_replace('/^START TRANSACTION.*;/mi', '-- Removed START', $sanitizedSql);
        $sanitizedSql = preg_replace('/^COMMIT.*;/mi', '-- Removed COMMIT', $sanitizedSql);
        $sanitizedSql = preg_replace('/^CREATE DATABASE.*;/mi', '-- Removed CREATE', $sanitizedSql);
        $sanitizedSql = preg_replace('/^USE .*;$/mi', '-- Removed USE', $sanitizedSql);
        $sanitizedSql = preg_replace('/^DROP DATABASE.*;/mi', '-- Removed DROP DB', $sanitizedSql);

        // 2. Execute Multi-Query (Fastest)
        $sanitizedSql = "SET FOREIGN_KEY_CHECKS=0;\n" . $sanitizedSql . "\nSET FOREIGN_KEY_CHECKS=1;";
        if ($mysqli->multi_query($sanitizedSql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }

        if ($mysqli->error) {
            $error = $mysqli->error;
            $mysqli->close();
            throw new \Exception("Lỗi import SQL: " . $error);
        }

        $mysqli->close();
        return true;
    }

    /**
     * Update .env file
     */
    public function updateEnv($envPath, $updates)
    {
        if (!file_exists($envPath)) return false;

        $content = file_get_contents($envPath);
        foreach ($updates as $key => $value) {
            // Match KEY=VALUE or KEY="VALUE"
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        return file_put_contents($envPath, $content) !== false;
    }
}
