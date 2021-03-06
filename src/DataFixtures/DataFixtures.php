<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class DataFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger= $slugger;
    }
    # Cette fonction load() sera exécuté en ligne de commande avec php bin/console doctrine:fixtures:load --append
        # => le drapeau --append permet de nepas purger la BDD
    public function load(ObjectManager $manager): void
    {
       // Déclaration d'une variable de type array, avec le nom des différentes catégories de NewsActu.
        $categories = [

            'Astronomie',
            'Beauté',
            'Culture',
            'Écologie',
            'Économie',
            'Hi Tech',
            'Informatique',
            'Mode',
            'People',
            'Politique',
            'Santé',
            'Sciences',
            'Société',
            'Sport'
            
        ];
       
       // la boucle foreach() est optimisée pour les tableaux (array).
        // La syntaxe complète à l'interieur des parenthèses est ($key => $value)
        foreach($categories as $cat) {

            //Instanciation d'un objet Categorie()
            $categorie = new Categorie();

            //Appel des setters de notre Objet $categorie
            $categorie->setName($cat);
            $categorie->setAlias($this->slugger->slug($cat));
            $categorie->setCreatedAt(new DateTime());
            $categorie->setUpdateAt(new DateTime());

            // EntityManager, on appel sa méthode persist() pour insérer en BDD l'objet $categorie
            $manager->persist($categorie);
        }
  
        // On vide l'EntityManager pour la uite
        $manager->flush();
    }
}
