<?php

class PodcastGuidFile implements ArrayAccess
{
    protected $dataChanged = false;
    protected $filePath = null;
    protected $guids = [];

    public function __destruct()
    {
        if ($this->dataChanged) {
            $this->writeGuidFile($this->guids);
        }
    }

    public static function createFromFile($filePath)
    {
        try {
            PodcastGuidFile::validateGuidFile($filePath);
        } catch (Exception $e) {
            return false;
        }

        $guidFile = new PodcastGuidFile();
        $guidFile->filePath = $filePath;

        if (file_exists($filePath)) {
            $guidFileData = file($filePath);

            if ($guidFileData !== false) {
                foreach ($guidFileData as $guidFileLine) {
                    $guidLineData = explode('~~~', trim($guidFileLine));
                    $guidFile->guids[$guidLineData[0]] = $guidLineData[1];
                }
            }
        }

        return $guidFile;
    }

    public function getGuidForId($id)
    {
        if (!$this->offsetExists($id)) {
            $this->offsetSet($id, \sweelix\guid\Guid::v4());
            $this->dataChanged = true;
        }

        return $this->offsetGet($id);
    }

    public function offsetExists($offset)
    {

        return is_array($this->guids) ? array_key_exists($offset, $this->guids) : false;
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->guids[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->guids[$offset] = $value;
        $this->dataChanged = true;
    }

    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->guids[$offset]);
            $this->dataChanged = true;
        }
    }

    public static function validateGuidFile($guidFile)
    {
        clearstatcache();

        if (file_exists($guidFile)) {
            if (!is_writeable($guidFile)) {
                throw new Exception("Existing file at {$guidFile} is not writable");
            }
        } else {
            $directory = dirname($guidFile);
            if (!is_writeable($directory)) {
                throw new Exception("Directory {$directory} is not writable");
            }
        }

        return true;
    }

    public function writeGuidFile($guids)
    {
        $guidLines = [];

        foreach ($guids as $key => $value) {
            $guidLines[] = "{$key}~~~{$value}" . PHP_EOL;
        }

        file_put_contents($this->filePath, $guidLines);
    }
}
