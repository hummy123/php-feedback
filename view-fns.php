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
    private static function rating_button($value) {
      return <<<RATING_BUTTON
<input type="radio" 
       id="rating" 
       name="rating" 
       value="$value"
       class="form-check-input position-static" />
RATING_BUTTON;
    }

    public static function rating_form($title) {
      // escape $title to make it safe to include in HTML
      $title = htmlspecialchars($title);

      $rating1 = self::rating_button(1);
      $rating2 = self::rating_button(2);
      $rating3 = self::rating_button(3);
      $rating4 = self::rating_button(4);
      $rating5 = self::rating_button(5);

      return <<<RATING_FORM

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
    <form action="feedback-received.php" method="POST">
      <p>We're all ears! How was it for you? Rate us from 1 (awful) to 5 (awesome).</p>
      <label for="full_name">Full name</label>
      <input id="full_name" 
	     name="full_name" 
             class="form-control"
             required />

      <!-- We can and should rely on browser's built-in email validation
           for a user experience consistent with what the user expects from other sites. -->
      <label for="email">Email</label>
      <input type="email"
             id="email" 
	     name="email" 
             class="form-control"
             required />

      $rating1
      $rating2
      $rating3
      $rating4
      $rating5

      <input type="submit" value="Submit Your Rating" />
    </form>
  </body>
</html>

RATING_FORM;
    }
  }

?>
