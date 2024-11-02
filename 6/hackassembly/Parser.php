<?php

declare(strict_types=1);

class Parser
{
    private object $_assemblyFile;
    private string $_currentCommand;

    public const A_COMMAND = 1;
    public const C_COMMAND = 2;
    public const L_COMMAND = 3;

    private const A_COMMAND_PATTERN = '/^@([0-9a-zA-Z_\.\$:]+)$/';
    private const C_COMMAND_PATTERN = '/^(?:(A?M?D?)=)?([^;]+)(?:;(.+))?$/';
    private const L_COMMAND_PATTERN = '/^\(([0-9a-zA-Z_\.\$:]*)\)$/';
    private const C_COMMAND_PATTERN_GROUP = '/^(?:(?<dest>[^=]*)=)?(?<comp>[^;]*)(?:;(?<jump>.*))?$/';

    public function __construct(string $assemblyFilePath)
    {
        $this->_assemblyFile = fopen($assemblyFilePath, 'r');
    }

    public function advance(): bool
    {
        $this->_currentCommand = (string) fgets($this->_assemblyFile);

        return !empty($this->_currentCommand);
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
            return  (string) $matches['dest'];
        }
    }

    public function comp()
    {
        if($this->commandType() !== self::C_COMMAND) {
            throw new Exception("Current command is no C_COMMAND");
            return "";
        }

        if (preg_match(self::C_COMMAND_PATTERN_GROUP, $this->_currentCommand, $matches)) {
            return  (string) $matches['comp'];
        }
    }

    public function jump()
    {
        if($this->commandType() !== self::C_COMMAND) {
            throw new Exception("Current command is no C_COMMAND");
            return "";
        }

        if (preg_match(self::C_COMMAND_PATTERN_GROUP, $this->_currentCommand, $matches)) {
            return  (string) $matches['jump'];
        }
    }

    public function commandType(): int
    {
        if(preg_match(self::A_COMMAND_PATTERN, $this->_currentCommand)) {
            return self::A_COMMAND;
        }

        if(preg_match(self::C_COMMAND_PATTERN, $this->_currentCommand)) {
            return self::C_COMMAND;
        }

        if(preg_match(self::L_COMMAND_PATTERN, $this->_currentCommand)) {
            return self::L_COMMAND;
        }

        throw new Exception("Syntax error");
        return 0;
    }
}