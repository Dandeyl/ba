<?php
/**
 * This class holds all information about the scanning process like which functions,
 * methods and classes are defined, which files included, which variables declared etc.
 * 
 */
abstract class ScanInfo {
    /**
     * In which run we are at the moment.
     * @var int 
     */
    protected static $scan_run;


    /**
     * List of files that get parsed.
     * @var Obj_Filetree
     */
    protected static $filetree;
    
    /**
     * List of variables in the whole programm.
     * @var Obj_Varlist
     */
    protected static $varlist;
    
    /**
     * List of functions that were defined during parsing process
     * @var Obj_Function[] 
     */
    protected static $functions;
    
    /**
     * Name of the current scope.
     * Naming conventions:
     *  - Function:          Function#<name>
     *  - Class:                Class#<name>
     *  - Namespace:        Namespace#<name>
     *  - Method:              Method#<classname>#<methodname>
     *  - Static Method: StaticMethod#<classname>#<methodname>
     * 
     * @param string $scope
     */
    protected static $scope = '';
    
    /**
     * The controlstructure we are in at the moment. Can be an array 
     * @var Obj_ControlStructure[] 
     */
    protected static $controlstructures;
    
    /**
     * List of vulnerabilities that were found.
     * @var Obj_VulnerabilityList[] 
     */
    protected static $vulnerabilitylist;
    
    
    /**
     * Includes that were not successful.
     * @var Obj_FailedInclude[]
     */
    protected static $failed_includes;
    
    
    /**
     * Initialise scanning information
     */
    public static function init() {
        // initialise Information
        self::$scan_run = 1;
        self::$varlist = new Obj_Varlist;
        self::$vulnerabilitylist = new Obj_VulnerabilityList;
        self::$controlstructures = array();
        
        // put information in varlist
        require_once dirname(__FILE__).'/info_initialisation/variable_initialisations.php';
        
        // put information in function list
        require_once dirname(__FILE__).'/info_initialisation/function_initialisations.php';
    }
    
    
    
    /////////////////////////////////////////////
    //     ---- File related methods           //
    /////////////////////////////////////////////
    
    /**
     * Return the file tree.
     * @return Obj_Filetree
     */
    public static function getFileTree() {
        return self::$filetree;
    }
    
    /**
     * Returns an array of files that were included to get to the current node
     * @return array
     */
    public static function getCurrentFileStack() {
        return self::$filetree->getCurrentFileStack();
    }
    
    /**
     * Add a file to the file tree.
     * @param string $file
     */
    public static function addFile($file) {
        if(null !== self::$filetree) {
            self::$filetree->push($file);
        }
        else {
            self::$filetree = new Obj_Filetree($file);
        }
    }
    
    /**
     * Return to the parent file in file tree.
     * @param string $file
     */
    public static function parentFile() {
        // are there paths left to go?
        try {
            return self::$filetree->parent();
        }
        // Ende of Scan reached, check if there are ControlStructure paths left
        catch(Obj_Filetree_EndOfTree_Exception $e) {
            $control_structs = &self::$controlstructures;
            if(!empty($control_structs)) {
                // get last control structure
                /* @var $struct Obj_Controlstructure */
                $struct = null;
                while($struct == null) {
                    $struct = end($control_structs);
                    // if last path of structure has been gone in this iteration, delete this
                    // controlstructure and go to next higher level structure
                    if($struct->getCurrentPathNumber() == $struct->getNumPaths()-1) {
                        ScanInfo::removeLastControlStructure();
                        $struct = null;
                    }
                    else {
                        $struct->incCurrentPathNumber();
                    }
                    // no structures anymore
                    if(empty($control_structs)) {
                        break;
                    }
                }
                
                if($struct) {
                    $skipwalker = new NodeVisitor_WalkTo($struct->getNode(), $struct->getFileStack());
                    $tree = self::$filetree;
                    $file = $tree::getMainFile()->getPath();
                    // start scanning from beginning with NodeVisitor_WalkTo enabled -> walks to last found controlstructure
                    Scanner::walkFile($file, $skipwalker);
                }
            }
        }
    }
    
    /**
     * Returns the content of the currently scanned file
     * @return string
     */
    public static function getCurrentFileContent() {
        return self::$filetree->getContents();
    }
    
    
    /**
     * Return the real path of the currently scanned file.
     * @return string
     */
    public static function getCurrentFilePath() {
        return self::$filetree->getPath();
    }
    
    public static function getCurrentFileLine() {
        return self::$filetree->getLine();
    }
    
    public static function setCurrentFileLine($line) {
        self::$filetree->setLine($line);
    }
    /**
     * Add a file to the list of failed includes.
     * @param Obj_FailedInclude $fi
     */
    public static function addFailedInclude(Obj_FailedInclude $fi) {
        self::$failed_includes[] = $fi;
    }
    
    
    
    //////////////////////////////////////////
    //   ---- variable related methods
    //////////////////////////////////////////
    
    /**
     * Get the current list of variables.
     * @return Obj_Varlist
     */
    public static function getVarlist() {
        return self::$varlist;
    }
    
    /**
     * Find a variable with the given name in the current scope.
     * @param string $name
     * @return &Obj_Variable|false
     */
    public static function findVar($name) {
        if($name == '$baz') {
            $breakpoint = true;
        }
        
        self::$varlist->setScope(self::getScope());
        return self::$varlist->find($name);
    }
    
    /**
     * Adds a variable to the varlist.
     * @param Obj_Variable $insertvar Variable that gets registered
     * @param PHPParser_Node $node Assignment node. This is a mandatory parameter to build the history of the variable.
     * @return type
     */
    public static function addVar(Obj_Variable $insertvar) {
        $name   = $insertvar->getName();
        return self::$varlist->push($insertvar);
    }
    
    
    /**
     * Adds a variable to the varlist.
     * @param string $name
     * @return type
     */
    public static function removeVar($name) {
        return self::$varlist->remove($name);
    }
    
    
    //////////////////////////////////////////
    //   ---- scope related methods
    //////////////////////////////////////////
    
    /**
     * Set the current scope. This method has to be called every time a function, method, or namespace is entered or left. 
     * Naming conventions:
     *  - Function: Function#<name>
     *  - Class:  Class#<name>
     *  - Namespace: Namespace#<name>
     *  - Method: Method#<classname>#<methodname>
     *  - Static Method: StaticMethod#<classname>#<methodname>
     * @param string $scope_name
     */
    public static function setScope($scope_name) {
        // Todo: Check validity
        self::$scope = $scope_name;
        self::$varlist->setScope($scope_name);
    }
    
    
    /**
     * Get the name of the current scope.
     */
    public static function getScope() {
        return self::$scope;
    }
    
    ///////////////////////////////////////////////////
    //   ---- control structure related methods
    //////////////////////////////////////////////////////
    
    public static function findControlStructure(Obj_ControlStructure $struct) {
        return self::findControlStructureByNode($struct->getNode());
    }
    
    /**
     * Find a control structure using the STMT node.
     * @param PHPParser_Node $node
     * @return Obj_ControlStructure
     */
    public static function findControlStructureByNode(PHPParser_Node $node) {
        $registered_structs = self::$controlstructures;
        
        foreach($registered_structs as &$r_struct) {
            if(Helper_NodeEquals::equals($r_struct->getNode(), $node)) {
                return $r_struct;
            }
        }
    }  
            
    public static function addControlStructure(Obj_ControlStructure $struct) {
        return self::$controlstructures[] = $struct;
    }
    
    /**
     * Removes the last control structure
     * @return Obj_ControlStructure The Last control structure element
     */
    public static function removeLastControlStructure() {
        return array_pop(self::$controlstructures);
    }
    
    //////////////////////////////////////////
    //   ---- vulnerability related methods
    //////////////////////////////////////////
    
    /**
     * Adds a detected vulnerability to the list of found vulnerabilities.
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addVulnerability($type, $node) {
        self::$vulnerabilitylist->addVulnerability(1, $type, $node, self::getCurrentFilePath(), self::getCurrentFileLine());
    }
    
    public static function getNumVulnerabilities() {
        return self::$vulnerabilitylist->getNumVulnerabilities();
    }
    
    /**
     * Adds a warning to the scaninfo. Warnings can lead to a vulnerability
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addWarning($type, PHPParser_Node $node) {
       self::$vulnerabilitylist->addWarning(1, $type, $node, self::getCurrentFilePath(), self::getCurrentFileLine());
    }
    
    
    
    
    
    
    
    public static function addNotFoundClass($name) {
        
    }
    
    /**
     * 
     * @param type $name
     */
    public static function addNotFoundFunction($name) {
        
    }
    
    
    public static function exportScanData() {
        $exp["vars"]  = self::$varlist;
        $exp["funcs"] = self::$functions;
        $exp["files"] = self::$filetree;
        $exp["scope"] = self::$scope;
        $exp["scan_run"] = self::$scan_run;
        
        return serialize($exp);
    }
    
    public static function importScanData($export) {
        $export = unserialize($export);
        self::$varlist = $export["vars"];
        self::$functions = $export["funcs"];
        self::$filetree = $export["files"];
        self::$scope = $export["scope"];
        self::$scan_run = $export["scan_run"];
    }
    
    /**
     * Dump all scan results
     */
    public static function dump() {
        echo "\n\n\n############### SCAN RESULTATE ###################\n\n";
        echo 'Kritische Vulnerabilities gefunden: '.self::$vulnerabilitylist->getNumVulnerabilities()."\n";
        echo 'Warnings gefunden: '.self::$vulnerabilitylist->getNumWarnings()."\n";
        echo 'Informationen gefunden: '.self::$vulnerabilitylist->getNumInformation()."\n";
        echo "\n\n";
        echo "### Vulnerabilities:\n\n";
        
        $vuln = self::$vulnerabilitylist->getVulnerabilities();
        $i = 1;
        foreach($vuln as $v) {
            echo $i." -----------------------\n";
            echo "Typ: ".$v->getType()."   \n";
            echo "Datei: ".$v->getFile()."  \n";
            echo "Zeile: ".$v->getLine()."  \n";
            echo "> ".Scanner::printNode($v->getNode());
            
            echo "\n\n\n";
            $i++;
        }
        
        echo "\n\nMemory used: ".memory_get_usage(true)."\n";
    }
}