<?php

class NodeVisitor_Exit extends PHPParser_NodeVisitorAbstract {
    
    public function leaveNode(PHPParser_Node $node) {
        if($node instanceof PHPParser_Node_Expr_Exit) {
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
