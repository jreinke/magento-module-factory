<?php

class JR_ModuleFactory_Helper_Data extends Mage_Core_Helper_Abstract
{
    const VAR_FOLDER = 'module_factory';

    public function getTemplateDir()
    {
        return Mage::getModuleDir('community', $this->_getModuleName()) . DS . 'templates' . DS;
    }

    public function getTemplateFile($file)
    {
        return $this->getTemplateDir() . $file;
    }

    public function getVarDir()
    {
        return Mage::getConfig()->getVarDir(self::VAR_FOLDER);
    }

    public function getTmpModuleDir($moduleName)
    {
        return $this->getVarDir() . DS . $moduleName . DS;
    }

    public function moduleExists($moduleName)
    {
        return (bool) Mage::getConfig()->getNode('modules/' . $moduleName);
    }

    public function createModule($data)
    {
        $namespace = $this->_cleanString($data['namespace']);
        $module = $this->_cleanString($data['module']);
        $pool = $data['pool'];
        $version = $this->_cleanString($data['version']);
        $dependencies = array();
        if (isset($data['depends'])) {
            foreach ($data['depends'] as $dependency) {
                $dependencies[] = sprintf('%s<%s />', str_repeat(' ', 4 * 4), $dependency);
            }
        }
        $replacements = array(
            '{{Namespace}}' => $namespace,
            '{{namespace}}' => strtolower($namespace),
            '{{Module}}'    => $module,
            '{{module}}'    => strtolower($module),
            '{{pool}}'      => $pool,
            '{{version}}'   => $version,
            '{{depends}}'   => implode(PHP_EOL, $dependencies),
        );

        $io = new Varien_Io_File();
        $tplDir = $this->getTemplateDir();
        $tmpDir = $this->getTmpModuleDir($namespace . '_' . $module);

        $io->checkAndCreateFolder($tmpDir);

        if (!$io->isWriteable($tmpDir)) {
            Mage::throwException('Module temp dir is not writeable');
        }

        @shell_exec("cp -r $tplDir $tmpDir");
        $files = $this->_getTemplateFiles($tmpDir);

        if (empty($files)) {
            Mage::throwException('Could not copy templates files to module temp dir');
        }

        $this->_replaceVars($tmpDir, $replacements);

        $dest = Mage::getBaseDir();
        if (!$io->isWriteable($dest)) {
            Mage::throwException(sprintf(
                'Could not move module files to Magento tree. However, module structure is available in %s',
                $tmpDir
            ));
        }
        @shell_exec("cp -r $tmpDir $dest");

        return true;
    }

    public function validate($data)
    {
        $errors = array();

        if (!isset($data['namespace']) || empty($data['namespace'])) {
            $errors[] = $this->__('Namespace is required');
        }

        if (!isset($data['module']) || empty($data['module'])) {
            $errors[] = $this->__('Module is required');
        }

        if ($this->moduleExists($data['namespace'] . '_' . $data['module'])) {
            $errors[] = $this->__('A module with the same name already exists');
        }

        if (!isset($data['pool']) || empty($data['pool'])) {
            $errors[] = $this->__('Code pool is required');
        }

        return empty($errors) ? true : $errors;
    }

    protected function _cleanString($string)
    {
        return Mage::helper('core/string')->cleanString($string);
    }

    protected function _replaceVars($dir, $replacements)
    {
        $files = glob($dir . DS . '*', GLOB_MARK | GLOB_NOSORT);
        if (!empty($files)) {
            foreach ($files as $file) {
                $new = strtr($file, $replacements);
                @rename($file, $new);
                if (substr($new, -1) != DS) {
                    // It's a file
                    $content = strtr(file_get_contents($new), $replacements);
                    file_put_contents($new, $content);
                } else {
                    $this->_replaceVars($new, $replacements);
                }
            }
        }

        return true;
    }

    protected function _getTemplateFiles($dir)
    {
        $files = array();
        $items = glob($dir . '*', GLOB_MARK | GLOB_NOSORT);

        foreach ($items as $item) {
            if (substr($item, -1) != DS) {
                $files[] = $item;
            } else {
                $files = array_merge($files, $this->_getTemplateFiles($item));
            }
        }

        return $files;
    }
}