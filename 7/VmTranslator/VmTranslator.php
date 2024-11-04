<?php

declare(strict_types=1);

require("./Parser.php");
require("./CodeWrite.php");
require("./VmConstant.php");


$vmPath = $argv[1];
$outputAssemblyPaht = substr($argv[1], 0, strlen($argv[1]) - 3) . ".asm";
$vmFilePaths = [];

if(strpos($vmPath, ".vm") === false) {
    $files = glob($vmPath . "/*.vm");
    foreach($files as $file) {
        $vmFilePaths[] = $file;
    }
} else {
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
    }
}

$codeWrite->closeFile();