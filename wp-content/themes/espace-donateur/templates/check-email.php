<?php
/* Template Name: Check email */
get_header();

$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {

	$data = get_salesforce_user_data($_POST['email']);
	if (isset($data->hasEspaceDon)) {

		if ($data->hasEspaceDon) {
			$success_message = "Redirection vers la page de connexion";

			// wp_redirect(get_permalink( get_page_by_path( 'creer-un-compte' ) ));
		} else {
			$success_message = "L'utilisateur n'a pas accès à l'espace don";
		}
	}
}
?>

<?php if (isset($success_message)) : ?>
	<div class="success-message"><?php echo $success_message; ?></div>
<?php endif; ?>



<main class="wp-block-group is-layout-flow wp-block-group-is-layout-flow">

	<div class="container">


		<header class="wp-block-group article-header is-layout-flow wp-block-group-is-layout-flow">
			<h1 class="article-title wp-block-post-title">Qui êtes vous ?</h1>
		</header>



		<p>Nous avons besoin de votre email pour déterminer si vous avez êtes déja connu</p>

		<form role="form" method="POST" action="">
			<label>Votre adresse email</label>
			<div>
				<input placeholder="" value="" type="email" name="email" required="true">
				<button aria-label="Rechercher" class="btn btn--dark" type="submit">Vérifier votre email</button>
			</div>
		</form>



	</div>

</main>