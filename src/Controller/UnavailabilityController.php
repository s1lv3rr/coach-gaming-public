<?php
namespace App\Controller;

use App\Entity\User;
use FOS\RestBundle\View\View;
use App\Entity\Unavailability;
use App\Form\UnavailabilityType;
use FOS\RestBundle\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UnavailabilityRepository;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\AbstractFOSRestController;

class UnavailabilityController extends AbstractFOSRestController
{
    /**
     * @Get(
     *  path ="/coach/{id}/unavailabilities",
     *  name ="coach_unavailabilities")     
     */
    public function getCoachUnvailabilities(User $user, UnavailabilityRepository $unavailabilityRepo)
    {   

        $view = View::create();
        $context = new Context();        
        $context->addGroup('unavailability');    
        $view->setContext($context);

        $infoCoach = $user->getInfoCoach();
        //Requete custom qui classe les coachs (en propriété de l'objet game) par bonne note.
        $unavailabilities = $unavailabilityRepo->findByInfoCoach($infoCoach);      
             
        $view->setData($unavailabilities);        
        $view->setStatusCode(200);       
                 
        return $this->handleView($view);      

    }


    /**
     * @Post(
     *  path ="api/add/unavailabilitie/coach/{id}",
     *  name ="add_coach_unavailabilities")  
     */
    public function addAvailabilitie(EntityManagerInterface $em, Request $request, User $user = null, UnavailabilityRepository $unavailabilityRepo)
    {   
        //Je n'utilise pas le ParamConverter pour hydrater directement un objet, je veux verifier les dates manuellement avant traitement

        if(is_null($user)) {
            $json = [
                "log" => [
                    "error" => "Ce coach n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);
            return $this->handleView($view);
        }
                
        $currentUser = $this->getUser();
        $currentUserRole = $currentUser->getRole()->getName();
        
        if($currentUserRole == 'coach' && $currentUser !== $user) {
            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $user->getUsername()
                ]
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        $unavailability = new Unavailability();
        $data = $request->request->all();

        //Je recupère les dates de départ et de fin de reservation dans le tableau data qui contient la Request
        $start = $data['start'];
        $end =  $data['end'];
        //L'objet availability contiens les champs "start" et "end" qui sont des datetime mais aussi des champs "startUnix" et "endUnix" qui sont des conversions de ces dernières.
        //Je converti les dates envoyées dans la requete en UnixTimeStamp pour pouvoir faire des comparaison sur les dates de reservation (stockés également en bdd)
        $unixStart = strtotime($start);
        $unixEnd = strtotime($end);
        //La même chose mais en DateTime
        $startDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $start);
        $endDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $end);        

        //Si la convertion datetime echoue et renvoie false, le format envoyé en json est mauvais.
        //J'ai remarqué que le datetime faisait la convertion des secondes..
        //"2020-08-12 16:25:90" sera converti en "2020-08-12 16:26:30".. Mais le unix ne gère pas cela, donc double verif.
        if($startDateTime == false ||  $endDateTime == false || $unixStart == false ||  $unixEnd == false) {

            $json = [
                "log" => [
                    "error" => "Le format de date envoyé n'est pas bon ! (yyyy-mm-dd hh:mm:ss)"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }  
       
        //Si les dates de début et fin sont inversés...
        if($unixStart > $unixEnd) {

            $json = [
                "log" => [
                    "error" => "La date de départ doit être avant la date de fin"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        $startEndDifference = $unixEnd - $unixStart;

        //Si la réservation fait moins d'une heure
        if($startEndDifference < 3600) {

            $json = [
                "log" => [
                    "error" => "Une indisponibilité doit faire au minimum une heure"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        $currentTime = date('m/d/Y h:i:s a', time());
        $currentUnixTime = strtotime($currentTime);

        //Si la réservation commence à une date passée.
        if($unixStart < $currentUnixTime) {
            $json = [
                "log" => [
                    "error" => "Une indisponibilité ne peut commencer a une date/heure déjà passée !"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        //Je récupère toutes les indisponibilitées déjà stocké en BDD pour ce coach.
        $unavailabilities = $unavailabilityRepo->findByInfoCoach($user->getInfoCoach());

        //Pour chaques indisponibilitées du coach, je compare les dates demandées envoyées dans la requête n'empietes pas sur les dates deja stockées en BDD
        foreach($unavailabilities as $undisponibility) {            
            //4 cas de figure :
            //-la réservation demandée commence avant ou en meme temps qu'une réservation déjà enregistrée et fini pendant celle ci.
            if($unixStart <= $undisponibility->getStartUnix() && $unixEnd >= $undisponibility->getStartUnix()) {

                $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                $view = $this->view($json, 400);            
                return $this->handleView($view);
            }
            //-la réservation demandée commence avant ou en meme temps que la fin d'une réservation déjà enregistré et fini après celle ci.
            if($unixStart <= $undisponibility->getEndUnix() && $unixEnd >= $undisponibility->getEndUnix()) {

                $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                $view = $this->view($json, 400);            
                return $this->handleView($view);
            }
            //-la réservation demandée se trouve au millieu d'une réservation déjà enregistrée.
            if($unixStart >= $undisponibility->getStartUnix() && $unixEnd <= $undisponibility->getEndUnix()) {

                $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                $view = $this->view($json, 400);            
                return $this->handleView($view);
            }
            //-la réservation demandée commence avant le début et fini après la fin d'une réservation déjà enregistrée.
            if($unixStart <= $undisponibility->getStartUnix() && $unixEnd >= $undisponibility->getEndUnix()) {

                $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                $view = $this->view($json, 400);            
                return $this->handleView($view);
            }

        }
        //Si tout les tests sont passés je continue mon traitement, je stock les date dans mon nouvel objet Availibility, formats datetime et unix
        $unavailability->setStart($startDateTime);
        $unavailability->setEnd($endDateTime);
        $unavailability->setStartUnix($unixStart);
        $unavailability->setEndUnix($unixEnd);

        $form = $this->createForm(UnavailabilityType::class, $unavailability);
        $form->submit($request->request->all());
        //Si cette action est effectuée par un coach.....
        if ($currentUserRole == 'coach') {  
               
            $infocoach = $user->getInfoCoach();
            $unavailability->setInfoCoach($infocoach);
            //Dans le cas ou cette route est appellé par le coach, il défini une indisponibilité de travailler à cette période
            $em->persist($unavailability);
            $em->flush();
            //Si je récupère bien l'id de la BDD, l'enregistrement est bien effectué.
            if ($unavailability->getId() !== null) {
                $json = [
                    "log" => [
                        "success" => "Enregistrement effectué"
                    ],
                ];
    
                $view = $this->view($json, 201);            
                return $this->handleView($view);
            }

            $json = [
                "log" => [
                    "danger" => "Une erreur c'est produite"
                ],
            ];
    
            $view = $this->view($json, 400);
            return $this->handleView($view);
        }
        //Si à l'inverse, cette route est appelé par un "user" (user token), alors c'est une réservation pour le coach transmis par l'id dans l'url..
        if ($currentUserRole == 'user') {            
               
            $infocoach = $user->getInfoCoach();
            $unavailability->setInfoCoach($infocoach);
            //Même chose que juste au dessus, sauf qu'on ajoute un client (user du token) pour matérialisé que cette indisponibilité est une réservation
            $unavailability->setClient($currentUser);            
            $em->persist($unavailability);
            $em->flush();
            //Si je récupère bien l'id de la BDD, l'enregistrement est bien effectué.
            if ($unavailability->getId() !== null) {
                $json = [
                    "log" => [
                        "success" => "Enregistrement effectué"
                    ],
                ];
    
                $view = $this->view($json, 201);            
                return $this->handleView($view);
            }

            $json = [
                "log" => [
                    "danger" => "Une erreur c'est produite"
                ],
            ];
    
            $view = $this->view($json, 400);
            return $this->handleView($view);
        }
               
    }

    /**
     * @Post(
     *  path ="api/edit/unavailabilitie/{id}",
     *  name ="edit_unavailability")  
     */
    public function editAvailabilitie(Request $request, Unavailability $unavailability = null, UnavailabilityRepository $unavailabilityRepo, EntityManagerInterface $em)
    {   
        
        if (is_null($unavailability)) {
            $json = [
                "log" => [
                    "error" => "Cette indisponibilité/réservation n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);
            return $this->handleView($view);
        }
          
        $currentUser = $this->getUser(); 
        $client =  $unavailability->getClient();
        $coach = $unavailability->getInfoCoach()->getUser();        
        $data = $request->request->all();

        $start = $data['start'];
        $end =  $data['end'];   

        $unixStart = strtotime($start);
        $unixEnd = strtotime($end);

        $startDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $start);
        $endDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $end);

        
        if($startDateTime == false ||  $endDateTime == false || $unixStart == false ||  $unixEnd == false) {

            $json = [
                "log" => [
                    "error" => "Le format de date envoyé n'est pas bon ! (yyyy-mm-dd hh:mm:ss)"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }  
        
        if($unixStart > $unixEnd) {

            $json = [
                "log" => [
                    "error" => "La date de départ doit être avant la date de fin"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        $startEndDifference = $unixEnd - $unixStart;
        
        //Si la réservation fait moins d'une heure
        if($startEndDifference < 3600) {

            $json = [
                "log" => [
                    "error" => "Une indisponibilité doit faire au minimum une heure"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }

        $currentTime = date('m/d/Y h:i:s a', time());
        $currentUnixTime = strtotime($currentTime);

        //Si la réservation commence à une date passée.
        if($unixStart < $currentUnixTime) {
            $json = [
                "log" => [
                    "error" => "Une indisponibilité ne peut commencer a une date/heure déjà passée !"
                    ]                
            ];

            $view = $this->view($json, 400);            
            return $this->handleView($view);
        }
        
        $unavailabilities = $unavailabilityRepo->findByInfoCoach($coach->getInfoCoach());
                
        
        foreach($unavailabilities as $undisponibility) {

            //Dans le cadre d'une mise a jour, je verifie que les dates ne se chevauchent pas sauf pour l'unavailability en cours de traitement, en effet si je veux racourcir une durée par exemple, la nouvelle date envoyer dans la requete risque d'empieter avec elle même (la date envoyé en requete empietera surement avec la date que j'essai de modifier).
            //exemple : un crénaux de 22h a 23h que je modifier en 22h à 22h30...les crénaux ce chavauche donc je ne passe pas la condition, pour eviter ça, je rajoute le test juste en dessous.

            if($unavailability !== $undisponibility) {
            
                if ($unixStart <= $undisponibility->getStartUnix() && $unixEnd >= $undisponibility->getStartUnix()) {
                    $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                    $view = $this->view($json, 400);
                    return $this->handleView($view);
                }
                
                if ($unixStart <= $undisponibility->getEndUnix() && $unixEnd >= $undisponibility->getEndUnix()) {
                    $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                    $view = $this->view($json, 400);
                    return $this->handleView($view);
                }
                
                if ($unixStart >= $undisponibility->getStartUnix() && $unixEnd <= $undisponibility->getEndUnix()) {
                    $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                    $view = $this->view($json, 400);
                    return $this->handleView($view);
                }
                
                if ($unixStart <= $undisponibility->getStartUnix() && $unixEnd >= $undisponibility->getEndUnix()) {
                    $json = [
                    "log" => [
                        "error" => "Vous empiétez sur une date déjà reservée"
                        ]                    
                ];
    
                    $view = $this->view($json, 400);
                    return $this->handleView($view);
                }
            }  
           
        }
        
        $unavailability->setStart($startDateTime);
        $unavailability->setEnd($endDateTime);
        $unavailability->setStartUnix($unixStart);
        $unavailability->setEndUnix($unixEnd);

        $form = $this->createForm(UnavailabilityType::class, $unavailability);
        $form->submit($request->request->all());
        
        if ($currentUser->getRole()->getName() == 'coach') {    
            
            if($currentUser !== $coach) {

                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $coach->getUsername()
                    ]
                ];
    
                $view = $this->view($json, 403);            
                return $this->handleView($view);
            }
               
        $em->flush();

        $json = [
            "log" => [
                "success" => "Modifications effectuées"
            ],
        ];

        $view = $this->view($json, 200);        
        return $this->handleView($view);

        }
        
        if ($currentUser->getRole()->getName() == 'user') { 
            //Dans le cas où cette route est appellé par un user, si le client est null, c'est à dire que cette indisponibilité a été créée par le coach, ce n'est donc pas une réservation passé par un user. Le user n'a pas le droit de modifier cette ressource.
            if(is_null($client)) {

                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette ressource"
                    ]
                ];
    
                $view = $this->view($json, 403);            
                return $this->handleView($view);
            }
            
            if($currentUser !== $client) {

                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette ressource, veuillez vous connecter en tant que " . $client->getUsername()
                    ]
                ];
    
                $view = $this->view($json, 403);            
                return $this->handleView($view);
            }            
                         
        $em->flush();

        $json = [
            "log" => [
                "success" => "Modifications effectuées"
            ],
        ];

        $view = $this->view($json, 200);        
        return $this->handleView($view);

        }
               
    }

    /**
     * @Delete(
     *  path ="api/delete/unavailabilitie/{id}",
     *  name ="edit_unavailability")  
     */
    public function deleteAvailabilitie(Unavailability $unavailability = null, EntityManagerInterface $em)
    {

        if (is_null($unavailability)) {
            $json = [
                "log" => [
                    "error" => "Cette indisponibilité/réservation n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);
            return $this->handleView($view);
        }
          
        $currentUser = $this->getUser(); 
        $client =  $unavailability->getClient();
        $coach = $unavailability->getInfoCoach()->getUser();

        if ($currentUser->getRole()->getName() == 'coach') {

            if ($currentUser !== $coach) {

                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $coach->getUsername()
                    ]
                ];
    
                $view = $this->view($json, 403);
                return $this->handleView($view);
            }

            $em->remove($unavailability);
            $em->flush();

            $json = [
                "log" => [
                    "success" => "Suppression effectuée"
                ],
            ];

            $view = $this->view($json, 200);
            return $this->handleView($view);
        }

        if ($currentUser->getRole()->getName() == 'user') {
            //Dans le cas où cette route est appellé par un user, si le client est null, c'est à dire que cette indisponibilité a été créée par le coach, ce n'est donc pas une réservation passé par un user. Le user n'a pas le droit de modifier cette ressource.
            if (is_null($client)) {

                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette ressource"
                    ]
                ];
    
                $view = $this->view($json, 403);
                return $this->handleView($view);
            }
            
            if ($currentUser !== $client) {
                $json = [
                    "log" => [
                        "error" => "Vous n'avez pas les droits pour accéder à cette ressource, veuillez vous connecter en tant que " . $client->getUsername()
                    ]
                ];
    
                $view = $this->view($json, 403);
                return $this->handleView($view);
            }

        $em->remove($unavailability);
        $em->flush();

        $json = [
            "log" => [
                "success" => "Suppression effectuée"
            ],
        ];

        $view = $this->view($json, 200);
        return $this->handleView($view);

        }
    } 
}
