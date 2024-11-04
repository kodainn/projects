<?php

declare(strict_types=1);

class CodeWrite
{
    private mixed $_assemblyFile;
    private string $_currentMvFileName;
    private int $_labelNumber;

    public function __construct(string $outputAssemblyFilePath)
    {
        $this->_assemblyFile = fopen($outputAssemblyFilePath, "w");
        $this->_labelNumber = 0;
    }

    public function setMvFileName(string $fileName): void
    {
        $this->_currentMvFileName = $fileName;
    }

    public function writeArithmetic(string $command): void
    {
        if(in_array($command, [VmConstant::L_ADD, VmConstant::L_SUB, VmConstant::L_AND, VmConstant::L_OR])) {
            $this->writeBinaryOperation($command);
        }
        if(in_array($command, [VmConstant::L_NEG, VmConstant::L_NOT])) {
            $this->writeUnaryOperation($command);
        }
        if(in_array($command, [VmConstant::L_EQ, VmConstant::L_GT, VmConstant::L_LT])) {
            $this->writeCompOperation($command);
        }
    }

    public function writePushPop(int $command, string $segment, int $index): void
    {
        if($command === VmConstant::C_PUSH) {
            if($segment === VmConstant::CONSTANT) {
                $this->writeCodes([
                    "@" . strval($index),
                    "D=A"
                ]);
                $this->writePushFromDRegister();
            }
            if(in_array($segment, [VmConstant::LOCAL, VmConstant::ARGUMENT, VmConstant::THIS, VmConstant::THAT], true)) {
                $this->writePushFromVirtualSegment($segment, $index);
            }
            if(in_array($segment, [VmConstant::TEMP, VmConstant::POINTER])) {
                $this->writePushFromPointerAndTempSegment($segment, $index);
            }
            if($segment === VmConstant::STATIC) {
                $this->writeCodes([
                    "@" . $this->_currentMvFileName . "." . strval($index),
                    "D=M"
                ]);
                $this->writePushFromDRegister();
            }
        }
        if($command === VmConstant::C_POP) {
            if(in_array($segment, [VmConstant::LOCAL, VmConstant::ARGUMENT, VmConstant::THIS, VmConstant::THAT], true)) {
                $this->writePopFromVirtualSegment($segment, $index);
            }
            if(in_array($segment, [VmConstant::TEMP, VmConstant::POINTER])) {
                $this->writePopFromPointerAndTempSegment($segment, $index);
            }
            if($segment === VmConstant::STATIC) {
                $this->writePopToMRegister();
                $this->writeCodes([
                    "D=M",
                    "@" . $this->_currentMvFileName . "." . strval($index),
                    "M=D"
                ]);
            }
        }
    }

    public function closeFile(): void
    {
        fclose($this->_assemblyFile);
    }

    private function writeBinaryOperation(string $command): void
    {
        $this->writePopToMRegister();
        $this->writeCode("D=M");
        $this->writePopToMRegister();
        if($command === VmConstant::L_ADD) {
            $this->writeCode("D=D+M");
        }
        if($command === VmConstant::L_SUB) {
            $this->writeCode("D=M-D");
        }
        if($command === VmConstant::L_AND) {
            $this->writeCode("D=D&M");
        }
        if($command === VmConstant::L_OR) {
            $this->writeCode("D=D|M");
        }
        $this->writePushFromDRegister();
    }

    private function writeUnaryOperation(string $command): void
    {
        $this->writeCodes([
            "@SP",
            "A=M-1"
        ]);
        if($command === VmConstant::L_NEG) {
            $this->writeCode("M=-M");
        }
        if($command === VmConstant::L_NOT) {
            $this->writeCode("M=!M");
        }
    }

    private function writeCompOperation(string $command): void
    {
        if($command === VmConstant::L_EQ) {
            $compType = "JEQ";
        }
        if($command === VmConstant::L_GT) {
            $compType = "JGT";
        }
        if($command === VmConstant::L_LT) {
            $compType = "JLT";
        }

        $label1 = $this->getNewLabel();
        $label2 = $this->getNewLabel();
        $this->writePopToMRegister();
        $this->writeCode("D=M");
        $this->writePopToMRegister();
        $this->writeCodes([
            "D=M-D",
            "@" . $label1,
            "D;" . $compType,
            "D=0",
            "@" . $label2,
            "0;JMP",
            "(" . $label1 . ")",
            "D=-1",
            "(" . $label2 . ")"
        ]);

        $this->writePushFromDRegister();
    }

    private function writePushFromVirtualSegment(string $segment, int $index): void
    {
        if($segment === VmConstant::LOCAL) {
            $registerName = "LCL";
        }
        if($segment === VmConstant::ARGUMENT) {
            $registerName = "ARG";
        }
        if($segment === VmConstant::THIS) {
            $registerName = "THIS";
        }
        if($segment === VmConstant::THAT) {
            $registerName = "THAT";
        }

        $this->writeCodes([
            "@" . $registerName,
            "A=M"
        ]);

        for($i = 0; $i < $index; $i++) {
            $this->writeCode("A=A+1");
        }

        $this->writeCode("D=M");
        $this->writePushFromDRegister();
    }

    private function writePopFromVirtualSegment(string $segment, int $index): void
    {
        if($segment === VmConstant::LOCAL) {
            $registerName = "LCL";
        }
        if($segment === VmConstant::ARGUMENT) {
            $registerName = "ARG";
        }
        if($segment === VmConstant::THIS) {
            $registerName = "THIS";
        }
        if($segment === VmConstant::THAT) {
            $registerName = "THAT";
        }

        $this->writePopToMRegister();
        $this->writeCodes([
            "D=M",
            "@" . $registerName,
            "A=M"
        ]);

        for($i = 0; $i < $index; $i++) {
            $this->writeCode("A=A+1");
        }
        $this->writeCode("M=D");
    }

    private function writePushFromPointerAndTempSegment(string $segment, int $index): void
    {
        if($segment === VmConstant::POINTER) {
            $baseAddress = VmConstant::POINTER_BASE_ADDRESS;
        }
        if($segment === VmConstant::TEMP) {
            $baseAddress = VmConstant::TEMP_BASE_ADDRESS;
        }

        $this->writeCode("@" . strval($baseAddress));

        for($i = 0; $i < $index; $i++) {
            $this->writeCode("A=A+1");
        }

        $this->writeCode("D=M");
        $this->writePushFromDRegister();
    }

    private function writePopFromPointerAndTempSegment(string $segment, int $index): void
    {
        if($segment === VmConstant::POINTER) {
            $baseAddress = VmConstant::POINTER_BASE_ADDRESS;
        }
        if($segment === VmConstant::TEMP) {
            $baseAddress = VmConstant::TEMP_BASE_ADDRESS;
        }

        $this->writePopToMRegister();

        $this->writeCodes([
            "D=M",
            "@" . strval($baseAddress)
        ]);

        for($i = 0; $i < $index; $i++) {
            $this->writeCode("A=A+1");
        }
        $this->writeCode("M=D");
    }

    private function writePushFromDRegister(): void
    {
        $this->writeCodes([
            "@SP",
            "A=M",
            "M=D",
            "@SP",
            "M=M+1"
        ]);
    }

    private function writePopToMRegister(): void
    {
        $this->writeCodes([
            "@SP",
            "M=M-1",
            "A=M"
        ]);
    }

    private function writeCode(string $code): void
    {
        fwrite($this->_assemblyFile, $code . PHP_EOL);
    }

    private function writeCodes(array $codes): void
    {
        foreach($codes as $code) {
            $this->writeCode($code);
        }
    }

    private function getNewLabel(): string
    {
        $this->_labelNumber++;
        return "LABEL" . strval($this->_labelNumber);
    }
}