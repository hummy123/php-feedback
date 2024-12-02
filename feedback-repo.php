<?php
  class FeedbackRepo {
    // basic database settings for MySQL.
    // will need to change some of these settings
    // since we are running on localhost
    // and there are dependencies to be aware of 
    // like the MySQL-related PHP extensions being installed
    // PREQREUISITE: User of this script must have a MySQL database
    // named 'doac
    private $DB_HOST = 'localhost';
    private $DB_USERNAME = 'root';
    private $DB_PW = '4475';
    private $DB_NAME = 'doac';
    private $TABLE_NAME = 'feedback';

    // db connection
    private $conn = null;
    // prepared statements for security and efficiency
    private $insert_stmt = null;
    private $find_email_stmt = null;
    private $update_stmt = null;

    function __construct() {
      // Connect to database
      $this->conn = mysqli_connect($this->DB_HOST, $this->DB_USERNAME, $this->DB_PW, $this->DB_NAME);

      // display connection edrror and exit script
      // if error occurred
      if ($this->conn->connect_error) {
        die("feedback-repo connection failed: " . $this->conn->connect_error);
      }

      // Create table if it doesn't exist
      // Table design note: We use the surrogate key "feedback_id"
      // even though the natural key "email" will work for this case.
      // This gives us greater flexibility if we want to add a description field,
      // where the user inputs a text review in a text field, later.
      // If we did want that functionality (although it is not in the assignment), 
      // it would make sense to allow multiple email records.
      // Since, if a user enters a review, submits it, and later wants to say something
      // else that they forgot, it would make sense to allow multiple email records
      // rather than concatenating the user's previous message to the new message
      // and possibly bypassing the VARCHAR limit.
      $query = <<<SQL_CREATE_TABLE
CREATE TABLE IF NOT EXISTS $this->TABLE_NAME (
  feedback_id INT PRIMARY KEY AUTO_INCREMENT,
  full_name VARCHAR(25) NOT NULL,
  email VARCHAR(25) NOT NULL,
  rating INT NOT NULL
);
SQL_CREATE_TABLE;
      mysqli_query($this->conn, $query);

      // Initialise prepared statements
      $this->insert_stmt = $this->conn->prepare(
        "INSERT INTO $this->TABLE_NAME (full_name, email, rating) VALUES (?, ?, ?);"
      );
      $this->find_email_stmt = $this->conn->prepare(
        "SELECT COUNT(email) as count FROM $this->TABLE_NAME WHERE email = ?;"
      );
      $this->update_stmt = $this->conn->prepare(
        "UPDATE $this->TABLE_NAME SET rating = ? WHERE email = ?;"
      );
    }

    /*
     * Type for associative $errors associative array (JSON notation):
     * {
     *   "name type": string | null,
     *   "rating type": string | null,
     *   "rating value": string | null,
     *   "email type": string | null,
     *   "email length": string | null,
     *   "name length": string | null,
     *   "email @": string | null,
     *   "email domain": string | null
     * }
     * */

    // I personally prefer static, stateless functions, when possible. 
     private static function validate_string($field_name, $field, $errors) {
      // Store result of strlen in variable
      // I don't know a lot about PHP's compiler and optimisations,
      // but in C, a naive compiler will cause a performance drain
      // by calculating the length of the string twice.
      // Smarter compilers like GCC and clang will recognise strlen is pure
      // and thus calculate it only once.
      // I don't know if PHP has same optimisation, so I am doing that manually.
      $len = strlen($field);
      if ($len === 0) {
         $errors["$field_name length"] = "$field_name is required";
      } elseif ($len > 25) {
        // because of VARCHAR limit in table.
	// Better to return validation error visible to user
	// rather than letting MySQL throw exception
        $errors["$field_name length"] = "$field_name must be 25 characters or less";
      }
      return $errors;
    }

    private static function validate_fields($name, $email, $rating) {
      $errors = [];

      // We could try being more thorough in validating the name,
      // such as checking that at least one space exists
      // (indicating that it is a full name)
      // but it's better to avoid this assumption
      // due to different cultures having different naming conventions.
      if (is_string($name)) {
        $errors = self::validate_string("name", $name, $errors);
      } else {
        $errors["name type"] = "name must be string";
      }

      if (is_string($email)) {
        // Email is a string, so validate it further.
        // We don't want to validate email too heavily with regex for example.
        // Email-validity regexes tend to be error prone 
        // because of the complexity in the email address specification.
        // The usual way to verify is by sending an email to the address
        // and asking the user to click a link which will verify their email as valid
        // but that is overkill in this case.
        // So we will just perform basic validation,
        // checking for the "@" and "." characters,
        // which should be in every email.
        $errors = self::validate_string("email", $email, $errors);
	if (!str_contains($email, "@")) {
          $errors["email @"] = "\nemail must have an @ sign";
	}
	if (!str_contains($email, ".")) {
          $errors["email domain"] = "email must have a domain like .com";
	}
      } else {
        // email is not a string
	$errors["email type"] = "email must be string";
      }

      if (is_numeric($rating)) {
        // Check that rating is between 0 and 5
        if ($rating < 0 || $rating > 5) {
	  $errors["rating value"] = "rating must be between 0 and 5";
        }
      } else {
        // rating must be integer but it is not
	$errors["rating type"] = "rating must be between 0 and 5";
      }

      return $errors;
    }

    private function emailExists($email) {
      $this->find_email_stmt->bind_param("s", $email); $this->find_email_stmt->execute();
      $result = $this->find_email_stmt->get_result();
      $email = $result->fetch_assoc();
      return $email["count"] > 0;
    }

    public function insert($name, $email, $rating) {
      $rating = intval($rating, 10);
      // escape string arguments for security
      $name = $this->conn->real_escape_string($name);
      // normalise email to lowercase as well
      // so hello@example.com is same as hElLo@eXaMpLe.com
      $email = $this->conn->real_escape_string($email);
      $email = strtolower($email);

      // Validate arguments
      $errors = self::validate_fields($name, $email, $rating);

      // Have validation errors so can't continue.
      if (sizeof($errors) > 0) {
         return $errors;
      }

      // Arguments are valid, so insert into db
      if ($this->emailExists($email)) {
        // If email exists, have to to update rating rater than insert
        // new record with same email.
        $this->update_stmt->bind_param("is", $rating, $email);
        $this->update_stmt->execute();
      } else {
        // Email does not exist, so insert new record
        $this->insert_stmt->bind_param("ssi", $name, $email, $rating);
        $this->insert_stmt->execute();
      }
      return [];
    }
  }
?>
