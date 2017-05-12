<?php
//src/OC/PlatformBundle/DataFixtures/ORM/LoadCategory.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Category;

class LoadCategory implements FixtureInterface{
    // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
    public function load(ObjectManager $manager){
        // TODO: Implement load() method.
        //Liste des noms de catégories à ajouter
        $names = array(
            "Développement web",
            "Développement mobile",
            "Graphisme",
            "Intégration",
            "Réseau"
        );

        foreach ($names as $name){
            //on crée la category
            $category = new Category();
            $category->setName($name);

            //On la persiste
            $manager->persist($category);
        }

        //On déclenche l'enregistrement de toutes les catégories
        $manager->flush();
    }
}
