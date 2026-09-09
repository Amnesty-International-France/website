# MAINT-229 — Runbook SRE : correction des liens cassés (404)

> Document d'exécution destiné à l'équipe SRE. Cible : environnements Clever Cloud
> **staging** (`fairness-dev`) puis **production** (`main`).
> Script concerné : `fix-broken-links-maint-229.php` (racine du repo, branche `feature/maint-229`).

---

## 1. Objectif

L'audit technique SEO a recensé une liste de liens internes en erreur 404. Une partie
(les lignes « Les Tilleuls » du CSV) est assignée à l'équipe technique. Le script corrige
ces liens **directement dans le contenu stocké en base** (réécriture d'URL), sans modifier
le code du thème ni les templates.

Critères d'acceptation de l'issue :
- tous les liens assignés sont corrigés ou supprimés ;
- plus aucune 404 générée par ces emplacements.

---

## 2. Ce que fait le script

`fix-broken-links-maint-229.php` est un script **WP-CLI** (exécuté via `wp eval-file`),
sur le modèle de `clean-posts.php` déjà présent dans le repo. Il parcourt :

1. les contenus **publiés et privés** (`post_content`) des types :
   `post, page, tribe_events, actualities-my-space, chronique, training, edh, portrait,
   fiche_pays, landmark, local-structures, petition, press-release` ;
2. les **descriptions de termes** (`wp_terms.description`, toutes taxonomies) — affichées
   sur les pages d'archive `/categorie/{slug}/`.

Il applique trois catégories de corrections :

| Cat | Traitement | Détail |
|-----|-----------|--------|
| **CAT1** | Remplacement de lien | 8 anciens slugs repères/dossiers → nouvelles URLs (cœur du volume) |
| **CAT2** | Fiches pays | Anciennes URLs `?p=ID&post_type=fiche_pays` → `/pays/{slug}/` résolu à l'exécution |
| **CAT3** | Emails Cloudflare | Décodage d'`/cdn-cgi/l/email-protection` → `mailto:` réel, **si présent en base** |

Caractéristiques importantes :

- **Dry-run par défaut** : sans argument, le script n'écrit **rien**, il ne fait que
  produire un rapport. L'écriture n'a lieu qu'avec l'argument explicite `live`.
- **Agnostique au scheme/host** : le matching porte sur le *chemin* de l'URL ; le
  domaine (amnesty.fr, cleverapps.io, localhost…) est préservé tel quel.
- **Idempotent** : ré-exécuter le script ne produit aucun changement supplémentaire.
- **Journalisation** : écrit un log détaillé `fixed_links_maint229.txt` (un ligne par
  post/terme modifié, avec compteurs par catégorie, + résumé final).
- **Correction par URL** : un lien cassé est corrigé **partout où il apparaît**, pas
  seulement sur les pages listées dans l'audit. Effet voulu (maillage interne).

---

## 3. Conséquences / impact

**Ce qui est modifié :**
- `wp_posts.post_content` des posts **`publish` / `private`** contenant un lien cassé CAT1
  (et CAT2/CAT3 le cas échéant).
- `wp_term_taxonomy.description` (table jointe à `wp_terms`) si une description contient
  un lien cassé.

**Ce qui n'est PAS touché :**
- les **révisions** (`post_status = inherit`), brouillons, et tout contenu non publié →
  ils ne sont pas servis aux visiteurs, donc ne génèrent pas de 404 front ;
- le **code** (thème, plugins, templates, patterns) : aucune modification ;
- aucune donnée donateur / PII : seul du contenu éditorial public est concerné.

**Constat sur les données (validé en local sur une copie de la base prod) :**
- **CAT1** = le travail réel (≈ 377 remplacements sur ≈ 320 posts publics).
- **CAT2** = **0** : les fiches pays sont déjà publiées avec les bons slugs ; les anciennes
  URLs `?p=ID&post_type=fiche_pays` ne sont plus présentes en base et redirigent déjà vers
  `/pays/{slug}/`. Le script reste un filet de sécurité (no-op attendu).
- **CAT3** = **0** : `/cdn-cgi/l/email-protection` est généré **à la volée par Cloudflare**
  dans le HTML servi ; le contenu stocké contient déjà le vrai email. Rien à corriger en base.
  (Ces 404 disparaîtront côté Cloudflare, pas via ce script.)

> Les chiffres exacts en prod peuvent varier légèrement, mais l'ordre de grandeur et la
> répartition (CAT1 ≫ CAT2 = CAT3 = 0) sont attendus identiques.

---

## 4. Pré-requis & précautions

- Exécuter **staging d'abord**, valider, puis production.
- **Sauvegarde** : Clever Cloud effectue un backup quotidien de la base. Pour un filet
  supplémentaire, faire un dump ciblé des deux tables avant le `live` (voir §5.2).
- Le script écrit son log dans le **répertoire courant** : se placer dans un dossier
  **inscriptible** (`$HOME` ou `/tmp`) avant de lancer, et référencer le script par son
  chemin absolu.
- Purger les caches après le `live` (cache objet / page éventuel, et le cache HTML/CDN
  Cloudflare sur les pages corrigées).

---

## 5. Commandes

### 5.1 Connexion à l'environnement

```bash
clever applications list
clever link <application_id> --alias aif-wp-staging   # puis aif-wp-prod
clever ssh --alias aif-wp-staging
```

Une fois dans le conteneur, se placer à la racine de l'application (là où est déployé le
repo, contenant `fix-broken-links-maint-229.php`) et vérifier WP-CLI :

```bash
cd /www                 # webroot Clever Cloud (CC_WEBROOT=/www) ; sinon $APP_HOME
wp --info               # si 'wp' introuvable : ~/.local/bin/wp (installé par castor)
ls fix-broken-links-maint-229.php
```

> Travailler depuis un dossier inscriptible pour le log :
> ```bash
> cd "$HOME"
> WP="wp --path=/www"          # adapter --path à la racine WordPress
> ```

### 5.2 Sauvegarde ciblée (optionnelle, avant le live)

```bash
mysqldump -h "$MYSQL_ADDON_HOST" -P "$MYSQL_ADDON_PORT" \
  -u "$MYSQL_ADDON_USER" -p"$MYSQL_ADDON_PASSWORD" \
  --single-transaction --no-tablespaces \
  "$MYSQL_ADDON_DB" wp_posts wp_term_taxonomy \
  > "$HOME/backup-maint229-$(date +%F).sql"
```

### 5.3 Dry-run (aucune écriture)

```bash
cd "$HOME"
$WP eval-file /www/fix-broken-links-maint-229.php
```

Vérifier dans la sortie et dans `fixed_links_maint229.txt` :
- `CAT1` > 0, `CAT2` = 0, `CAT3` = 0 (attendu) ;
- `X fiches pays résolues, 0 non résolues` (si des pays sont « non résolus », les noter
  et remonter avant le live) ;
- nombre de posts impactés cohérent.

### 5.4 Exécution réelle

```bash
$WP eval-file /www/fix-broken-links-maint-229.php live
```

### 5.5 Contrôle d'idempotence (immédiatement après le live)

```bash
$WP eval-file /www/fix-broken-links-maint-229.php      # dry-run : doit afficher CAT1=0
```

Sortie attendue : `… 0 contenant des liens à corriger.` et `CAT1 : 0 | CAT2 : 0 | CAT3 : 0`.

### 5.6 Vérification en base (exemple sur un mapping CAT1)

```bash
# Plus aucun ancien slug dans les contenus publics :
$WP db query "SELECT post_status, COUNT(*) FROM wp_posts
  WHERE post_content LIKE '%reperes/traite-sur-le-commerce-des-armes-tca%'
  GROUP BY post_status;"
# Attendu : uniquement 'inherit' (révisions) et/ou 'draft', AUCUN 'publish'/'private'.
```

### 5.7 Purge des caches

```bash
$WP cache flush
# + purge du cache Cloudflare pour les pages corrigées (dashboard CF ou API).
```

---

## 6. Vérification fonctionnelle finale

Re-crawler un échantillon de pages issues de la colonne « Page où le lien a été trouvé »
de l'audit (inclure 2-3 pages `/actualites/...` et 2-3 `/categorie/{pays}/`) et confirmer
l'absence de 404 sur les liens concernés → clôture des deux critères d'acceptation.

---

## 7. Rollback

- **Filet immédiat** : restaurer le dump ciblé du §5.2 :
  ```bash
  mysql -h "$MYSQL_ADDON_HOST" -P "$MYSQL_ADDON_PORT" \
    -u "$MYSQL_ADDON_USER" -p"$MYSQL_ADDON_PASSWORD" \
    "$MYSQL_ADDON_DB" < "$HOME/backup-maint229-<date>.sql"
  ```
- **Sinon** : restauration via le backup quotidien Clever Cloud.
- Le script étant idempotent et ciblé (remplacement d'URL connues), le risque de
  régression est faible ; le rollback ne concerne que `wp_posts` / `wp_term_taxonomy`.

---

## 8. Annexe — mappings CAT1

| Ancien chemin | Nouveau chemin |
|---|---|
| `/dossiers/investiture-de-trump/` | `/dossiers/ma-vie-sous-trump-recits-amerique-en-resistance/` |
| `/reperes/convention-contre-la-torture/` | `/reperes/torture-traitements-cruels-degradants-inhumains/` |
| `/reperes/crimes-de-guerre-et-crimes-contre-lhumanite/` | `/reperes/terrorisme-crime-de-guerre-crime-contre-lhumanite-que-dit-le-droit-international/` |
| `/reperes/droit-asile/` | `/reperes/definitions-refugie-migrant-demandeur-asile/` |
| `/reperes/droit-des-femmes/` | `/reperes/8-mars-journee-internationale-des-femmes/` |
| `/reperes/migrant/` | `/reperes/definitions-refugie-migrant-demandeur-asile/` |
| `/reperes/traite-sur-le-commerce-des-armes-tca/` | `/reperes/traite-commerce-armes-explication/` |
| `/reperes/traitements-cruels-inhumains-ou-degradants/` | `/reperes/torture-traitements-cruels-degradants-inhumains/` |

CAT2 : 29 fiches pays résolues dynamiquement vers `/pays/{slug}/` (Togo, Russie, Inde,
Algérie, Cambodge, Pakistan, Syrie, France, Égypte, Guinée, Côte d'Ivoire, Philippines,
RDC, Mexique, Burkina Faso, Sénégal, Chine, Kazakhstan, Colombie, Yémen, Kenya, Tunisie,
Burundi, Hong Kong, Jamaïque, Liberia, Brunéi Darussalam, Nauru, Luxembourg).
