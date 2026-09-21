<?php session_start(); // $_SESSION ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site de Recettes - Page d'accueil</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="container">

    <!-- Navigation -->
    <?php include_once('header.php'); ?>

    <!-- Inclusion des fichiers utilitaires -->
    <?php 
        include_once('mysql.php');
        include_once('variables.php');
        include_once('functions.php');
    ?>

    <?php if (isset($_SESSION['LOGGED_USER'])): ?>

    <form action="index.php" method="post">

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="title" class="form-label">
                Titre de la recette
            </label>

            <input
                type="text"
                class="form-control"
                id="title"
                name="title"
                placeholder="Exemple : Gâteau au chocolat"
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
                placeholder="Écrivez votre recette ici..."
                required
            ></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Créer la recette
        </button>

    </form>

<?php else: ?>

    <div class="alert alert-warning">
        Vous devez être connecté pour créer une recette.
    </div>

<?php endif; ?>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['LOGGED_USER'])) {

    // Requête SQL
    $sqlQuery = '
        INSERT INTO recipes(title, recipe, author, is_enabled)
        VALUES(:title, :recipe, :author, :is_enabled)
    ';

    // Préparation
    $insertRecipe = $db->prepare($sqlQuery);

    // Exécution
    $insertRecipe->execute([
        'title' => $_POST['title'],
        'recipe' => $_POST['recipe'],
        'author' => $_SESSION['LOGGED_USER']['email'],
        'is_enabled' => 1
    ]);
}
?>


    <?php

    $sqlQuery = 'SELECT * FROM recipes WHERE is_enabled = 1';

    $recipesStatement = $db->prepare($sqlQuery);

    $recipesStatement->execute();

    $recipes = $recipesStatement->fetchAll();

    ?>

    <!-- Inclusion du formulaire de connexion -->
    <?php include_once('login.php'); ?>
    
    <h1>Site de Recettes !</h1>

    <!-- Si l'utilisateur existe, on affiche les recettes -->
    <?php if (isset($_SESSION['LOGGED_USER'])): ?>

    <?php foreach (getRecipes($recipes) as $recipe): ?>

        <article>

            <h3>
                <?php echo htmlspecialchars($recipe['title']); ?>
            </h3>

            <div>
                <?php echo htmlspecialchars($recipe['recipe']); ?>
            </div>

            <i>
                <?php echo displayAuthor($recipe['author'], $users); ?>
            </i>

            <?php if ($_SESSION['LOGGED_USER']['email'] === $recipe['author']): ?>

                <a
                    href="edition.php?id=<?php echo $recipe['recipe_id']; ?>"
                    class="btn btn-warning mt-2"
                >
                    Modifier
                </a>

                <a
                   href="delete.php?id=<?php echo $recipe['recipe_id']; ?>"
                   class="btn btn-danger mt-2"
                 >
                   Supprimer
                </a>

            <?php endif; ?>

        </article>

    <?php endforeach; ?>

<?php endif; ?>
    </div>

    <?php include_once('footer.php'); ?>
</body>
</html>