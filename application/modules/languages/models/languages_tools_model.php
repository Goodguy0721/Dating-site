<?php

namespace Pg\Modules\Languages\Models;

class Languages_tools_model extends \Model
{

    /**
     * Link to CodeIgniter
     *
     * @var object
     */
    private $ci;
    private $lang_file_ext = '.php';

    /**
     * Constructor
     *
     * @return Languages_model
     */
    public function __construct()
    {
        parent::__construct();
        $this->ci = &get_instance();
    }

    /**
     * 1) Fills empty strings with values from the default lang
     * 2) Sorts the result
     * 3) Saves it to the same file
     * 
     * @param string $default_lang_code
     */
    public function fillSort($default_lang_code)
    {
        $dir_end = DIRECTORY_SEPARATOR . 'langs' . DIRECTORY_SEPARATOR;
        foreach (new \DirectoryIterator(MODULEPATH) as $module_dir) {
            if ($module_dir->isDot() || !$module_dir->isDir()) {
                continue;
            }
            $langs_dir = $module_dir->getRealPath() . $dir_end;
            $default_lang_files = $this->getLangContent($langs_dir . $default_lang_code . DIRECTORY_SEPARATOR);
            if (false === $default_lang_files) {
                //TODO: error
                continue;
            }
            
            // Langs inside a module
            foreach (new \DirectoryIterator($langs_dir) as $lang_dir) {
                if ($lang_dir->isDot() || !$lang_dir->isDir() || $lang_dir->getBasename() === $default_lang_code) {
                    continue;
                }
                $lang_files = $this->getLangContent($langs_dir . $lang_dir->getBasename() . DIRECTORY_SEPARATOR);
                $filled_lang_files = $this->fillEmpty($lang_files, $default_lang_files);
                $sorted_lang_files = $this->sort($filled_lang_files);
                $this->rewriteFiles($lang_dir, $sorted_lang_files);
            }
        }
    }

    /**
     * Fill empty $lang_files strings with the $default_files values
     * @param array $lang_files
     * @param array $default_files
     * @return array|false
     */
    private function fillEmpty(array $lang_files, array $default_files)
    {
        if (false === $lang_files) {
            //TODO: error
            return false;
        }
        foreach ($default_files as $default_file => $default_content) {
            foreach ($default_content as $gid => $string) {
                if (!isset($lang_files[$default_file][$gid])) {
                    $lang_files[$default_file][$gid] = $string;
                }
            }
        }
        return $lang_files;
    }

    private function sort(array $langs_arr)
    {
        foreach ($langs_arr as &$lang_arr) {
            natsort($lang_arr);
            ksort($lang_arr);
        }
        return $langs_arr;
    }

    /**
     * Save new data into language files
     * @param \SplFileInfo $lang_dir
     * @param array $lang_files
     */
    private function rewriteFiles(\SplFileInfo $lang_dir, array $lang_files)
    {
        foreach ($lang_files as $file => $content) {
            if (!is_array($content)) {
                //TODO: error
                continue;
            }
            if (!file_exists($lang_dir->getRealPath())) {
                mkdir($lang_dir->getRealPath());
            }
            $file_path = $lang_dir->getRealPath() . DIRECTORY_SEPARATOR . $file . $this->lang_file_ext;
            $h = fopen($file_path, 'w');
            $prepared_content = $this->prepareFileContent($content, $lang_dir->getBasename());
            fwrite($h, $prepared_content);
            fclose($h);
        }
    }

    /**
     * Converts data into the ready for the record string.
     * 
     * @param array $data
     * @return string
     */
    private function prepareFileContent(array $data)
    {
        $html = "<?php\n\n";
        if (!is_array(current($data))) {
            $html .= $this->ci->pg_language->pages->generate_install_lang($data);
        } else {
            $html .= $this->ci->pg_language->ds->generate_install_lang($data);
        }
        return $html;
    }

    /**
     * Generates language files array.
     * 
     * @param string $lang_path
     * @return boolean|array
     */
    public function getLangContent($lang_path)
    {
        if (!is_dir($lang_path)) {
            return false;
        }
        $strings = array();
        foreach (new \DirectoryIterator($lang_path) as $lang_file) {
            if ($lang_file->isDot()) {
                continue;
            }
            $install_lang = array();
            include($lang_file->getRealPath());
            $strings[$lang_file->getBasename($this->lang_file_ext)] = $install_lang;
        }
        return $strings;
    }

    public function getLangContentFromPagesAndDs($lang_path)
    {
        if (!is_dir($lang_path)) {
            return false;
        }
        $strings = array();
        foreach (new \DirectoryIterator($lang_path) as $lang_file) {
            if ($lang_file->isDot()) {
                continue;
            }
            if ($lang_file->getBasename() != "pages.php" && $lang_file->getBasename() != "ds.php") {
                continue;
            }

            $install_lang = array();
            include($lang_file->getRealPath());
            if (!empty($install_lang)) {
                $strings[$lang_file->getBasename($this->lang_file_ext)] = $install_lang;
            }
        }
        return $strings;
    }

}
