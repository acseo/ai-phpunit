<?php

namespace ACSEO\AIPHPUnit;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessFacade
{
    /**
     * Process a file and generate PHPUnit tests for functions without them
     * 
     * @param mixed $filePath The path to the file to process
     * @param OutputInterface $output The output interface to write messages to
     * 
     * @return int The status of the command (success or failure)
     */
    public function processFile(mixed $filePath, OutputInterface $output): int
    {
        $output->writeln('Processing file: '.$filePath);
        try {
            $functions = (new FileParser)->getFunctionsFromFile($filePath);
            $errors = 0;
            $completions = 0;
            foreach ($functions as $function) {
                if (!$function['test']) {
                    $output->writeln('Found function without test: ' . $function['name']);
                    try {
                        $testCode = PHPUnitGenerator::createTestForFunction($function['body']);
                        if ((new FileWriter)->writeText($filePath, $testCode)) {
                            $output->writeln('<info>Wrote phpunit test for function ' . $function['name'] . '</info>');
                            $completions++;
                        } else {
                            $output->writeln('<error>Generated phpunit test for function <' . $function['name'] . '>, but could not write it to the file.</error>');
                            $output->writeln($docs);
                            $errors++;
                        }
                    } catch (Exception $error) {
                        $output->writeln('<error>Could not generate phpunit test for function ' . $function['name'] . ': ' . $error->getMessage() . '</error>');
                    }
                }
            }

            if (empty($functions)) {
                $output->writeln('<comment>🙈 No functions found in the file.</comment>');
            }

            if ($completions > 0) {
                $output->writeln('Finished processing ' . $filePath . ' with ' . $completions . ' PHPUnit tests written and ' . $errors . ' errors.');
            }

            return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    /**
     * Process a directory and its contents.
     *
     * @param string $directoryPath The path of the directory to process
     * @param bool $recursive Whether or not to process subdirectories
     * @param OutputInterface $output The output interface to write messages to
     *
     * @return int The status of the operation (Command::SUCCESS or Command::FAILURE)
     */

    public function processDirectory(string $directoryPath, bool $recursive, OutputInterface $output): int
    {
        $output->writeln('<comment>Processing directory: '.$directoryPath.'</comment>');
        $success = Command::SUCCESS;

        if (!is_dir($directoryPath)) {
            $output->writeln('<error>'.$directoryPath.' is not a valid directory.</error>');
            return Command::INVALID;
        }

        $iterator = new RecursiveDirectoryIterator($directoryPath, FilesystemIterator::SKIP_DOTS);


        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() == 'php') {
                $success = $this->processFile($file->getPathname(), $output) == Command::SUCCESS ? Command::SUCCESS : Command::FAILURE;
            }

            if ($file->isDir() && $recursive ) {
                $this->processDirectory($file->getPathname(), true, $output);
            }
        }

        return $success;
    }

}