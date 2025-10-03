<?php
$newsletter_id = isset($_GET['newsletter_id']) ? intval($_GET['newsletter_id']) : null;
$post = get_post($newsletter_id);
if (!$newsletter_id || !$post || $post->post_type != 'newsletter') {
    echo '<div class="notice notice-error"><p>Aucune newsletter sélectionnée.</p></div>';
    return;
}

$articles = [];
for ($i = 1; $i <= 5; $i++) {
    $article = get_field($i, $post);
    $lien = $article['lien'] ?? null;
    if (!$lien) {
        continue;
    }
    if (!$lien instanceof \WP_Post) {
        $article['lien'] = get_post($lien);
    }
    $articles[] = $article;
}
$public_url = sprintf('%s/voir-newsletter?newsletter_id=%s', get_home_url(), $newsletter_id);
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta name="robots" content="noindex, nofollow"/>
	<meta name="referrer" content="no-referrer"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title><?php echo esc_html(get_the_title()); ?></title>
	<style type="text/css">@import url(https://fonts.googleapis.com/css?family=Oswald:400,600);
		body { background: #fff; }
		table { font-family: sans-serif; }
		.button { cursor: pointer; font-size:20px; background: transparent; border: 2px solid #000; font-family: Oswald, Arial, sans-serif; font-style: normal; text-transform: uppercase; padding: 5px 15px; }
		.button:hover { border-color: #ef8200; color: #ef8200; }
	</style>
	<link href="https://fonts.googleapis.com/css?family=Oswald:400,600" rel="stylesheet"/>
	<img src="https://click.email.amnesty.fr/open.aspx?ffcb10-fe9216747c65017575-fe2d117574640d7f751078-fe8c13727763027972-ff6515717d-fe27107775650d74751076-ff311170756d&d=70247&bmt=0" width="1" height="1" alt="">
</head>
<body style="width:100%;margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%">
<table width="100%" height="100%" cellPadding="0" cellSpacing="0" border="0" align="left" valign="top">
	<tbody>
	<tr>
		<td align="center" valign="top">
			<table width="580px" align="center" cellPadding="0" cellSpacing="0" border="0" valign="top">
				<tbody>
				<tr>
					<td align="center">
						<div style="font-size:5px;height:5px;line-height:5px;mso-line-height-rule:exactly"></div>
						<span style="font-family:sans-serif;font-size:12px;line-height:14px;color:#000">Si ce message ne s&#x27;affiche pas correctement,
                              <a href="<?php echo $public_url; ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:underline">cliquez ici</a>.</span>
					</td>
				</tr>
				<tr>
					<td align="center">
						<div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
						<img alt="L&#x27;Hebdo - Chaque vendredi, l&#x27;essentiel de l&#x27;acutalité des droits humains" src="https://prismic-io.s3.amazonaws.com/lesbonneschoses-vg498smaaleapicq%2F0544d35b-01ef-448c-9f2f-62dd4d8669af_amnesty-mailing-hebdo-header.jpg" width="518" height="81" style="display:block;outline:none;border:none;text-decoration:none"/>
					</td>
				</tr>
				<tr>
					<td>
						<?php foreach ($articles as $article):
						    $post = $article['lien'];
						    setup_postdata($post);
						    $post_url = esc_url(get_permalink($post->ID));
						    $title = !empty($article['titre']) ? $article['titre'] : esc_html(get_the_title($post->ID));
						    $image_url = $article['image'] ?: esc_url(get_the_post_thumbnail_url($post->ID, 'medium'));
						    ?>
							<table align="center" style="border:1px solid #d6d6d6;box-shadow:0 5px 25px 0 #e2e2e2;margin-top:20px;margin-bottom:30px" cellPadding="0" cellSpacing="0" border="0" valign="top">
								<tbody>
								<tr>
									<td><a href="<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none"><img alt="<?php echo get_the_post_thumbnail_caption($post->ID) ?>" src="<?php echo $image_url ?>" width="580" height="295" style="display:block;outline:none;border:none;text-decoration:none"/></a></td>
								</tr>
								<tr>
									<td>
										<table width="100%" cellPadding="10" style="padding:0 10px" cellSpacing="0" border="0" align="left" valign="top">
											<tbody>
											<div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
											<tr>
												<td><a href="<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none"><span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:30px;color:#000;font-style:normal;text-transform:uppercase;text-align:left"><?php echo $title; ?></span></a></td>
											</tr>
											<tr>
												<td>
													<span style="font-family:sans-serif;font-size:14px;line-height:14px;color:#000;text-align:left">
														<?php echo $article['accroche']; ?>
													</span>
												</td>
											</tr>
											<tr>
												<td align="right"><span style="font-family:sans-serif;font-size:14px;line-height:14px;color:#000"><a href="<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" style="color:#EF8200;text-decoration:none"> &gt; <?php echo $article['libelle_du_lien'] ?></a></span></td>
											</tr>
											</tbody>
										</table>
									</td>
								</tr>
								</tbody>
							</table>
						<?php endforeach; ?>
					</td>
				</tr>
				<tr>
					<td align="center">
						<span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:14px;color:#000;font-style:normal;text-transform:uppercase;text-align:left">Donnez-nous les moyens d&#x27;agir</span>
						<div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
					</td>
				</tr>
				<tr>
					<td align="center">
						<button class="button">Devenir Membre</button>
						<div style="font-size:30px;height:30px;line-height:30px;mso-line-height-rule:exactly"></div>
					</td>
				</tr>
				<tr>
					<td style="background-color:black;padding:20px 70px">
						<table cellPadding="0" cellSpacing="0" border="0" align="left" valign="top">
							<tbody>
							<tr>
								<td>
									<table width="100%" style="padding:0 10px" cellPadding="0" cellSpacing="0" border="0" align="left" valign="top">
										<tbody>
										<tr>
											<td width="170"><img alt="Logo Amnesty Internalional France" src="https://prismic-io.s3.amazonaws.com/lesbonneschoses-vg498smaaleapicq%2F7a711500-38f3-4d70-98a6-23e9aee71712_logo_blanc-noir.jpeg" width="151" style="display:block;outline:none;border:none;text-decoration:none"/></td>
											<td vAlign="middle">
												<table cellPadding="5" cellSpacing="0" border="0" align="left" valign="top">
													<tbody>
													<tr>
														<td><span style="font-family:sans-serif;font-size:14px;line-height:14px;color:#989898">Nous suivre:</span></td>
														<td><img alt="facebook" src="https://prismic-io.s3.amazonaws.com/lesbonneschoses-vg498smaaleapicq%2Fceedb361-b2a3-4c74-ae60-9ad38217f946_fb.png" width="36" height="36" style="display:block;outline:none;border:none;text-decoration:none"/></td>
														<td>
															<img alt="twitter" src="https://prismic-io.s3.amazonaws.com/lesbonneschoses-vg498smaaleapicq%2Fb5386c48-07b9-44c4-a9b2-d63ae4e82111_tw.png" width="36" height="36" style="display:block;outline:none;border:none;text-decoration:none"/>
														</td>
														<td><img alt="youtube" src="https://prismic-io.s3.amazonaws.com/lesbonneschoses-vg498smaaleapicq%2F2bdcaaaf-33fb-4648-829b-551449590b76_yt.png" width="36" height="36" style="display:block;outline:none;border:none;text-decoration:none"/></td>
														<td>
															<img alt="instagram" src="https://images.prismic.io/amnestyfr/6c60d5fa-2d61-488d-8b30-f19b02b852ae_instagram.png?auto=compress,format&amp;w=36&amp;h=36" width="36" height="36" style="display:block;outline:none;border:none;text-decoration:none"/>
														</td>
													</tr>
													</tbody>
												</table>
											</td>
										</tr>
										</tbody>
									</table>
									<table cellPadding="0" cellSpacing="0" border="0" align="left" valign="top">
										<tbody>
										<tr>
											<td>
												<div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
												<p style="text-align:center;color:#989898;font-size:11px;line-height:14px">Confidentialité des données : Conformément à la loi française (article 27 de la loi n°78-17 du 6 janvier 1978 relative à l&#x27;informatique, aux fichiers et aux libertés), vous disposez, en vous adressant au Siège d&#x27;Amnesty International, d&#x27;un droit d&#x27;accès, de rectification et d&#x27;opposition aux informations vous concernant. Si vous ne souhaitez plus recevoir d&#x27;e-mails de ce type, vous pouvez vous désinscrire.</p>
											</td>
										</tr>
										</tbody>
									</table>
								</td>
							</tr>
							</tbody>
						</table>
					</td>
				</tr>
				</tbody>
			</table>
		</td>
	</tr>
	</tbody>
</table>
<?php
if (is_admin()):
    ?>
	<button id="copy-source" data-public-url="<?php echo $public_url; ?>" style="position: absolute; top: 60px; right: 3vw; border: 1px solid #000; padding: 8px 12px; background: transparent; cursor: pointer;">
		Copier
	</button>
	<script>
		const button = document.querySelector('#copy-source');
		const publicUrl = button.getAttribute('data-public-url');
		button.addEventListener('click', () => {
			fetch(publicUrl)
				.then(response => response.text())
				.then(data => {
					try {
						navigator.clipboard.writeText(data);
						alert("Le contenu de la newsletter a été copié dans le presse papier.")
					} catch (error) {
						alert('Erreur lors de la récupération du contenu de la newsletter.')
					}
				});
		});
	</script>
<?php endif; ?>
</body>
</html>
