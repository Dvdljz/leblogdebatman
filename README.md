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