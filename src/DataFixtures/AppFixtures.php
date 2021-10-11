<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // Instanciation du Faker (en français !)
        $faker = Faker\Factory::create('fr_FR'); // cf. doc de Faker ;-)

        // Création d'un compte admin
        $admin = new User();

        // Hydratation du compte
        $admin
            ->setEmail('admin@a.fr')
            ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
            ->setPseudonym('Batman')
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword(
                $this->encoder->hashPassword($admin, 'Aaaaaa/8')
            )
        ;

        //Persistence de l'admin
        $manager->persist($admin);

        // Création de 15 comptes utilisateur
        for($i = 0; $i < 15; $i++){

            $user = new User();

            $user
                ->setEmail( $faker->email )
                ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now') )
                ->setPseudonym( $faker->userName )
                // Même mot de passe pour tous les comptes
                ->setPassword($this->encoder->hashPassword($user, 'Aaaaaa/8') )
            ;

            $manager->persist($user);

        }

        // Création de 200 articles
        for($i = 0; $i < 200; $i++){

            $article = new Article();

            $article
                ->setTitle($faker->sentence(10))
                ->setContent($faker->paragraph(15))
//                ->setPublicationDate($faker->dateTimeBetween('-1 year', 'now'))
                ->setAuthor($admin)  // Batman a écrit TOUS les articles !!!
            ;

            $manager->persist($article);

        }

        // TODO: ajouter les commentaires


        // Sauvegarde des nouvelles entités dans la base de données
        $manager->flush();

    }
}
