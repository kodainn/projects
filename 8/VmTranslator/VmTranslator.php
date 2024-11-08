<?php

declare(strict_types=1);

require("./Parser.php");
require("./CodeWrite.php");
require("./VmConstant.php");


$vmPath = $argv[1];
$vmFilePaths = [];

if(strpos($vmPath, ".vm") === false) {
    $explodePath = explode("\\", $vmPath);
    $outputAssemblyPaht = $argv[1] . "\\" . $explodePath[count($explodePath) - 1] . ".asm";
    $files = glob($vmPath . "/*.vm");
    foreach($files as $file) {
        $vmFilePaths[] = $file;
    }
} else {
    $outputAssemblyPaht = substr($argv[1], 0, strlen($argv[1]) - 3) . ".asm";
    $vmFilePaths[] = $vmPath;
}

$codeWrite = new CodeWrite($outputAssemblyPaht);
foreach($vmFilePaths as $path) {
    $pathSplit = explode("\\", $path);
    $codeWrite->setMvFileName(substr($pathSplit[count($pathSplit) - 1], 0, strlen($pathSplit[count($pathSplit) - 1]) - 3));
    $parser = new Parser($path);
    while($parser->advance()) {
        $commandType = $parser->commandType();
        $arg1 = $parser->arg1();
        $arg2 = 0;
        if(in_array($commandType, [VmConstant::C_POP, VmConstant::C_PUSH, VmConstant::C_FUNCTION, VmConstant::C_CALL])) {
            $arg2 = $parser->arg2();
        }

        if($commandType === VmConstant::C_ARITHMETIC) {
            $codeWrite->writeArithmetic($arg1);
        }
        if($commandType === VmConstant::C_PUSH) {
            $codeWrite->writePushPop(VmConstant::C_PUSH, $arg1, $arg2);
        }
        if($commandType === VmConstant::C_POP) {
            $codeWrite->writePushPop(VmConstant::C_POP, $arg1, $arg2);
        }
        if($commandType === VmConstant::C_LABEL) {
            $codeWrite->writeLabel($arg1);
        }
        if($commandType === VmConstant::C_GOTO) {
            $codeWrite->writeGoto($arg1);
        }
        if($commandType === VmConstant::C_IF) {
            $codeWrite->writeIf($arg1);
        }
        if($commandType === VmConstant::C_FUNCTION) {
            $codeWrite->writeFunction($arg1, $arg2);
        }
        if($commandType === VmConstant::C_CALL) {
            $codeWrite->writeCall($arg1, $arg2);
        }
        if($commandType === VmConstant::C_RETURN) {
            $codeWrite->writeReturn();
        }
    }
}

$codeWrite->closeFile();