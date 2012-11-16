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
    protected static $curr_controllstructure;
    
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
        
        // put information in varlist
        require_once dirname(__FILE__).'/info_initialisation/variable_initialisations.php';
        
        // put information in function list
        require_once dirname(__FILE__).'/info_initialisation/function_initialisations.php';
    }
    
    
    
    //////////////////////////////////////////
    //     ---- File related methods
    //////////////////////////////////////////
    
    /**
     * Return the file tree.
     * @return Obj_Filetree
     */
    public static function getFileTree() {
        return self::$filetree;
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
        self::$varlist->setScope(self::getScope());
        return self::$varlist->find($name);
    }
    
    /**
     * Adds a variable to the varlist.
     * @param Obj_Variable $var Variable that gets registered
     * @param PHPParser_Node $node Assignment node. This is a mandatory parameter to build the history of the variable.
     * @return type
     */
    public static function addVar(Obj_Variable $var) {
        return self::$varlist->push($var);
    }
    
    
    /**
     * Adds a variable to the varlist.
     * @param string $name
     * @return type
     */
    public static function removeVar($name) {
        self::$varlist->setScope(self::getScope());
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
    
    
    //////////////////////////////////////////
    //   ---- vulnerability related methods
    //////////////////////////////////////////
    
    /**
     * Adss a detected vulnerability to the list of found vulnerabilities.
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addVulnerability($type, $node, $file, $line) {
        self::$vulnerabilitylist->addVulnerability(1, $type, $node, $file, $line);
    }
    
    /**
     * Adds a warning to the scaninfo. Warnings can lead to a vulnerability
     * @param int $type
     * @param string $file
     * @param int $line
     * @param PHPParser_Node $node
     */
    public static function addWarning($type, $file, $line, PHPParser_Node $node) {
       self::$vulnerabilitylist->addWarning(1, $type, $node, $file, $line);
    }
    
    public static function addNotFoundClass($name) {
        
    }
    
    /**
     * 
     * @param type $name
     */
    public static function addNotFoundFunction($name) {
        
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
    }
}