<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Image;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller{

    public function indexAction($page){
        if ($page < 1) {
            throw new NotFoundHttpException('Page "'.$page.'" inexistante.');
        }

        // Notre liste d'annonce en dur
        $listAdverts = array(
            array(
                'title'   => 'Recherche développpeur Symfony',
                'id'      => 1,
                'author'  => 'Alexandre',
                'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Mission de webmaster',
                'id'      => 2,
                'author'  => 'Hugo',
                'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
                'date'    => new \Datetime()),
            array(
                'title'   => 'Offre de stage webdesigner',
                'id'      => 3,
                'author'  => 'Mathieu',
                'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
                'date'    => new \Datetime())
        );

        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
            'listAdverts' => $listAdverts
        ));
    }


    //-------------------------------- Afficher
    public function viewAction($id){
        //**** GET L'ENTITY MANAGER
        $em = $this->getDoctrine()->getManager();
        //**** GET LE REPOSITORY
        $repository = $em->getRepository("OCPlatformBundle:Advert");

        //**** GET L'ENTITE COURANTE
        $advert = $repository->find($id);

        //**** GET LES APPLICATIONS
        $listApplications = $em->getRepository("OCPlatformBundle:Application")->findBy(array("advert" => $advert));

        if( null === $advert ){
            throw new NotFoundHttpException("L'annonce ". $id ." n'existe pas !");
        }

        return $this->render('OCPlatformBundle:Advert:view.html.twig', array(
            "advert" => $advert,
            "listApplications" => $listApplications
        ));
    }


    //-------------------------------- Ajouter
    public function addAction(Request $request){
        //**** GET L'ENTITY MANAGER
        $em = $this->getDoctrine()->getManager();

        //**** CREATION DE L'ENTITE ADVERT
        $advert = new Advert();
        $advert->setTitle("Assistant chef de projet SEO");
        $advert->setAuthor("Frank Gesner");
        $advert->setContent("Vous un passionné du référencement naturel, alors venez chez nous !");
            //Date et publication sont définies par défaut
            /*$advert2 = $em->getRepository("OCPlatformBundle:Advert")->find(1);
            $advert2->setAuthor("Jean Charles");*/

        //**** CREATION DE L'ENTITE IMAGE
        $image = new Image();
        $image->setAlt("Image Assistant SEO");
        $image->setUrl("http://www.needava.com/wp-content/uploads/2016/06/Expert-In-Technical-SEO1.jpg");

        //**** ON RELIT L'IMAGE A L'ANNONCE$
        $advert->setImage($image);

        //**** CREATION DE L'ENTITE APPLICATION
        $application1 = new Application();
        $application1->setAuthor("Grégoire Margotton");
        $application1->setContent("Je suis motivé comme jamais !");
        $application1->setAdvert($advert);

        $application2 = new Application();
        $application2->setAuthor("Hakim Kotni");
        $application2->setContent("J'ai le profil que vous recherchez !");
        $application2->setAdvert($advert);

        //**** ON PERSISTE L'ENTITE | Vu que la relation entre l'image et l'advert est de type 1-1 on n'a pas besoin de persister à nouveau l'image
        $em->persist($advert);
        $em->persist($application1);
        $em->persist($application2);

        //**** ON FLUSH L'ENTITY MANAGER (TOUT CE QUI A ETE PERSISTE)
        $em->flush();

        // Si la requête est en POST, c'est que le visiteur a soumis le formulaire
        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            // Puis on redirige vers la page de visualisation de cettte annonce
            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()) );
        }

        // Si on n'est pas en POST, alors on affiche le formulaire
        return $this->render('OCPlatformBundle:Advert:add.html.twig');
    }


    //-------------------------------- Modifier
    public function editAction($id, Request $request){
        //**** ON CHOPE L'ENTITY MANAGER
        $em = $this->getDoctrine()->getManager();

        //**** ON CHOPE L'ANNONCE PAR SON ID
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);

        //**** SI l'id est NULL
        if( null === $advert ){
            throw new HttpException("Cette annonce n'existe pas !");
        }

        //*** La méthode findAll retourne toutes les catégories de la base de données
        $categories = $em->getRepository("OCPlatformBundle:Category")->findAll();
        //**** On boucle sur les catégories pour les lier à l'annonce
        foreach ($categories as $category){
            $advert->addCategory($category);
        }
        //** Pour persister le changement dans la relation, il faut persister l'entité propriétaire. Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine
        $em->flush();

        if ($request->isMethod('POST')) {
            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien modifiée.');

            return $this->redirectToRoute('oc_platform_view', array('id' => $advert->getId()));
        }

        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array(
            'advert' => $advert
        ));
    }


    //-------------------------------- Modifier une image
    public function editImageAction($advertId){
        //**** GET L'ENTITY MANAGER
        $em = $this->getDoctrine()->getManager();

        //**** ON RECUPERE L'IMAGE DE L'ARTICLE CONCERNE
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($advertId);
        $advert->getImage()->setUrl("http://www.seocktail.fr/wp-content/uploads/2015/11/seo.jpg");

        $em->persist($advert);
        $em->flush();

        return $this->redirectToRoute("oc_platform_view", array(
            "id" => $advert->getId()
        ));

    }


    //-------------------------------- Supprimer
    public function deleteAction($id){
        //**** GET L'ENTITY MANAGER
        $em = $this->getDoctrine()->getManager();

        //**** ON GET L'ANNONCE CONCERNE
        $advert = $em->getRepository("OCPlatformBundle:Advert")->find($id);

        if( null === $advert ){
            throw new NotFoundHttpException("Cette annonce n'existe pas !");
        }

        //**** On boucle sur les catégories de l'annonce pour les supprimer
        foreach ($advert->getCategories() as $category){
            $advert->removeCategory($category);
        }
        //**** Pour persister le changement dans la relation, il faut persister l'entité propriétaire. Ici, Advert est le propriétaire, donc inutile de la persister car on l'a récupérée depuis Doctrine

        $em->flush();

        return $this->render('OCPlatformBundle:Advert:delete.html.twig');
    }

    //-------------------------------- Menu Action
    public function menuAction($limit){
        // On fixe en dur une liste ici, bien entendu par la suite on la récupérera depuis la BDD !
        $listAdverts = array(
            array('id' => 6, 'title' => 'Recherche développeur Symfony'),
            array('id' => 5, 'title' => 'Mission de webmaster'),
            array('id' => 9, 'title' => 'Offre de stage webdesigner')
        );

        return $this->render('OCPlatformBundle:Advert:menu.html.twig', array(
            // Tout l'intérêt est ici : le contrôleur passe les variables nécessaires au template !
            'listAdverts' => $listAdverts
        ));
    }
}
