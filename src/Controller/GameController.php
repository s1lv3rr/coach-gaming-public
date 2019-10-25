<?php
namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;


class GameController extends AbstractFOSRestController
{
       
     /**
     * @Get(
     *  path ="/games",
     *  name ="games")     
     */
    public function getGames(GameRepository $gameRepo)
    {   
        
        $view = View::create();
        $context = new Context();        
        $context->addGroup('home');    
        $view->setContext($context);
        //Requete custom qui classe les coachs (en propriété de l'objet game) par bonne note.
        $games = $gameRepo->findAllJoinCoachesByRating();      
             
        $view->setData($games);        
        $view->setStatusCode(200);       
                 
        return $this->handleView($view);      

    }

    /**
    * @Get(
    *  path ="/game/{slug}",
    *  name ="game")          
    */
    public function getGame(Game $game = null, GameRepository $gameRepo)
    {   
    
       if(is_null($game)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'est pas disponible",                    
                ]
            ];

            $view = $this->view($json, 404);                     
            return $this->handleView($view);
       }

        $view = View::create();
        $context = new Context();        
        $context->addGroup('home');    
        $view->setContext($context);
                
        $currentGame = $gameRepo->findOneByJoinCoachesByRating($game->getName());      
             
        $view->setData($currentGame);       
        $view->setStatusCode(200);

        return $this->handleView($view);
    }   
    
}
