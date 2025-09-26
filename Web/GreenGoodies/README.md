Green Goodies

> Site e-commerce d’une boutique lyonnaise spécialisée dans le **bien-être**, la **relaxation** et le **développement personnel**.  
> Application web (catalogue, panier, commandes) + **API JWT** (accès conditionné à l’activation dans le profil).

---

## 🚀 Démarrage rapide

```bash
# 1) Cloner le dépôt
git clone https://github.com/lorlixe/GreenGoodies
cd green-goodies

# 2) Installer les dépendances
composer install

# 3) Configuration — .env.local
Crée le fichier .env.local à la racine :
Copier et modifier le code
###> doctrine/doctrine-bundle ###
DATABASE_URL="mysql://user:password@127.0.0.1:3306/green_goodies?serverVersion=8.0&charset=utf8mb4"
###< doctrine/doctrine-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_PASSPHRASE="change-me-strong-passphrase"
###< lexik/jwt-authentication-bundle ###

#4) Clés JWT
php bin/console lexik:jwt:generate-keypair --skip-if-exists


#5) Base de données
Copier le code
php bin/console doctrine:database:create
php bin/console make:migration
php bin/console doctrine:migrations:migrate

#6) Jeu de données (fixtures)

php bin/console doctrine:fixtures:load

#7) Lancer le serveur

symfony server:start

____________________________________
Identifiants de test

user:  admin@example.com / admin1234
_______________________________________________
Fonctionnalités

Catalogue produits (liste + détail)
Panier (ajout, modification des quantités, suppression si quantité = 0)
Commandes (création de ccommande)
Inscription + connexion automatique après inscription
Connexion / déconnexion
Page “Mon compte” (historique des commandes)
Activation de l’accès API
Suppression de compte

```
