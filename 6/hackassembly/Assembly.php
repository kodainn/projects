<?php

declare(strict_types=1);

require(__DIR__ . "/Parser.php");
require(__DIR__ . "/Code.php");
require(__DIR__ . "/SymbolTable.php");

$symbolTable = new SymbolTable();

$assemblyFilePath = __DIR__ . "/../" . $argv[1];

$parser = new Parser($assemblyFilePath);

$opAddress = 0;
while($parser->advance()) {

    $currentCommandType = $parser->commandType();
    if(in_array($currentCommandType, [$parser::A_COMMAND, $parser::C_COMMAND], true)) {
        $opAddress++;
    }

    if($currentCommandType === $parser::L_COMMAND) {
        $symbolTable->addEntry($parser->symbol(), $opAddress);
    }
}

$parser = new Parser($assemblyFilePath);
$hackFilePath = __DIR__ . "/../" . $argv[2];

$hackFile = fopen($hackFilePath, 'w');

while($parser->advance()) {

    $currentCommandType = $parser->commandType();
    if($currentCommandType === $parser::A_COMMAND) {
        $symbol = $parser->symbol();
        if(is_numeric($symbol)) {
            fwrite($hackFile, str_pad(decbin((int) $symbol), 16, "0", STR_PAD_LEFT) . PHP_EOL);
        } else {
            //変数シンボルの追加
            if($symbolTable->contains($symbol) === false) {
                $symbolTable->addVariable($symbol);
            }

            fwrite($hackFile, str_pad(decbin($symbolTable->getAddress($symbol)), 16, "0", STR_PAD_LEFT) . PHP_EOL);
        }
    }

    if($currentCommandType === $parser::C_COMMAND) {
        fwrite($hackFile, "111" . Code::compMnemonicToBinary($parser->comp()) . Code::destMnemonicToBinary($parser->dest()) . Code::jumpMnemonicToBinary($parser->jump()) . PHP_EOL);   
    }
}

fclose($hackFile);