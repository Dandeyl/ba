<?php
/**
 * Some nodes, such as echo are a language construct rather than a function call.
 * However, this is only important for parsing the file, not for scanning. Thats why
 * we make a function call of these statements.
 */
class NodeVisitor_LanguageConstructFunctionRenamer extends PHPParser_NodeVisitorAbstract {
    public function enterNode(PHPParser_Node $node) {
        // Echo
        if($node instanceof PHPParser_Node_Stmt_Echo) {
            $arguments = array();
            foreach($node->exprs as $expr) {
                $arguments[] = new PHPParser_Node_Arg($expr);
            }
            
            $newnode = new PHPParser_Node_Expr_FuncCallEcho(
                    new PHPParser_Node_Name('echo'),
                    $arguments,
                    $node->getAttributes()
            );
            return $newnode;
        }
        
        // Print
        elseif($node instanceof PHPParser_Node_Expr_Print) {
            $newnode = new PHPParser_Node_Expr_FuncCallPrint(
                new PHPParser_Node_Name('print'),
                array(new PHPParser_Node_Arg($node->expr)),
                $node->getAttributes()
            );
            return $newnode;
        }
        
        // Exit
        elseif($node instanceof PHPParser_Node_Expr_Exit) {
            $arguments = array();
            if($node->expr) {
                $arguments[] = new PHPParser_Node_Arg($node->expr);
            }
            
            $newnode = new PHPParser_Node_Expr_FuncCallExit(
                new PHPParser_Node_Name('exit'),
                $arguments,
                $node->getAttributes()
            );
            return $newnode;
        }
        
        // Include
        elseif($node instanceof PHPParser_Node_Expr_Include) {
            $newnode = new PHPParser_Node_Expr_FuncCallInclude(
                new PHPParser_Node_Name('include'),
                array(new PHPParser_Node_Arg($node->expr)),
                $node->type,
                $node->getAttributes()
            );
            return $newnode;
        }
    }
}