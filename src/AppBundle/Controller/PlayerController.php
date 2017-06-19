<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Player;

class PlayerController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */

    // la syntaxe Request $request équivaut à $request = new Request()
    public function indexAction(Request $request)
    {
        $title = 'Liste des joueurs';

        $joueur1 = ['nom' => 'Bonnucci', 'prenom' => 'Leo', 'age' => 29];
        $joueur2 = ['nom' => 'Chiellini', 'prenom' => 'Giorgio', 'age' => 34];
        $joueur3 = ['nom' => 'Barzagli', 'prenom' => 'Andrea', 'age' => 36];

        $joueurs = [$joueur1, $joueur2, $joueur3];

        // chargement des joueurs depuis la base de données
        // Récupération du repository pr les opérations en lecture (pas utile pr les autres op CRUD).
        // Le repository est un instrument (objet) permettant de récupérer les données
        // Il propose de nombreuses méthodes de récupération de données (ex: findAll(), findById(), etc...)
        $repository = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Player');

        $players = $repository->findAll();

        return $this->render('player/index.html.twig', array(
            'title'         => $title,
            'message'       => 'Symfony semble formidable',
            'joueur1'       => $joueur1,
            'joueurs'       => $joueurs,
            'players'       => $players
        ));
    }

    /**
     * @Route("/test/player/add", name="testaddplayer")
     */
    public function testAddAction(Request $request)
    {
        $player = new Player();
        $player->setNom("Diego Armando");
        $player->setPrenom("Maradonna");
        $player->setAge(54);
        $player->setMaillot(10);

        // récupération de l'Entity Manager
        // objet permettant in fine d'intéragir avec la base
        $em = $this->getDoctrine()->getManager();

        // étape 1 : on "persiste" les données => enregistrement
        $em->persist($player);

        // étape 2 : nettoyage
        $em->flush();

        // on DOIT retourner une réponse HTTP au client
        return new Response('joueur ajouté avec succès');
    }

    /**
     * @Route("/player/add", name="addplayer")
     */
    public function addAction(Request $request)
    {
        //déterminer si cette route a été demandée en POST ou en GET
        //var_dump($request);
        if ($request->isMethod('POST')) {
            $player = new Player();
            $player->setNom($request->get('nom'));
            $player->setPrenom($request->get('prenom'));
            $player->setAge($request->get('age'));
            $player->setMaillot($request->get('maillot'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();

            //redirection vers la page d'accueil
            return $this->redirectToRoute('homepage');

        } else {
            //echo 'requête en GET';
            // Si la route est demandée en GET, on renvoie un formulaire
            return $this->render('player/forms/add.html.twig');
        }

    }

    /**
     * @Route("/player/{id}", name="detail_player")
     */
    public function detailAction($id)
    {
        //->getDoctrine()   Récupère l'ORM
        //->getManager()    Outil pour opération en écriture
        //->getRepository() Outils pour opération en lecture
        $repository = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Player');

        // récupération de l'id
        //$id = $request ->query->get('id'); // renvoie NULL
        //var_dump($id);

        // Trouver le joueur correspondant en base de données
        $player = $repository->find($id); // find() == findById() cherche tjs ds la colonne id de la table sql
        //var_dump($player);

        // Afficher les inforamtions via une vue/template (fichier twig)
        // render() associe la vue (fichier .twig) passé en premier arguement avec le tableau associatif passé en deuxième argument
        // Les données que le controller fournit à la vue seront accessible (affichables, itérables, etc..) par cette dernière.
        return $this->render('player/detail.html.twig', array(
            'player' => $player
        ));
    }

}
