<?php

declare(strict_types=1);

class Parser
{
    private mixed $_assemblyFile;
    private string|bool $_currentCommand;

    public const NOT_VALID_COMMAND = 0;
    public const A_COMMAND         = 1;
    public const C_COMMAND         = 2;
    public const L_COMMAND         = 3;

    private const C_COMMAND_PATTERN_GROUP = '/^(?:(?<dest>[^=]*)=)?(?<comp>[^;]*)(?:;(?<jump>.*))?$/';

    public function __construct(string $assemblyFilePath)
    {
        $this->_assemblyFile = fopen($assemblyFilePath, 'r');
    }

    public function advance(): bool
    {
        if(is_resource($this->_assemblyFile) === false) {
            return false;
        }

        $this->_currentCommand = fgets($this->_assemblyFile);
        if($this->_currentCommand !== false) {
            $this->_currentCommand = str_replace(" ", "", $this->_currentCommand);
            $this->_currentCommand = substr($this->_currentCommand, 0, strlen($this->_currentCommand) - 2);
        }

        if($this->_currentCommand === false) {
            fclose($this->_assemblyFile);
        }

        return $this->_currentCommand !== false;
    }

    public function getCurrentCommand(): string
    {
        return $this->_currentCommand;
    }

    public function symbol(): string
    {
        $commandType = $this->commandType();
        if($commandType === self::A_COMMAND) {
            //@XxxのXxxを取り出す
            return substr($this->_currentCommand, 1);
        }

        if($commandType === self::L_COMMAND) {
            //(Xxx)のXxxを取り出す
            return substr($this->_currentCommand, 1, strlen($this->_currentCommand) - 2);
        }

        throw new Exception("Cunrrent command is not A_COMMAND or L_COMMAND");
        return "";
    }

    public function dest(): string
    {
        if($this->commandType() !== self::C_COMMAND) {
            throw new Exception("Current command is no C_COMMAND");
            return "";
        }

        if (preg_match(self::C_COMMAND_PATTERN_GROUP, $this->_currentCommand, $matches)) {
            return  $matches['dest'] ?? "";
        }
    }

    public function comp(): string
    {
        if($this->commandType() !== self::C_COMMAND) {
            throw new Exception("Current command is no C_COMMAND");
            return "";
        }

        if (preg_match(self::C_COMMAND_PATTERN_GROUP, $this->_currentCommand, $matches)) {
            return  $matches['comp'] ?? "";
        }
    }

    public function jump(): string
    {
        if($this->commandType() !== self::C_COMMAND) {
            throw new Exception("Current command is no C_COMMAND");
            return "";
        }

        if (preg_match(self::C_COMMAND_PATTERN_GROUP, $this->_currentCommand, $matches)) {
            return  $matches['jump'] ?? "";
        }
    }

    public function commandType(): int
    {
        //コメントアウトは無効
        if(preg_match('/\/\//', $this->_currentCommand)) {
            return self::NOT_VALID_COMMAND;
        }
    
        //空白行を無視
        if(empty($this->_currentCommand)) {
            return self::NOT_VALID_COMMAND;
        }

        if($this->_currentCommand[0] === "@") {
            return self::A_COMMAND;
        }elseif($this->_currentCommand[0] === "(" && $this->_currentCommand[strlen($this->_currentCommand) - 1] === ")") {
            return self::L_COMMAND;
        } else {
            return self::C_COMMAND;
        }
    }
}