<?php
namespace RamboWoon;

class ConfigManager {
    private $configPath;

    public function __construct($path) {
        $this->configPath = $path;
    }

    public function getAll() {
        if (!file_exists($this->configPath)) return [];
        return json_decode(file_get_contents($this->configPath), true) ?: [];
    }

    public function save($projectName, $config) {
        $configs = $this->getAll();
        $configs[$projectName] = $config;
        return file_put_contents($this->configPath, json_encode($configs, JSON_PRETTY_PRINT));
    }

    public function delete($projectName) {
        $configs = $this->getAll();
        if (isset($configs[$projectName])) {
            unset($configs[$projectName]);
            return file_put_contents($this->configPath, json_encode($configs, JSON_PRETTY_PRINT));
        }
        return false;
    }

    public function updateDeployedInfo($projectName, $stage, $info) {
        $configs = $this->getAll();
        if (!isset($configs[$projectName])) $configs[$projectName] = [];
        if (!isset($configs[$projectName]['deployed'])) $configs[$projectName]['deployed'] = [];
        
        $configs[$projectName]['deployed'][$stage] = $info;
        return file_put_contents($this->configPath, json_encode($configs, JSON_PRETTY_PRINT));
    }

    public function addHistory($projectName, $action, $message = '') {
        $configs = $this->getAll();
        if (!isset($configs[$projectName])) $configs[$projectName] = [];
        if (!isset($configs[$projectName]['history'])) $configs[$projectName]['history'] = [];
        
        $entry = [
            'action' => $action,
            'message' => $message,
            'time' => date('Y-m-d H:i:s')
        ];
        
        // Push to top (newest first)
        array_unshift($configs[$projectName]['history'], $entry);
        
        // Keep only last 50
        $configs[$projectName]['history'] = array_slice($configs[$projectName]['history'], 0, 50);
        
        return file_put_contents($this->configPath, json_encode($configs, JSON_PRETTY_PRINT));
    }

    public function getForProject($projectName) {
        $configs = $this->getAll();
        return $configs[$projectName] ?? null;
    }
}
