<?php

namespace Pg\Libraries;

use Gaufrette\Filesystem as GaufretteFilesystem;
use Gaufrette\Adapter as GaufretteAdapter;

class Filesystem
{

    private $filesystem;

    public function __construct(GaufretteAdapter $adapter)
    {
        $this->filesystem = new GaufretteFilesystem($adapter);
    }

    /**
     * Indicates whether the file matching the specified key exists
     *
     * @param string $key
     *
     * @return boolean TRUE if the file exists, FALSE otherwise
     */
    public function has($key)
    {
        return $this->filesystem->exists($key);
    }
    
    /**
     * Renames a file
     *
     * @param string $sourceKey
     * @param string $targetKey
     *
     * @return boolean                  TRUE if the rename was successful
     * @throws Exception\FileNotFound   when sourceKey does not exist
     * @throws Exception\UnexpectedFile when targetKey exists
     * @throws \RuntimeException        when cannot rename
     */
    public function rename($sourceKey, $targetKey)
    {
        return $this->filesystem->rename($sourceKey, $targetKey);
    }

    /**
     * Returns the file matching the specified key
     *
     * @param string  $key    Key of the file
     * @param boolean $create Whether to create the file if it does not exist
     *
     * @throws Exception\FileNotFound
     * @return File
     */
    public function get($key, $create = false)
    {
        return $this->filesystem->get($key, $create);
    }

    /**
     * Writes the given content into the file
     *
     * @param string  $key                 Key of the file
     * @param string  $content             Content to write in the file
     * @param boolean $overwrite           Whether to overwrite the file if exists
     * @throws Exception\FileAlreadyExists When file already exists and overwrite is false
     * @throws \RuntimeException           When for any reason content could not be written
     *
     * @return integer The number of bytes that were written into the file
     */
    public function write($key, $content, $overwrite = false)
    {
        return $this->filesystem->write($key, $content, $overwrite);
    }

    /**
     * Reads the content from the file
     *
     * @param  string                 $key Key of the file
     * @throws Exception\FileNotFound when file does not exist
     * @throws \RuntimeException      when cannot read file
     *
     * @return string
     */
    public function read($key)
    {
        return $this->filesystem->read($key);
    }

    /**
     * Deletes the file matching the specified key
     *
     * @param string $key
     * @throws \RuntimeException when cannot read file
     *
     * @return boolean
     */
    public function delete($key)
    {
        return $this->filesystem->delete($key);
    }

    /**
     * Returns an array of all keys
     *
     * @return array
     */
    public function keys()
    {
        return $this->filesystem->keys();
    }

    /**
     * Lists keys beginning with given prefix
     * (no wildcard / regex matching)
     *
     * if adapter implements ListKeysAware interface, adapter's implementation will be used,
     * in not, ALL keys will be requested and iterated through.
     *
     * @param  string $prefix
     * @return array
     */
    public function listKeys($prefix = '')
    {
        return $this->filesystem->listKeys($prefix);
    }

    /**
     * Returns the last modified time of the specified file
     *
     * @param string $key
     *
     * @return integer An UNIX like timestamp
     */
    public function mtime($key)
    {
        return $this->filesystem->mtime($key);
    }

    /**
     * Returns the checksum of the specified file's content
     *
     * @param string $key
     *
     * @return string A MD5 hash
     */
    public function checksum($key)
    {
        return $this->filesystem->checksum($key);
    }

    /**
     * Returns the size of the specified file's content
     *
     * @param string $key
     *
     * @return integer File size in Bytes
     */
    public function size($key)
    {
        return $this->filesystem->checksum($key);
    }

    public function createStream($key)
    {
        return $this->filesystem->createStream($key);
    }

    /**
     * Creates a new file in a filesystem.
     *
     * @param $key
     * @return File
     */
    public function createFile($key)
    {
        return $this->filesystem->createFile($key);
    }

    /**
     * Get the mime type of the provided key
     *
     * @param string $key
     *
     * @return string
     */
    public function mimeType($key)
    {
        return $this->filesystem->mimeType($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isDirectory($key)
    {
        return $this->filesystem->isDirectory($key);
    }

    public function rmdir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $full_name = "$dir/$file";
            (is_dir($full_name)) ? $this->rmdir($full_name) : $this->delete($full_name);
        }

        return rmdir($dir);
    }

}
