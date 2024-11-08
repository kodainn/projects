<?php

declare(strict_types=1);

class Parser
{
    private mixed $_vmFile;

    private string $_currentCommand;
    private string $_currentArg1;
    private int    $_currentArg2;

    public function __construct(string $vmFilePath)
    {
        $this->_vmFile = fopen($vmFilePath, "r");
    }

    public function advance(): bool
    {
        if(is_resource($this->_vmFile) === false) {
            return false;
        }

        $this->nextEnableCommands();
        if($this->isEnableCommands() === false) {
            fclose($this->_vmFile);
            return false;
        }

        return true;
    }

    public function commandType(): int
    {
        if($this->_currentCommand === VmConstant::L_PUSH) {
            return VmConstant::C_PUSH;
        }
        if($this->_currentCommand === VmConstant::L_POP) {
            return VmConstant::C_POP;
        }
        if($this->_currentCommand === VmConstant::L_LABEL) {
            return VmConstant::C_LABEL;
        }
        if($this->_currentCommand === VmConstant::L_GOTO) {
            return VmConstant::C_GOTO;
        }
        if($this->_currentCommand === VmConstant::L_IF) {
            return VmConstant::C_IF;
        }
        if($this->_currentCommand === VmConstant::L_FUNCTION) {
            return VmConstant::C_FUNCTION;
        }
        if($this->_currentCommand === VmConstant::L_RETURN) {
            return VmConstant::C_RETURN;
        }
        if($this->_currentCommand === VmConstant::L_CALL) {
            return VmConstant::C_CALL;
        }
        if(in_array($this->_currentCommand, [
            VmConstant::L_ADD, VmConstant::L_SUB, VmConstant::L_NEG, VmConstant::L_EQ, VmConstant::L_GT, VmConstant::L_LT, VmConstant::L_AND, VmConstant::L_OR, VmConstant::L_NOT
        ])) {
            return VmConstant::C_ARITHMETIC;
        }

        throw new Exception("Syntax error");
    }

    public function arg1(): string
    {
        if($this->commandType() === VmConstant::C_ARITHMETIC) {
            return $this->_currentCommand;
        }

        return $this->_currentArg1;
    }

    public function arg2(): int
    {
        return $this->_currentArg2;
    }

    private function isEnableCommands(): bool
    {
        return !empty($this->_currentCommand) || !empty($this->_currentArg1) || $this->_currentArg2 !== 0;
    }

    //次の有効なコマンドが現れるまで探す
    private function nextEnableCommands(): void
    {
        $inputCommand = "";
        while($inputCommand !== false) {
            $inputCommand = fgets($this->_vmFile);;

            if($inputCommand !== false) {
                //改行コードの削除
                $inputCommand = substr($inputCommand, 0, strlen($inputCommand) - 2);
                //コメントアウトの削除
                $commentOutBaseIndex = strpos($inputCommand, "//");
                if($commentOutBaseIndex !== false) {
                    $inputCommand = substr($inputCommand, 0, $commentOutBaseIndex);
                }

                $inputCommand = ltrim($inputCommand);

                if(!empty($inputCommand)) {
                    $commands = explode(" ", $inputCommand);
                    $this->_currentCommand = $commands[0];
                    $this->_currentArg1 = isset($commands[1]) ? $commands[1] : "";
                    $this->_currentArg2 = isset($commands[2]) ? intval($commands[2]) : 0;

                    return;
                }
            }
        }

        $this->_currentCommand = "";
        $this->_currentArg1 = "";
        $this->_currentArg2 = 0;
    }
}