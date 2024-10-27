    // Put your code here:
        @R0
        M=0
        @R1
        M=0
(LOOP)  
        @KBD
        D=M
        @COLORBLACK
        D;JEQ
        @R0    //color
        M=0
        @PAINT
        0;JMP
(COLORBLACK)
        @R0    //color
        M=-1
        @PAINT
        0;JMP
(PAINT)
        @SCREEN
        D=A
        @8191
        D=D+A
        @R1  // Address 
        M=D
(FILL)
        @R0  // color
        D=M
        @R1
        A=M
        M=D
        @R1
        MD=M-1
        @SCREEN
        D=D-A
        @FILL
        D;JGE
        @LOOP
        0;JMP
(END)
        @END
        0;JMP