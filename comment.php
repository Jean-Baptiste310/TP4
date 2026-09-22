<?php

session_start();

include_once('mysql.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['LOGGED_USER'])) {

    $recipeId = $_POST['recipe_id'];
    $comment = $_POST['comment'];
    $review = (float) $_POST['review'];

    // Vérifier que la note est comprise entre 0 et 5
    if ($review < 0 || $review > 5) {
        die('La note doit être comprise entre 0 et 5.');
    }

    // Récupérer le user_id grâce à l'email
    $userQuery = $db->prepare('
        SELECT user_id
        FROM users
        WHERE email = :email
    ');

    $userQuery->execute([
        'email' => $_SESSION['LOGGED_USER']['email']
    ]);

    $user = $userQuery->fetch();

    // Ajouter le commentaire et la note
    $sqlQuery = '
        INSERT INTO comments(user_id, recipe_id, comment, review)
        VALUES(:user_id, :recipe_id, :comment, :review)
    ';

    $insertComment = $db->prepare($sqlQuery);

    $insertComment->execute([
        'user_id' => $user['user_id'],
        'recipe_id' => $recipeId,
        'comment' => $comment,
        'review' => $review
    ]);
}

header('Location: index.php');
exit;
