<?php
namespace App\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use App\Entity\User;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use App\Repository\ReviewRepository;
use App\Repository\InfoCoachRepository;


class CoachController extends AbstractFOSRestController
{
    
    /**
     * @Get(
     *  path ="/coachs",
     *  name ="coachs")     
     */
    public function getCoachs(InfoCoachRepository $infoCoachRepo)
    {   

        $view = View::create();
        $context = new Context();        
        $context->addGroup('home');    
        $view->setContext($context);
        //Requete custom qui classe les coachs (en propriété de l'objet game) par bonne note.
        $coachs = $infoCoachRepo->findSixByRating();      
             
        $view->setData($coachs);        
        $view->setStatusCode(200);       
                 
        return $this->handleView($view);    

    }

    /**
     * @Get(
     *  path ="/coach/{slug}",
     *  name ="coach")     
     */
    public function getCoach(User $user = null, InfoCoachRepository $infoCoachRepo) 
    {   
        
       //Si le user n'existe pas ou si il n'a pas d'infoCoach (dans la logique il n'est donc pas coach) alors je renvoie un erreur 
        if(is_null($user)) {

            $json = [
                "log" => [
                    "error" => "Ce user n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                  
            return $this->handleView($view);
       }

        if(is_null($user->getInfoCoach())) {

            $json = [
                "log" => [
                    "error" => "Ce user n'est pas coach ou n'a pas d'info coach",                    
                ]
            ];

            $view = $this->view($json, 403);                    
            return $this->handleView($view);

       }

        $view = View::create();
        $context = new Context();        
        $context->addGroup('detail-coach');    
        $view->setContext($context);
       
        $infoCoach = $infoCoachRepo->joinForCoachDetails($user->getInfoCoach());
        $view->setData($infoCoach);
        
        $view->setStatusCode(200);                 
        return $this->handleView($view);
    }

    /**
    * @Get(
    *  path ="/coach/{slug}/reviews",
    *  name ="reviews_coach")          
    */
    public function getCoachReviews(User $user = null, ReviewRepository $reviewRepo) 
    {   
        //Si le user n'existe pas ou si il n'a pas d'infoCoach (dans la logique il n'est donc pas coach) alors je renvoie un erreur
        if(is_null($user) || is_null($user->getInfoCoach())) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                   
            return $this->handleView($view);
       }              

        $view = View::create();
        $context = new Context();        
        $context->addGroup('detail-coach');    
        $view->setContext($context);

        //Trouve les reviews grace a l'id d'un coach...        
        $reviews = $reviewRepo->findReviewsByUser($user->getInfoCoach()->getId());        

        $view->setData($reviews);        
        $view->setStatusCode(200);
                 
        return $this->handleView($view);
    }    
}
    