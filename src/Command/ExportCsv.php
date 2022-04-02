<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Csv;
use App\Service\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

class ExportCsv extends Command
{
    /**
     * name of the command console
     */
    const COMMAND_NAME = 'app:export-csv';

    /**
     * name of json file
     */
    const JSON_FILE_NAME = 'sample_data_test_CDI.json';

    /**
     * Tips message of console
     * @var string[]
     */
    protected $messages = [
                'failure' => 'The csv file generation failed, please try again ;(',
                'success' => 'The csv file was generated successfully, you can find it here:',
            ];

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var File
     */
    protected $finder;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * constructor export
     * @param Csv $csv
     * @param File $finder
     */
    public function __construct(
        Csv $csv,
        File $finder
    )
    {
        parent::__construct(self::COMMAND_NAME);

        $this->csv = $csv;
        $this->finder = $finder;
    }

    /**
     * get application root path by current directory
     * @param string $file
     * @return string
     */
    public function getRootPath(string $file = '')
    {
        $currentDir = dirname(__DIR__);
        $rootPath = Path::getDirectory($currentDir);

        if ($file) {
            $rootPath .= '/' . $file;
        }

        return $rootPath;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // current output object
            $this->output = $output;

            // display message in console
            $displayMsg = function($preFile, $newFile) {
                $message = $newFile ? $this->messages['success'] : $this->messages['failure'] ;
                if ($newFile) {
                    $this->output->writeln("<info>$message</info>");
                    $this->output->writeln("<info>$newFile</info>");
                } else {
                    $this->output->writeln("<error>$message</error>");
                    $this->output->writeln("<error>$preFile</error>");
                }
                $this->output->writeln('');
            };

            // load json file by a condition search
            $content = $this->finder->getFile($this->getRootPath(), self::JSON_FILE_NAME)->getContent();

            // transform the json content to array
            $content = $this->csv->convert($content);

            // generate a new csf file `teams.csv`
            $preFile = $this->getRootPath('resource/teams.csv');
            $newFile = $this->csv->export($preFile, $content, 1);
            $displayMsg($preFile, $newFile);

            // generate a new csf file `team_members.csv`
            $preFile = $this->getRootPath('resource/team_members.csv');
            $newFile = $this->csv->export($preFile, $content, 2);
            $displayMsg($preFile, $newFile);

        } catch (\Exception $e) {
            $message = $e->getMessage() . PHP_EOL . $e->getTraceAsString();
            throw new \Exception($message);
        }

        return Command::SUCCESS;
    }
}

