<?php

declare(strict_types=1);

class VmConstant
{
    const C_ARITHMETIC = 1;
    const C_PUSH       = 2;
    const C_POP        = 3;
    const C_LABEL      = 4;
    const C_GOTO       = 5;
    const C_IF         = 6;
    const C_FUNCTION   = 7;
    const C_RETURN     = 8;
    const C_CALL       = 9;

    const ARGUMENT = "argument";
    const LOCAL    = "local";
    const STATIC   = "static";
    const CONSTANT = "constant";
    const THIS     = "this";
    const THAT     = "that";
    const POINTER  = "pointer";
    const TEMP     = "temp";

    const L_PUSH       = "push";
    const L_POP        = "pop";
    const L_LABEL      = "label";
    const L_GOTO       = "goto";
    const L_IF         = "if-goto";
    const L_FUNCTION   = "function";
    const L_RETURN     = "return";
    const L_CALL       = "call";
    const L_ADD        = "add";
    const L_SUB        = "sub";
    const L_NEG        = "neg";
    const L_EQ         = "eq";
    const L_GT         = "gt";
    const L_LT         = "lt";
    const L_AND        = "and";
    const L_OR         = "or";
    const L_NOT        = "not";

    const POINTER_BASE_ADDRESS = 3;
    const TEMP_BASE_ADDRESS    = 5;
}