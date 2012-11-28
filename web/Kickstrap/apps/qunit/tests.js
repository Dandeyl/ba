// Write your tests here to prevent them from loading before QUnit does.
// test("a basic unit test", function() {var value="hello";equal(value,"hello","We expect value to be hello");});

// Tests
// -----

/*module("Browser Properties Test"); // Throws a crazy loop with less.js
test("localStorage Test", function() {
	var lSMatch = ("w" == (localStorage.setItem = "w"));
	ok(lSMatch, "Browser has localStorage"); 
  var consoleLogVal = (typeof window.console.log == 'function')
  ok(consoleLogVal, "console.log() is running");
});*/

module("Subdirectory Tests");
test("rootDir definition", function() {
	ok(rootDir, "var rootDir is defined as " + rootDir);
});
// get blank.js
var passed = false;
$.getScript(rootDir + 'Kickstrap/apps/qUnit/blank.js', function() {
	passed = true;
})
// shouldn't take more than a second.
kickstrap.ready(function() {
	module("Subdirectory Tests");
	test("Subdirectory file retrieval", function() {
	  ok(passed, "Subdirectory (blank.js) retrieval working.");
  });
});

module("Common Errors");
test("Universals must load to trigger other apps", function() {
	ok(universals.path, "Universals path set");
	ok(typeof window.setDir == 'function', "setDir() exists");
});
test("Let isolated jQ functions run", function() {
	(function($){  
		 $.unitTest = function( m , o ) {return true;}; 
	})(jQuery);
	ok($.unitTest(), " Successfully ran a $. function");
});
setTimeout(function() {
	module("Common Errors");
	test("App resource loading", function() {
		for(i=0;i<appArray.length;i++) {
	  	ok(appArray[i].countRequired[0] == appArray[i].countRequired[1],
	  	"All resources loaded for " + appArray[i].name);
		}
	});
}, 2000); // Allow the apps to load.

