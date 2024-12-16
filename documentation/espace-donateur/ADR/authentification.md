Voici une version corrigée et améliorée de ton texte, avec des ajustements pour la clarté, la grammaire et la cohérence :

---

# ADR 001 - Création d'un "utilisateur"

## Points de vocabulaire :

1. **SalesForce** : Outil utilisé par Amnesty pour la gestion des donateurs et des membres.
2. **BDD** : Désigne la base de données utilisée par le site web.
3. **2FA** : "Authentification à deux facteurs" (2-Factor Authentication), désigne un mécanisme qui demande de valider l'authentification par un second moyen (réception d'un code par email, téléphone, etc.) en plus du mot de passe.

## Contexte

Toute la gestion des utilisateurs se fera côté SalesForce. Le login/mot de passe sur WordPress ajoutera une étape d'authentification avant d'appeler SalesForce.

## Besoins concernant l'implémentation

Le système devra :

- S'interfacer avec le thème Humanity.
- S'interfacer avec un système d'emailing (Brevo, Mailgun, etc.).
- Permettre l'envoi de mails personnalisés.
- Permettre une modification facile de l'UI.
- S'interfacer avec SalesForce.

## Contraintes

- Minimiser le code personnalisé en favorisant l'utilisation de plugins préexistants.
- Permettre une modification facile de l'UI.
- Éviter les plugins payants.
- Fonctionner avec une solution 2FA.
- Permettre l'envoi de mails.
- Il s'agit d'un espace réservé aux membres, mais ne gère pas l'espace donateur ni les dons.

## Hypothèses

### Plugins "clé en main"

#### Plugin "Humanity Donation"

Lien : [https://github.com/amnestywebsite/humanity-donations?tab=readme-ov-file](https://github.com/amnestywebsite/humanity-donations?tab=readme-ov-file)

**Avantages** :

- Développé par l'équipe d'Amnesty.

**Inconvénients** :

- Repose sur [WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/), un plugin payant (23 $/mois).

### Développer un plugin personnalisé

*Questions* :

- Comment ajouter un login/mot de passe dans la BDD de WordPress ?
- Comment se connecter à SalesForce ?
- Comment organiser le code ? (Plugin personnalisé ?)
- Comment intégrer un service d'emailing ?
- Comment intégrer des templates (pour les différents écrans) dans WordPress ?

**Décision**

Suite à nos recherches, nous n'avons pas trouvé de plugin permettant de répondre à nos besoins. Nous allons développer un plugin personnalisé.

## Organisation du code

Le plugin sera généré via le CLI de WordPress (voir [ici](https://developer.wordpress.org/cli/commands/scaffold/plugin/)).

La logique est que le fichier principal **plugin-slug.php** (remplacer "plugin-slug.php" par le nom du plugin, par exemple **espace-donateur.php**) agira comme un "contrôleur" (dans le sens MVC).

Ce fichier servira à afficher les templates (ou "écrans") en fonction du résultat.

Exemple :

```
SI l'email de l'utilisateur est connu par SalesForce
ALORS
  AFFICHER L'ÉCRAN "CONNEXION"

SINON
   REDIRIGER vers la page d'accueil
```

Chaque template sera stocké dans un dossier dédié.
