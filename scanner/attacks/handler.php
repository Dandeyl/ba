<?php
/**
 * Class that provides some attack-related static methods
 */
class Attack_Handler {
    
    /**
     * Check if a resolved object meets all conditions required for an attack
     * @param string $attack
     * @param Obj_Resolved $resovled
     */
    public static function checkAttackCondition($attack, Obj_Resolved $resolved) {
        switch($attack) {
            case Attack::Xss:
                return Attack_Xss::checkXssCondition($resolved);
                break;
            case Attack::SqlInjection:
                return Attack_SqlInjection::checkSqlInjectionCondition($resolved);
                break;
        }
    } 
}
?>
