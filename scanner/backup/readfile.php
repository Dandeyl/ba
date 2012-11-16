<?php
/**
 * This class is not needed anymore
 */
class ReadFile_NodeVisitor extends PHPParser_NodeVisitorAbstract
{
    public function enterNode(PHPParser_Node $node) {
        global $nodeDumper;
        
        
        
        if($node->getType() == 'Expr_FuncCall') {
            $funcname = $node->name->parts[0];
            
            echo ($nodeDumper->dump($node))."\n\n\n";
            
            if($funcname == 'readfile') {
                $value = $node->args[0]->value;
                
                // if argument of function readfile is a string, return because it is safe and can not be manipulated
                if($value->value !== null) {
                    return;
                }
                
                
                // if argument is not a static string
                echo ($nodeDumper->dump($value))."\n\n\n";
            }
            
        }
    }
}