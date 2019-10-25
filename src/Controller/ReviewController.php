<?php

namespace App\Controller;


use App\Entity\Review;
use App\Form\ReviewType;
use App\Repository\UserRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ReviewController extends AbstractFOSRestController
{   
    /**
    * @Get(
    *  path ="/reviews",
    *  name ="reviews")          
    */
    public function getReviews(ReviewRepository $reviewRepo)
    {
        $view = View::create();
        $context = new Context();        
        $context->addGroup('home');    
        $view->setContext($context);
        //Requete custom qui renvoie 3 reviews par date de création pour la home
        $reviews = $reviewRepo->findThreeByCreation();       
             
        $view->setData($reviews);        
        $view->setStatusCode(200);       
                 
        return $this->handleView($view);       
    }
    
    /**
     * @Post(
     *  path ="/api/add/review/{userId}/coach/{coachId}",
     *  name ="add_review")
     * @ParamConverter("review", converter="fos_rest.request_body")      
     */
    public function addReview(Request $request, Review $review, EntityManagerInterface $em, UserRepository $userRepo ,int $userId, int $coachId, ReviewRepository $reviewRepo)
    {   
        
        //Respectivement, le user identifié par son token, le user (user supposé envoyé par url) qui envoi une review et le coach (coach supposé envoyé par url) qui recois une review
        $currentUser = $this->getUser();        
        $user = $userRepo->find($userId);
        $coach = $userRepo->find($coachId);

        //Si l'un ou l'autre est nul je revoie une 404
        if(is_null($user) || is_null($coach)) {

            $json = [
                ["log" => [
                    "error" => "Le user ou le coach n'existe pas",]                    
                ]
            ];

            $view = $this->view($json, 404);                    
            return $this->handleView($view);
        }
        //Si la personne (identifié par son token) ne correspond pas a la personne envoyé dans l'url, alors il n'a pas le droit d'utiliser cette route.
        if($currentUser !== $user) {

            $json = [
                ["log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route",]                    
                ]
            ];

            $view = $this->view($json, 403);                   
            return $this->handleView($view);

        }
        //Nous n'autorisons seulement les reviews d'un user vers un coach, dernière verif a passer, le user a t-il bien le role user et le coach a t-il bien le role coach?
        if(!($user->getRole()->getName() == 'user' && $coach->getRole()->getName() == 'coach')){
        //Si ce n'est pas le cas : erreur...    
            $json = [
                ["log" => [
                    "error" => "Une review ne peut seulement être fait d'un joueur vers un coach"]
                ],
            ];
            
            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        $isReviewAlreadyExisting = $reviewRepo->findOneByUserAndInfocoach($user, $coach->getInfoCoach());
        //Je verifie que ce user n'as pas déjà posté une review pour ce coach
        if(!is_null($isReviewAlreadyExisting)) {

            $json = [
                ["log" => [
                    "error" => "Vous avez déjà posté une review pour ce coach"]
                ],
            ];
            
            $view = $this->view($json, 400);            
            return $this->handleView($view);

        }

        //creation du form, je passe l'objet review recupéré grace au paramconverter en second argument
        $form = $this->createForm(ReviewType::class, $review);
        $form->submit($request->request->all());
        //Les contraintes de champ (validator symfony) sont définies dans l'entité Review, dans les annotations @Assert
        if (false === $form->isValid()) {
            //Si les champs ne sont pas conformes, le json renvoyé contiendra les messages d'erreurs correctes.
            return $this->handleView(
                $this->view($form)
            );
        }
                 
        //Sinon je continue mon traitement...
        $infoCoach = $coach->getInfoCoach();  
        $review->setUser($user);
        $review->setinfoCoach($infoCoach);
        $em->persist($review);
        $em->flush();  

        //Après le flush je m'assure que mon objet contient un ID, ce qui signifie qu'il existe bien en BDD..        
        if ($review->getId() !== null) {
            //Si la review à bien été créé 
            //Je récupère tout les reviews pour un coach et je calcul sa note moyenne a stocker dans son entité "infoCoach"
            //Surement faisable en requete custom...
            $nbreview = 0;
            $ratingTotal = 0;
            
            foreach($infoCoach->getReviews() as $review) {
    
                $nbreview ++;
                $ratingTotal += $review->getRating();
            }
    
            $coachRating = $ratingTotal/$nbreview;
            $infoCoach->setRating($coachRating);
            $em->flush();

            $json = [
                "log" => [
                    "success" => "Enregistrement effectué"
                ],
            ];

            $view = $this->view($json, 201);            
            return $this->handleView($view);
        }

        //Ultime message d'erreur dans le cas ou je ne recupere pas l'id de mon objet.
        $json = [
            "log" => [
                "error" => "Une erreur est survenue"
            ],
        ];
        
        $view = $this->view($json, 400);       
        return $this->handleView($view);
        
    }

    //Une review ne peut-être modifiée ou supprimée, cela fait partie des historiques de commandes
}
