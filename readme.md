#  AI PHPUnit

AI PHPUnit is a tool that uses GPT-4 to automatically add missing PHPUnit tests to your PHP code.

## Prerequisites

This package uses the OpenAI API. Before using AI PHPUnit, you will need to have an OpenAI API key set as an environment variable. 

```shell
export OPENAI_KEY=...
```

## Installation

To install AI PHPUnit, run the following command:


```shell
composer global require acseo/ai-phpunit
```

## Usage

To add missing PHPUnit comments to a single file, use the following command:

```shell
aiphpunit file  /path/to/file.php
```

To add missing PHPUnit tests to a directory of files, use the following command. By default it iterates through the current directory for all files, but does not go into subdirectories:

```shell
aiphpunit dir
```


You may set the `--recursive` flag, or `-r` for short for it to go into subdirectories.

If you pass another variable (regardless of the recursive flag) it will treat it as another directory to sweep through instead of the working directory.

```shell
aiphpunit dir -r /somewhere/else
```

### Docker usage

You can use the Docker image acseo/ai-phpunit to use ai-phpunit via docker

```bash
$ docker run -it -e OPENAI_KEY=sk-xxx -v /path/to/your/code:/code acseo/ai-phpunit dir -r /code/src
```
## License

AI PHPUnit is licensed under the AGPL-3.0 license. See LICENSE for more information.

It is inspired by [ai-phpdoc](https://github.com/molbal/ai-phpdoc)
