# Prismic Migration

This plugin creates a wp-cli command to migrate data from Amnesty International France Prismic Repository to Wordpress with humanity-theme.

## Use

Content type are available there : [Type.php](Type.php)

```bash
wp prismic-migration --type=news
```

### Options

**--dry-run** :
```bash
wp prismic-migration --type=news --dry-run
```

**--since** :
```bash
wp prismic-migration --type=news --since=2025-11-01
```

import a precise article with **--id** :
```bash
wp prismic-migration --id=ABCD12345
```

## Repair Links

Execute this command to repair links in posts/pages after an import.

```bash
wp repair-links
```
