<?php
session_start();

include_once('mysql.php');
include_once('variables.php');
include_once('functions.php');

if (!isset($_SESSION['LOGGED_USER'])) {
    die('Vous devez être connecté pour modifier une recette.');
}

$id = $_GET['id'] ?? null;


// Récupération de la recette
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

if (!$recipe) {
    die('Cette recette n’existe pas ou ne vous appartient pas.');
}


// Modification de la recette
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $recipeText = trim($_POST['recipe'] ?? '');
    $validation = isset($_POST['validation']);

    if (empty($title)) {
        $errorMessage = 'Le titre est obligatoire.';
    } elseif (empty($recipeText)) {
        $errorMessage = 'La recette est obligatoire.';
    } elseif (!$validation) { 
        $errorMessage = 'Vous devez cocher la case de validation.'; 
        } else {

        $sqlQuery = '
            UPDATE recipes
            SET title = :title,
                recipe = :recipe
            WHERE recipe_id = :id
            AND author = :author
        ';

        $updateRecipe = $db->prepare($sqlQuery);

        $updateRecipe->execute([
            'title' => $title,
            'recipe' => $recipeText,
            'id' => $id,
            'author' => $_SESSION['LOGGED_USER']['email']
        ]);

        $successMessage = 'La recette a bien été modifiée.';

        // Mise à jour des valeurs affichées
        $recipe['title'] = $title;
        $recipe['recipe'] = $recipeText;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier une recette</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container">

    <h1>Modifier une recette</h1>

    <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>


    <form action="edition.php?id=<?php echo $id; ?>" method="post">

        <div class="mb-3">
            <label for="title" class="form-label">
                Titre de la recette
            </label>

            <input
                type="text"
                class="form-control"
                id="title"
                name="title"
                value="<?php echo htmlspecialchars($recipe['title']); ?>"
                required
            >
        </div>


        <div class="mb-3">
            <label for="recipe" class="form-label">
                Recette
            </label>

            <textarea
                class="form-control"
                id="recipe"
                name="recipe"
                rows="8"
                required
            ><?php echo htmlspecialchars($recipe['recipe']); ?></textarea>
        </div>

        <div class="mb-3 form-check">
            <input 
                type="checkbox" 
                class="form-check-input" 
                id="validation" 
                name="validation" 
            >
            <label class="form-check-label" for="validation">
                Je confirme vouloir modifier cette recette.
            </label>
        </div>

        <button type="submit" class="btn btn-primary">
            Modifier la recette
        </button>

    </form>

</div>

</body>
</html>