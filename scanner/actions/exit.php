<?php

class Action_Exit {
    
    public function leaveNode(PHPParser_Node $node) {
        if($node instanceof PHPParser_Node_Expr_FuncCallExit) {
            Scanner::scanFileEnd(true);
        }
        
        elseif($node instanceof PHPParser_Node_Stmt_Return) {
            if(ScanInfo::getScope() != '') {
                Scanner::scanStatementsEnd();
            }
            else {
                Scanner::scanFileEnd();
            }
        }
    }
}
?>
