<?php

abstract class Assignment {
    // Assignment types
    const Assign = 1;
    const AssignBitwiseAnd = 2;
    const AssignBitwiseOr = 3;
    const AssignBitwiseXor = 4;
    const AssignConcat = 5;
    const AssignDiv = 6;
    const AssignMinus = 7;
    const AssignMod = 8;
    const AssignMul = 9;
    const AssignPlus = 10;
    const AssignRef = 11;
    const AssignShiftLeft = 12;
    const AssignShiftRight = 13;
    const ClassConstant = 14;
    const Constant = 15;
    const FuncDefine = 16;
    
    // what is getting assigned
    const TargetVariable = 1;
    const TargetArray = 2;
    const TargetProperty = 3;
    const TargetStaticProperty = 4;
    const TargetConstant = 5;
    const TargetClassConstant = 6;
}