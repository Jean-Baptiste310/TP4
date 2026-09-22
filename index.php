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

            <?php

            $averageQuery = $db->prepare('
                SELECT ROUND(AVG(review), 2) AS moyenne
                FROM comments
                WHERE recipe_id = :recipe_id
            ');

            $averageQuery->execute([
                'recipe_id' => $recipe['recipe_id']
            ]);

            $average = $averageQuery->fetch();

            ?>

            <p>
                <strong>
                    Note moyenne :
                </strong>

                <?php if ($average['moyenne'] !== null): ?>

                    <?php echo $average['moyenne']; ?>/5

                <?php else: ?>

                    Aucune note

                <?php endif; ?>
            </p>

            
        <form action="comment.php" method="post" class="mt-3">

            <input
                type="hidden"
                name="recipe_id"
                value="<?php echo $recipe['recipe_id']; ?>"
            >

            <div class="mb-2">
                <label for="comment" class="form-label">
                    Votre commentaire
                </label>

                <textarea
                    class="form-control"
                    id="comment"
                    name="comment"
                    rows="3"
                    required
                ></textarea>
            </div>

            <div class="mb-2">
                <label for="review" class="form-label">
                    Votre note
                </label>

                <select
                    class="form-select"
                    id="review"
                    name="review"
                    required
                >
                    <option value="">-- Choisissez une note --</option>
                    <option value="1">1/5</option>
                    <option value="2">2/5</option>
                    <option value="3">3/5</option>
                    <option value="4">4/5</option>
                    <option value="5">5/5</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                Commenter
            </button>

        </form>

          <?php

        $commentsQuery = $db->prepare('
            SELECT 
                comments.comment,
                comments.review,
                comments.created_at,
                users.full_name
            FROM comments
            JOIN users ON comments.user_id = users.user_id
            WHERE comments.recipe_id = :recipe_id
            ORDER BY comments.comment_id DESC
        ');

            $commentsQuery->execute([
                'recipe_id' => $recipe['recipe_id']
            ]);

            $comments = $commentsQuery->fetchAll();

            ?>

            <h4 class="mt-3">Commentaires</h4>

            <?php foreach ($comments as $comment): ?>

                <div class="border rounded p-2 mb-2">

                    <strong>
                        <?php echo htmlspecialchars($comment['full_name']); ?>
                    </strong>

                    <p class="mb-0">
                        <?php echo htmlspecialchars($comment['comment']); ?>
                    </p>

                    <small class="text-muted">
                        Note : <?php echo htmlspecialchars($comment['review']); ?>/5
                        -
                        <?php echo htmlspecialchars($comment['created_at']); ?>
                    </small>

                </div>

            <?php endforeach; ?>

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