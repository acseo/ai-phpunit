<?php

namespace ACSEO\AIPHPUnit;

class FileWriter
{
    /**
     * 
     * Write a docblock to a given function
     * 
     * @param string $file The file to write the docblock to
     * @param string $body The body of the function
     * @param string $docblock The docblock to write
     * 
     * @return bool True if the docblock was written successfully, false otherwise
     */
    public function writeText(string $filePath, string $code): bool {

        $testFilePath = FileParser::getTestFilenameFromFile($filePath);
        $indentedCode = $this->indentCode(trim($code), '    ');
        $originalContents = file_get_contents($testFilePath);
        $lastBracePosition = strrpos($originalContents, '}');
        $newContents = substr($originalContents, 0, $lastBracePosition) . $indentedCode . substr($originalContents, $lastBracePosition);
        file_put_contents($testFilePath, $newContents);
        return  true;
    }

    /**
     * Indents a DocBlock string with the given whitespace.
     *
     * @param string $docs The DocBlock string to indent.
     * @param string $whitespace The whitespace to use for indentation.
     *
     * @return string The indented DocBlock string.
     */
    private function indentCode(string $code, string $whitespace): string
    {
        $lines = explode(PHP_EOL, $code);
        $modifiedLines = array_map(function($line) use ($whitespace) {
            return $whitespace . $line;
        }, $lines);
        return implode(PHP_EOL, $modifiedLines).PHP_EOL;
    }
}