# ADR 001 - Connexion/Création de compte

## Point vocabulaire :

1. **SalesForce**: Désigne l'outil SalesForce utilisé par Amnesty pour la gestion des donateurs et des membres
2. **BDD** : désigne la Base de données utilisée par le site web.
3. **2FA** : "2 facteur authentification" (authentification à double facteur), désigne un mécanisme qui demande de valider l'authentification par un autre mécanisme (réception d'un code par email, téléphone, ...) en plus de son mot de passe

## A. Création de compte

### Contexte

Cette fonctionnalité a pour objectif de permettre à un utilisateur de créer un compte

### Synthèse des règles métiers(*)

- la 2FA sert à vérifier que l'utilisateur est bien un humain (et pas un bot)
- Si l'utilisateur n'est pas connu par Saleforce, il ne peut créer de compte

(*) Les règles métiers sont indiquées sur le Linear. 

### Schéma du parcours utilisateur

```mermaid
flowchart TD
    A[Utilisateur voit formulaire de connexion] -->   B{a déja un compte ?}
    B--> |oui|C[Passe à l'étape de connexion]
    B--> |non|D[Remplis le formulaire]
    D --> E{Formulaire valide ?}
    E --> |oui| F[Soummet le formulaire]
    E --> |non| G[Corrige le formulaire]
    G --> E
    F--> H[Serveur génère et envoie le code 2FA par email]
    H--> I[Code 2FA envoyé par email]
    I--> J[Utilisateur reçoit le code 2FA]
    J --> K[Utilisateur soumet le code 2FA]
    K--> L{Vérifier le code 2FA}
    L -->|Code valide| M[Le login et le mot de passe de l'utilisateur est stocké en BDD]
    L -->|Code valide| A
    L --> |Code invalide| M[Demande l'envoi d'un nouveau code]
    L --> |Code Invalide| N[Corrige le numéro]
    N --> L
    M --> I
    F --> O[Envoi du contenu du formulaire à SalesForce]
    O --> P{Utilisateur connu sur SalesForce ?}
    P --> |Oui| Q[SalesForce retourne le status]
    P --> |Non| R[L'utilisateur voit un écran lui proposant de devenir donateur]

    
    
```

### Questions en suspens :


1. Que stocke-t-on en BDD ?

Réponse préférable : le strict minimum. Que le login et le mot de passe.

2. Quels status à un utilisateur après son inscription ?

## B. Connexion

```mermaid
flowchart TD

  A[Utilisateur voit formulaire de connexion] -->   B{a déja un compte ?}
  B --> |Non| C[Passe à l'étape de création de compte]
  B --> |Oui| D[Remplis son login et mot de passe]
  D --> E[Soummet le formulaire]
  E --> F{Login, mot de passe reconnu par la BDD ?}
  E --> K{Mot de passe oublié ?}
  K --> L[Affichage du formulaire 'mot de passe oublié']
  F --> |non| G[Affichage d'un message d'erreur]
  G --> J[L'utilisateur corrige son login et mot de passe]
  J --> E
  F --> |oui| H[Récupération du status chez SalesForce]
  H --> I[Affichage de la page d'acceuil en mode connecté]

```