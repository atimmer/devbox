<?php
namespace Devbot\Metadata;

class Metadata
{
    const DEFAULT_FILE_NAME = 'Botfile';
    
    /**
     * Free-form extra info
     * @var array
     */
    protected $extra = [];
    
    public function getExtra()
    {
        return $this->extra;
    }
    
    public function setExtra(array $extra)
    {
        $this->extra = $extra;
        return $this;
    }
    
    public function addExtra(array $extra)
    {
        return $this->setExtra(array_merge_recursive($this->getExtra(), $extra));
    }
    
    public function writeToFile($path)
    {
        $data = $this->jsonData();
        self::writeRawArrayToFile($path, $data);
    }
    
    public function writeToDirectory($directory)
    {
        $path = self::metadataPathForDirectory($$directory);
        return $this->writeToFile($path);
    }
    
    public static function readFromFile($path)
    {
        $data = self::readRawArrayFromFile($path);
        return self::objectFromJSONData($data);
    }
    
    public static function readFromDirectory($directory)
    {
        $path = self::metadataPathForDirectory($$directory);
        return self::readFromFile($path);
    }
    
    public static function metadataPathForDirectory($path)
    {
        return $path . DIRECTORY_SEPARATOR . DEFAULT_FILE_NAME;
    }
    
    private function jsonData()
    {
        $data = [];
        
        $data['extra'] = $this->extra;
        
        return $data;
    }
    
    private static function writeRawArrayToFile($path, $data)
    {
        $bytes = json_encode($data);
        file_put_contents($path, $bytes);
    }
    
    private static function readRawArrayFromFile($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Path {$path} does not exist");
        }
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("File {$path} is not readable");
        }
        $jsonData = file_get_contents($path);
        $data = json_decode($jsonData, true);
        if ($data === null) {
            throw new \UnexpectedValueException("Found invalid JSON data in {$path}: " . json_last_error_msg());
        }
        return $data;
    }
    
    private static function objectFromJSONData($data)
    {
        $obj = new self;
        
        if (isset ($data['extra'])) {
            if (!is_array($data['extra'])) {
                throw new \UnexpectedValueException("Metadata 'extra' key should contain an array or an object");
            }
            $obj->extra = $data['extra'];
        }
        
        return $obj;
    }
}