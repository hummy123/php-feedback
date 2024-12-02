<?php
  require "feedback-repo.php";

  $repo = new FeedbackRepo;
  $full_name = $_POST["full_name"];
  $email = $_POST["email"];
  $rating = $_POST["rating"];

  $errors = $repo->insert($full_name, $email, $rating);

  var_dump($errors);
?>
