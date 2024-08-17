<?php

namespace ACSEO\AIPHPUnit;

use Exception;

class FileParser
{


    /**
     * Extracts a list of functions from a string containing PHP code.
     *
     * @param string $code The string of PHP code.
     * @return array An array of functions, each of which is an associative array with the following keys:
     *   - name: the name of the function
     * @throws Exception if the file does not exist
     */
    public function getFunctionsFromString(string $code): array {

        $functions = [];
        $matches = [];

        preg_match_all('/^\s*(\/\*\*.*?\*\/)?\s*(?:(?:public|private|protected)\s+)?(?:static\s+)?function\s+(\w+)\s*\(([^)]*)\)\s*(?::\s?(\S+))?/ms', $code, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
        foreach ($matches as $match) {
            $functions[] = [
                'name' => $match[2][0],
                'test' => false,
                'body' => $match[0][0].PHP_EOL.$this->getFunctionBody($code,$match[0][1]),
            ];
        }
        return $functions;
    }

    /**
     * Extracts a list of functions from a PHP file.
     *
     * @param string $filePath The file path of the PHP file.
     * @return array An array of functions, each of which is an associative array with the following keys:
     *   - name: the name of the function
     *   - test: a boolean indicating whether the function has a test or not
     *   - body: the body of the function as a string
     * @throws Exception if the file does not exist
     */
    public function getFunctionsFromFile(string $filePath): array {
        
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
        $code = file_get_contents($filePath);
        $codeFunctions =  self::getFunctionsFromString($code);
        $testFunctions = [];
        $testFilePath = $this->getTestFilenameFromFile($filePath);
        if (!file_exists($testFilePath)) {
            // Extraire le chemin du répertoire sans le nom du fichier
            $directory = dirname($testFilePath);
            // Vérifier si le répertoire n'existe pas, et le créer récursivement
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            $testCode = PHPUnitGenerator::createTestForClass($code);
            file_put_contents($testFilePath, $testCode);
        }
        
        if (file_exists($testFilePath)) {
            $testCode = file_get_contents($testFilePath);
            $testFunctions =  self::getFunctionsFromString($testCode);
        }

        foreach ($codeFunctions as $index => $functionInfo) {
            $functionName = $functionInfo['name'];
            foreach($testFunctions as $testFunctionInfo) {
                $testFunctionName = $testFunctionInfo['name'];
                if ($this->matchFunctionAndTest($functionName, $testFunctionName)) {
                    $codeFunctions[$index]['test'] = $testFunctionInfo;
                    break;
                }
            }
        }
        
        return $codeFunctions;
    }

    public static function getTestFilenameFromFile(string $filePath) {
        $filename =  str_replace("src/", "tests/", $filePath);
        $filename =  str_replace(".php", "Test.php", $filename);

        return $filename;
    }

    public static function matchFunctionAndTest(string $functionName, string $testFunctionName) : bool
    {
        // basic case
        if ('test'.ucfirst($functionName) == $testFunctionName)
        {
            return true;
        }

        // case for __function()
        if ('test'.ucfirst(str_replace('_', '', $functionName)) == $testFunctionName)
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the body of a function from a given string.
     *
     * @param string $str The string containing the function.
     * @param int $startIndex The index of the string to start searching from.
     *
     * @return string The body of the function.
     */
    private function getFunctionBody(string $str, int $startIndex): string {
        $openBraceCount = 0;
        $closeBraceCount = 0;
        $contents = "";

        for ($i=$startIndex; $i<strlen($str); $i++) {
            if ($str[$i] == "{") {
                $openBraceCount++;
            } else if ($str[$i] == "}") {
                $closeBraceCount++;
            }

            if ($openBraceCount > 0) {
                $contents .= $str[$i];
            }

            if ($openBraceCount > 0 && $openBraceCount == $closeBraceCount) {
                break;
            }
        }

        return $contents;
    }

}