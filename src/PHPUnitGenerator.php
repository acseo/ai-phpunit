<?php

namespace ACSEO\AIPHPUnit;

class PHPUnitGenerator
{
    /**
     * createTestForFunction
     *
     * @param string $function The function to generate the test for
     * 
     * @return string The generated function
     */
    public static function createTestForFunction(string $function): string {
        $key = getenv('OPENAI_KEY');

        $openai = \OpenAI::client($key);

        $prompt = <<<EOF
        You are an helpful assistant that writes PHP Code. Specifically, you only write tests code with PHPUnit. 
        You only produce PHP Code given as a result of the given instruction. 
        When the user give you a full function, you produce a PHPUnit test for this function and adapt the original function name accordingly. Use the original class name if needed. 
        Use another function as data provider (declared as an annotation) to provide tests examples. 
        Do not mock services except Doctrine and entities. 
        The test function name must have the same name as the original function name and begin with test. exampme : function __construct() | function testConstruct(), function add() | function testAdd(), function createOrUpdateUser() | function testCreateOrUpdateUser()
        You can be creative to provide useful examples in dataProvider and in assertions. 
        The output contains only php function, no class declaration. The output does not contains ```php tags
        EOF;

        $response = $openai->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' =>  "Function : " . $function],
            ],
        ]);

        try {
            // Check if the OpenAI API returned an error response
            if (isset($completion['error'])) {
                throw new \RuntimeException($completion['error']);
            }

            return $response['choices'][0]['message']['content'];
        }
        catch (\Throwable $e) {
            throw new \RuntimeException('An error occurred while trying to get the doc block: ' . $e->getMessage());
        }
    }


    /**
     * createTestForClass
     *
     * @param string $class The class to generate the test for
     * 
     * @return string The generated test
     */
    public static function createTestForClass(string $class): string {
        $key = getenv('OPENAI_KEY');

        $openai = \OpenAI::client($key);

        $prompt = <<<EOF
        You are an helpful assistant that writes PHP Code. Specifically, you only write tests code with PHPUnit.
        You only produce PHP Code given as a result of the given instruction. 
        When the user give you a full class, you produce de PHPUnit class and adapt the original namespace and function names accordingly.
        For each test, Use another function as data provider (declared as an annotation) to provide tests examples.
        The test function name must have the same name as the original function name and begin with test. exampme : function __construct() | function testConstruct(), function add() | function testAdd(), function createOrUpdateUser() | function testCreateOrUpdateUser()
        Do not mock services except Doctrine and entities. The test class must inherit from KernelTestCase.
        You can be creative to provide useful examples in dataProvider and in assertions.
        The output does not contains ```php, only executable PHP code begining with <?php
        EOF;

        $response = $openai->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
                ['role' => 'user', 'content' =>  "PHP class: " . $class],
            ],
        ]);

        try {
            // Check if the OpenAI API returned an error response
            if (isset($completion['error'])) {
                throw new \RuntimeException($completion['error']);
            }

            return $response['choices'][0]['message']['content'];
        }
        catch (\Throwable $e) {
            throw new \RuntimeException('An error occurred while trying to get the doc block: ' . $e->getMessage());
        }
    }
}
