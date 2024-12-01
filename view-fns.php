<?php
  // This file just has string interpolation functions for building HTML.
  // I'm aware of the 'index.php' and 'index.view.php' pattern
  // where global variables are declared in index.php 
  // and where those global variables are used in index.view.php to build HTML
  // but I don't like that pattern because it pollutes the global scope with variables
  // and it also creates implicit coupling between two files (index.php and index.view.php
  // must use the same variable names but the code does not make this clear).
  // In my view, it's more sensible for index.view.php to contain functions which return HTML strings
  // rather than the usual pattern.
  // I know PHP HTML templating systems exist and appreciate them, but I want to be low on dependencies
  // because I don't know or control the PHP environment where this script will run.
  class ViewFns {

    public static function open_body($title) {
      // escape $title to make it safe to include in HTML
      $title = htmlspecialchars($title);

      return <<<OPEN_BODY
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
	  rel="stylesheet" 
	  integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" 
          crossorigin="anonymous">
    <title>$title</title>
  </head>
  <body>
OPEN_BODY;
    }

    public static function close_body() {
     return <<<CLOSE_BODY
  </body>
</html>
CLOSE_BODY;
    }
  }

?>
