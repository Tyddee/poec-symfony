<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// classe servant à la sérialisation (passage d'objet à chaîne de caractères)
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

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
/*        $repository = $this
                        ->getDoctrine()
                        ->getManager()
                        ->getRepository('AppBundle:Player');*/

        //$players = $repository->findAll();

        $em = $this->getDoctrine()->getManager();
        $playerRepo = $em->getRepository('AppBundle:Player');

        //listing peut être appelée avc ou sans argument
        //Sans argument spécifié ci dessous ds listing, la valeur par défaut (100) sera appliquée
        $players = $playerRepo->listing();
        //var_dump($players);

        return $this->render('player/index.html.twig', array(
            'title'         => $title,
            'message'       => 'Symfony semble formidable',
            'joueur1'       => $joueur1,
            'joueurs'       => $joueurs,
            'players'       => $players
        ));
    }

    /**
     * @Route("/json/players")
     */
    public function jsonIndexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $playerRepo = $em->getRepository('AppBundle:Player');
        $players = $playerRepo->listing();

        //Impératif: encoder le tableau d'objets Player en json
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $jsonPlayers = $serializer->serialize($players, 'json');
        //test
        //$t = ["nom" => "toto", "age" => 99];
        $res = new Response();

        // On autorise les requêtes provenant d'une origine différente (CROSS-DOMAIN). Ici, symfony "tourne" sur le port 8000, on autorise le traitement de requêtes provenant du port 80 (localhost:80)
        $res->headers->set('Access-Control-Allow-Origin', 'http://localhost');

        //json_encode fonctionne sur un tableau associatif.
        // mais ne parvient pas à encoder correctement un objet.
        //$res->setContent(json_encode($players));
        $res->setContent($jsonPlayers);

        return $res;
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
            $player->setEquipe($request->get('equipe'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($player);
            $em->flush();

            //redirection vers la page d'accueil
            return $this->redirectToRoute('homepage');

        } else {
            // récupérer la liste des équipes
            $em = $this->getDoctrine()->getManager();
            $repo = $em->getRepository('AppBundle:Team');
            $teams = $repo->findAll();

            //echo 'requête en GET';
            // Si la route est demandée en GET, on renvoie un formulaire
            return $this->render('player/forms/add.html.twig', array(
                'teams' => $teams
            ));
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
        $em = $this
                ->getDoctrine()
                ->getManager();

        $playerRepo = $em->getRepository('AppBundle:Player');
        //$teamRepo   = $em->getRepository('AppBundle:Team');

        // En l'absence de relation OneToOne spécifiée au niveau de la classe Player, il faut manuellement récupérer les données de l'équipe en fonction de l'identifiant du joueur
        // Si la relation OneToOne est définie, symfony se charge des jointures, de l'instanciation des objets et de l'hydratation

        // récupération de l'id
        //$id = $request ->query->get('id'); // renvoie NULL
        //var_dump($id);

        // Trouver le joueur correspondant en base de données
        $player = $playerRepo->find($id); // find() == findById() cherche tjs ds la colonne id de la table sql
        //var_dump($player);

        /*$teamId     = $player->getEquipe();

        if ($teamId != 0) {
            $teamName = $teamRepo->find($teamId)->getNom();
        } else {
            $teamName = 'Sans équipe';
        }*/



        // Afficher les inforamtions via une vue/template (fichier twig)
        // render() associe la vue (fichier .twig) passé en premier argument avec le tableau associatif passé en deuxième argument
        // Les données que le controller fournit à la vue seront accessible (affichables, itérables, etc..) par cette dernière.
        return $this->render('player/detail.html.twig', array(
            'player' => $player,
        ));
    }

}
