<?php

session_start();

include_once('mysql.php');

if (!isset($_SESSION['LOGGED_USER'])) {
    die('Vous devez être connecté.');
}

$id = $_GET['id'] ?? null;


// Vérifier que la recette appartient à l'utilisateur
$sqlQuery = '
    SELECT *
    FROM recipes
    WHERE recipe_id = :id
    AND author = :author
';

$recipeStatement = $db->prepare($sqlQuery);

$recipeStatement->execute([
    'id' => $id,
    'author' => $_SESSION['LOGGED_USER']['email']
]);

$recipe = $recipeStatement->fetch();


// Si la recette n'existe pas ou n'appartient pas à l'utilisateur
if (!$recipe) {
    die('Cette recette n’existe pas ou ne vous appartient pas.');
}


// Suppression
$sqlQuery = '
    DELETE FROM recipes
    WHERE recipe_id = :id
    AND author = :author
';

$deleteRecipe = $db->prepare($sqlQuery);

$deleteRecipe->execute([
    'id' => $id,
    'author' => $_SESSION['LOGGED_USER']['email']
]);


// Retour à l'accueil
header('Location: index.php');
exit;