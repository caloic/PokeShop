# PokéShop - Plateforme de E-Commerce

## Présentation

PokéShop est une application web de commerce électronique développée en PHP, offrant une expérience d'achat en ligne complète et intuitive.

## Technologies Utilisées

- PHP 7.4+
- MySQL
- HTML5
- CSS3
- JavaScript
- Bibliothèques tierces :
    - TCPDF (génération de factures)
    - Papaparse (traitement CSV)

## Fonctionnalités Principales

### Pour les Utilisateurs
- Authentification et inscription sécurisée
- Parcours et recherche de produits
- Gestion de panier dynamique
- Liste de souhaits personnalisable
- Processus de commande complet
- Historique des commandes détaillé
- Système de notation des articles

### Pour les Administrateurs
- Tableau de bord avec statistiques avancées
- Gestion complète des articles
- Gestion des utilisateurs
- Contrôle des accès granulaire

## Installation Rapide

### Prérequis
- PHP 7.4+
- MySQL
- MANP / WAMP / XAMPP
- Composer

### Étapes
1. Cloner le dépôt
2. Importer le schéma SQL (`db.sql`)
3. Configurer `config.php`
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'votre_utilisateur');
   define('DB_PASS', 'votre_mot_de_passe');
   define('DB_NAME', 'pokeshop');
   ```
4. Installer les dépendances Composer
    ```bash
   composer require tecnickcom/tcpdf
    ```
   ```bash
   composer update
    ```
   ```bash
   composer dump-autoload 
   ```

## Structure du Projet

```
php_exam_cano/
│
├── admin/                  # Pages d'administration
├── cart/                   # Gestion du panier
├── product/                # Gestion des produits
├── styles/                 # Feuilles de style CSS
├── uploads/                # Fichiers uploadés
├── config.php              # Configuration de la base de données
└── index.php               # Page d'accueil
```

## Sécurité

- Requêtes SQL préparées
- Criptage des mots de passe avec Bcrypt
- Validation des entrées utilisateur
- Contrôle d'accès basé sur les rôles
- Protection contre les injections SQL
- Gestion sécurisée des sessions

## Base de Données

Tables principales :
- `users`
- `articles`
- `stocks`
- `carts`
- `commandes`
- `commande_articles`
- `wishlist`
- `notes_articles`
- `factures`

## Évolutions Futures

- Système de recommandations
- Intégration de paiement en ligne
- Notifications utilisateurs
- Mode sombre
- Système de récompenses

## Développeur

Dylan ARLIN
Loïc CANO

---