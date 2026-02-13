<?php
$newsletter_id = isset($_GET['newsletter_id']) ? intval($_GET['newsletter_id']) : null;
$preview_mode = isset($_GET['preview_mode']) ? $_GET['preview_mode'] : null;
$post = get_post($newsletter_id);
if (!$newsletter_id || !$post || $post->post_type != 'newsletter') {
    echo '<div class="notice notice-error"><p>Aucune newsletter sélectionnée.</p></div>';
    return;
}

$post_title = get_the_title($post->ID);
$utm_campaign_date_part = $post->post_name;

if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $post_title, $matches)) {
    $utm_campaign_date_part = $matches[1] . $matches[2] . $matches[3];
}

$utm_params = [
    'utm_source'   => 'email_newsletter',
    'utm_medium'   => 'email',
    'utm_campaign' => 'newshebdo_' . $utm_campaign_date_part,
];

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
    if (!is_string($article['image'])) {
        $article['image'] = wp_get_original_image_url($article['image']);
    }
    $articles[] = $article;
}
$public_url = sprintf('%s/voir-newsletter?newsletter_id=%s', get_home_url(), $newsletter_id);

$url_non_adherent_base = 'https://soutenir.amnesty.fr/b?cid=24&reserved_originecode=17TWDE1MEN';
$url_adherent_base = 'https://soutenir.amnesty.fr/b?cid=228&lang=fr_FR&reserved_originecode=20FH1E1MEM';

$url_non_adherent_final = add_query_arg($utm_params, $url_non_adherent_base);
$url_adherent_final = add_query_arg($utm_params, $url_adherent_base);
?>
<html xmlns="http://www.w.org/1999/xhtml">
<head>
    <meta name="robots" content="noindex, nofollow"/>
    <meta name="referrer" content="no-referrer"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo esc_html($post_title); ?></title>
    <style type="text/css">@import url(https://fonts.googleapis.com/css?family=Oswald:400,600);
        body { background: #fff; }
        table { font-family: sans-serif; }
        .button { cursor: pointer; font-size:20px; background: transparent; border: 2px solid #000; font-family: Oswald, Arial, sans-serif; font-style: normal; text-transform: uppercase; padding: 5px 15px; }
        .button:hover { border-color: #ef8200; color: #ef8200; }
    </style>
    <link href="https://fonts.googleapis.com/css?family=Oswald:400,600" rel="stylesheet"/>
    <img src="https://click.email.amnesty.fr/open.aspx?ffcb10-fe9216747c65017575-fe2d117574640d7f751078-fe8c13727763027972-ff6515717d-fe27107775650d74751076-ff311170756d&d=70247&bmt=0" width="1" height="1" alt="">
    <custom name="opencounter" type="tracking"/>
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
                            $post_url = esc_url(add_query_arg($utm_params, get_permalink($post->ID)));
                            $title = !empty($article['titre']) ? $article['titre'] : esc_html(get_the_title($post->ID));
                            $featured_image_url = get_the_post_thumbnail_url($post->ID, 'post-featured');
                            if (! $featured_image_url) {
                                $featured_image_url = get_the_post_thumbnail_url($post->ID, 'full');
                            }
                            $image_url = $article['image'] ?: esc_url($featured_image_url);
                            ?>
                            <table align="center" style="border:1px solid #d6d6d6;box-shadow:0 5px 25px 0 #e2e2e2;margin-top:20px;margin-bottom:30px" cellPadding="0" cellSpacing="0" border="0" valign="top">
                                <tbody>
                                <tr>
                                    <td>
                                        <a href="<?php echo $post_url; ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none">
                                            <div style="width:580px;height:295px;overflow:hidden;">
                                                <img alt="<?php echo get_the_post_thumbnail_caption($post->ID) ?>" src="<?php echo $image_url ?>" width="580" height="295" style="display:block;outline:none;border:none;text-decoration:none;width:100%;height:100%;object-fit:cover;"/>
                                            </div>
                                        </a>
                                    </td>
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

                <?php
                if (isset($_GET['get_source'])): ?>
                    %%[IF (([Tech_Adherent_Actif] == false)) THEN]%%
                    <tr>
                        <td align="center">
                            <span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:14px;color:#000;font-style:normal;text-transform:uppercase;text-align:left">Donnez-nous les moyens d'agir</span>
                            <div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="border:2px solid #000000;padding:5px 15px;">
                                        <a href="<?php echo esc_url($url_non_adherent_final); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;font-family:Oswald, Arial, sans-serif;font-size:20px;line-height:20px;color:#000000;text-decoration:none;text-transform:uppercase;mso-line-height-rule:exactly;">
                                            Devenir Membre
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:30px;height:30px;line-height:30px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    %%[ELSE]%%
                    <tr>
                        <td align="center">
                            <span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:14px;color:#000;font-style:normal;text-transform:uppercase;text-align:left">Grâce à vous, nous pouvons agir</span>
                            <div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="border:2px solid #000000;padding:5px 15px;">
                                        <a href="<?php echo esc_url($url_adherent_final); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;font-family:Oswald, Arial, sans-serif;font-size:20px;line-height:20px;color:#000000;text-decoration:none;text-transform:uppercase;mso-line-height-rule:exactly;">
                                            Faire un don
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:30px;height:30px;line-height:30px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    %%[ENDIF]%%
                <?php elseif ($preview_mode === 'adherent'): ?>
                    <tr>
                        <td align="center">
                            <span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:14px;color:#000;font-style:normal;text-transform:uppercase;text-align:left">Grâce à vous, nous pouvons agir</span>
                            <div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="border:2px solid #000000;padding:5px 15px;">
                                        <a href="<?php echo esc_url($url_adherent_final); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;font-family:Oswald, Arial, sans-serif;font-size:20px;line-height:20px;color:#000000;text-decoration:none;text-transform:uppercase;mso-line-height-rule:exactly;">
                                            Faire un don
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:30px;height:30px;line-height:30px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td align="center">
                            <span style="font-family:Oswald, Arial, sans-serif;font-size:24px;line-height:14px;color:#000;font-style:normal;text-transform:uppercase;text-align:left">Donnez-nous les moyens d'agir</span>
                            <div style="font-size:20px;height:20px;line-height:20px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" align="center">
                                <tr>
                                    <td align="center" bgcolor="#ffffff" style="border:2px solid #000000;padding:5px 15px;">
                                        <a href="<?php echo esc_url($url_non_adherent_final); ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;font-family:Oswald, Arial, sans-serif;font-size:20px;line-height:20px;color:#000000;text-decoration:none;text-transform:uppercase;mso-line-height-rule:exactly;">
                                            Devenir Membre
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div style="font-size:30px;height:30px;line-height:30px;mso-line-height-rule:exactly"></div>
                        </td>
                    </tr>
                <?php endif; ?>

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
                                                <p style="text-align:center;color:#989898;font-size:11px;line-height:14px">Confidentialité des données : Conformément à la loi française (article 27 de la loi n°78-17 du 6 janvier 1978 relative à l&#x27;informatique, aux fichiers et aux libertés), vous disposez, en vous adressant au Siège d&#x27;Amnesty International, d&#x27;un droit d&#x27;accès, de rectification et d&#x27;opposition aux informations vous concernant. Si vous ne souhaitez plus recevoir d&#x27;e-mails de ce type, vous pouvez <a href="%%=CloudPagesURL(3321)=%%" target="_blank" rel="noopener noreferrer" style="color:#989898;text-decoration:underline">vous désinscrire</a>.</p>
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
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $url_adherent = add_query_arg('preview_mode', 'adherent', $current_url);
    $url_non_adherent = remove_query_arg('preview_mode', $current_url);
    ?>
    <div id="admin-toolbar" style="position: fixed; top: 10px; right: 10px; background: #fff; border: 1px solid #ccc; padding: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); z-index: 1000; font-family: sans-serif;">
        <h4 style="margin: 0 0 10px 0; padding: 0; font-size: 14px;">Outils d'administration</h4>
        
        <button id="copy-source" data-public-url="<?php echo $public_url; ?>" style="display: block; width: 100%; border: 1px solid #000; padding: 8px 12px; background: transparent; cursor: pointer; margin-bottom: 10px;">
            Copier le code source
        </button>

        <p style="margin: 10px 0 5px; font-size: 12px; color: #555;">Changer la prévisualisation :</p>
        <a href="<?php echo esc_url($url_non_adherent); ?>" style="display: inline-block; padding: 5px 10px; background-color: <?php echo !$preview_mode ? '#0073aa' : '#f0f0f0'; ?>; color: <?php echo !$preview_mode ? '#fff' : '#555'; ?>; text-decoration: none; border-radius: 3px; font-size: 12px;">
            Non-Adhérent (défaut)
        </a>
        <a href="<?php echo esc_url($url_adherent); ?>" style="display: inline-block; padding: 5px 10px; background-color: <?php echo ($preview_mode === 'adherent') ? '#0073aa' : '#f0f0f0'; ?>; color: <?php echo ($preview_mode === 'adherent') ? '#fff' : '#555'; ?>; text-decoration: none; border-radius: 3px; font-size: 12px;">
            Adhérent
        </a>
    </div>

    <script>
        const button = document.querySelector('#copy-source');
        const publicUrl = button.getAttribute('data-public-url');
        button.addEventListener('click', () => {
            fetch(publicUrl + '&get_source=true')
                .then(response => response.text())
                .then(data => {
                    try {
                        navigator.clipboard.writeText(data);
                        alert("Le code source de la newsletter a été copié dans le presse-papier.")
                    } catch (error) {
                        alert('Erreur lors de la récupération du code source de la newsletter.')
                    }
                });
        });
    </script>
<?php endif; ?>
</body>
</html>
