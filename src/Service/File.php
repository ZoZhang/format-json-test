<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Finder\Finder;

class File
{
    /**
     * current finder
     */
    protected $finder;

    /**
     * constructor service
     */
    public function __construct()
    {
        $this->finder = Finder::create();
    }

    /**
     * search an file by a condition path and file name
     * @param string $in
     * @param string $name
     * @return $this
     * @throws \Exception
     */
    public function getFile(string $in, string $name)
    {
        // find the file by a condition search
        $finder = $this->finder->in($in)->name($name);

        // displays an exception message in console when nothing finds
        if (!$finder->hasResults()) {
            throw new \RuntimeException('The ' . $in . '/' . $name . ' file does not exist.');
        }

        $this->finder = $finder;

        return $this;
    }

    /**
     * get the content of current file load
     * @return array|mixed|null
     */
    public function getContent()
    {
        $content = [];

        // return empty content when no current file found
        if (!($countFile = $this->finder->count())) {
            return $content;
        }

        // save the contents of the current file
        foreach ($this->finder->files() as $file) {
            $content[] = $file->getContents();
        }

        // return direct the content where current is only one file
        if (1 === $countFile) {
            $content = array_shift($content);
        }

        return $content;
    }

}