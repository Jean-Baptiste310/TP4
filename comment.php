<?php

session_start();

include_once('mysql.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['LOGGED_USER'])) {

    $recipeId = $_POST['recipe_id'];
    $comment = $_POST['comment'];

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

    // Ajouter le commentaire
    $sqlQuery = '
        INSERT INTO comments(user_id, recipe_id, comment)
        VALUES(:user_id, :recipe_id, :comment)
    ';

    $insertComment = $db->prepare($sqlQuery);

    $insertComment->execute([
        'user_id' => $user['user_id'],
        'recipe_id' => $recipeId,
        'comment' => $comment
    ]);
}

header('Location: index.php');
exit;
