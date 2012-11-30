<?php
/**
 * Given a condition, this helper collects all expressions that are needed for further
 * correct code execution.
 */
class Helper_ConditionImportantExpressionResolver {
    /**
     *
     * @var PHPParser_NodeTraverser 
     */
    protected static $traverser;
    
    /**
     *
     * @var NodeVisitor_ConditionImportantExpression 
     */
    protected static $visitor;
    
    
    public static function resolve($condition) {
        if(self::$traverser === null) {
            self::$traverser = new PHPParser_NodeTraverser();
            require(dirname(__FILE__).'/conditionimportantexpression/nodevisitor.php');
            self::$visitor = new NodeVisitor_ConditionImportantExpression;
            self::$traverser->addVisitor(self::$visitor);
        }
        
        self::$traverser->traverse(array($condition));
        return self::$visitor->getReturnNodes();
    }
}
?>
