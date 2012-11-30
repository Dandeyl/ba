<?php

/**
 * @property PHPParser_Node_Name|PHPParser_Node_Expr $name Function name
 * @property PHPParser_Node_Arg[]                    $args Arguments
 */
class PHPParser_Node_Expr_FuncCallInclude extends PHPParser_Node_Expr_FuncCall
{    
    /**
     * Constructs an include node.
     *
     * @param PHPParser_Node_Name $name       Name
     * @param PHPParser_Node_Expr $expr       Expression
     * @param int                 $type       Type of include
     * @param array               $attributes Additional attributes
     */
    public function __construct(PHPParser_Node_Name $name, array $args, $type, array $attributes = array()) {
        $this->subNodes = array(
            'name' => $name,
            'args' => $args,
            'type' => $type,
        );
        $this->attributes = $attributes;
    }
}