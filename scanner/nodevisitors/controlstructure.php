<?php

class NodeVisitor_ControlStructure extends PHPParser_NodeVisitorAbstract {
    /**
     * Checks if the node is a control structure, and starts the required actions if so
     * @param PHPParser_Node $node
     */
    public function enterNode(PHPParser_Node $node) {
        $struct = self::isControlStructure($node);
        
        if($struct === false) {return;}
        
 
        switch($struct) {
            // IF Statement found
            case Obj_ControlStructure::STMT_IF:
                return $this->controlStructure($node, 'resolveIf');
                break;
            case Obj_ControlStructure::EXPR_TERNARY:
                return $this->controlStructure($node, 'resolveTernary');
                break;
            case Obj_ControlStructure::EXPR_LOGICAL_OR:
                return $this->controlStructure($node, 'resolveLogicalOr');
        }
    }
    
    /**
     * If we're leaving a control structure, restore the original structure
     * (since we deleted all paths but one)
     * @param PHPParser_Node $node
     * @return type
     */
    public function leaveNode(PHPParser_Node $node) {
        /*$struct_type = self::isControlStructure($node);
        
        if($struct_type == false) { return; }
        
        return ScanInfo::findControlStructureByNode($node)->getNode();*/
    }
    
    
    
    /**
     * Checks if the current node is a control structure
     * @param PHPParser_Node $node
     */
    public static function isControlStructure(PHPParser_Node $node) {
        if($node instanceof PHPParser_Node_Stmt_If) {
            return Obj_ControlStructure::STMT_IF;
        }
        elseif($node instanceof PHPParser_Node_Stmt_For) {
            return Obj_ControlStructure::STMT_FOR;
        }
        elseif($node instanceof PHPParser_Node_Stmt_Foreach) {
            return Obj_ControlStructure::STMT_FOREACH;
        }
        elseif($node instanceof PHPParser_Node_Stmt_While) {
            return Obj_ControlStructure::STMT_WHILE;
        }
        elseif($node instanceof PHPParser_Node_Stmt_Do) {
            return Obj_ControlStructure::STMT_DO;
        }
        elseif($node instanceof PHPParser_Node_Stmt_Switch) {
            return Obj_ControlStructure::STMT_SWITCH;
        }
        elseif($node instanceof PHPParser_Node_Expr_Ternary) {
            return Obj_ControlStructure::EXPR_TERNARY;
        }
        elseif($node instanceof PHPParser_Node_Expr_LogicalOr) {
            return Obj_ControlStructure::EXPR_LOGICAL_OR;
        }
    }
    
    
    /**
     * A control structure was found. Now detect all paths that have to be executed and
     * append them to the ScanInfo-ControlStructure-List.
     * @param PHPParser_Node $node
     */
    protected function controlStructure(PHPParser_Node $node, $resolveFunc) {
        $struct = ScanInfo::findControlStructureByNode($node);
        
        if(!$struct) {
            $struct    = Helper_ControlStructureResolver::$resolveFunc($node);
            
            // if more than one path, add it to the controlstructure stack
            if($struct->getNumPaths() > 1){
                $filestack = ScanInfo::getFileTree()->getCurrentFileStack();
                $struct->setFileStack($filestack);
                $struct->setScanInfoExport(ScanInfo::exportScanData());
                ScanInfo::addControlStructure($struct);
            }
            
            return $struct->getPath(0)->getStmts();
        }
        else {
            // This structure is already registered. Import data a
            ScanInfo::importScanData($struct->getScanInfoExport());
            return $struct->getPath($struct->getCurrentPathNumber())->getStmts();
        }
    }
}
