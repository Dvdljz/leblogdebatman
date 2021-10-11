# Projet Le Blog de Batman

## Installation

### Clôner le projet:

    https://github.com/Dvdljz/leblogdebatman.git

### Modifier les params d'environnement dans le fichier .env (changer user_db et password_db)

### Déplacer le terminal dans le dossier clôné:

    cd leblogdebatman

### Taper les commandes suivantes:

    composer install
    symfony console doctrine:database:create
    symfony console make:migration
    symfony console doctrine:migrations:migrate
    symfony console doctrine:fixtures:load

Les fixtures créeront: 
* Un compte admin (email: admin@a.fr, password: Aaaaaa/8)
* 50 comptes utilisateurs (email aléatoire, password: Aaaaaa/8)
* 200 articles aléatoires
* Entre 0 et 10 commentaires par article