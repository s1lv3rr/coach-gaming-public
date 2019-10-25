<?php
namespace App\Controller;


use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\UserRepository;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\View\View;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Repository\MessageRepository;
use App\Entity\User;

class MessageController extends AbstractFOSRestController
{
    /**
     * @Post(
     *  path ="api/add/message/{userId}/to/{userTwoId}",
     *  name= "add_message")
     * @ParamConverter("message", converter="fos_rest.request_body")     
     */
    public function addMessage(Request $request, Message $message, int $userId, int $userTwoId, EntityManagerInterface $em, UserRepository $userRepo)
    {   
        //Le premier user passé dans l'url (le sender)
        $userOne = $userRepo->find($userId);
        //Le second user passé dans l'url (le receiver)
        $usertwo = $userRepo->find($userTwoId);        
        //Le user envoyé par le Token
        $currentUser = $this->getUser();

        if(is_null($userOne) || is_null($usertwo)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                  
            return $this->handleView($view);
        }

        if($userOne !== $currentUser) {

            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $userOne->getUsername()                   
                ]
            ];

            $view = $this->view($json, 403);                  
            return $this->handleView($view);
        }          
        
        $userOneRole = $userOne->getRole()->getName();
        $usertwoRole = $usertwo->getRole()->getName();
        //Verif pour savoir si c'est bien un echange de user a coach et pas de user a user ou de coach a coach
        if(($userOneRole == 'user' && $usertwoRole == 'coach') || ($userOneRole == 'coach' && $usertwoRole == 'user'))  {

            $message = new Message();
            $form = $this->createForm(MessageType::class, $message);
            $form->submit($request->request->all());

            if (false === $form->isValid()) {
                //Si les champs ne sont pas conformes, le json renvoyé contiendra les messages d'erreurs correctes.
                return $this->handleView($this->view($form));                
                
            }
            //La verif est passée je créé le message, Je set le message comme non lu ainsi que le 'sender' et le 'receiver'
            $message->setIsRead(false);
            $message->setSender($userOne);
            $message->setReceiver($usertwo);
            $em->persist($message);
            $em->flush();

            if ($message->getId() !== null) {
                
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

        $json = [
            "log" => [
                "error" => "Un message ne peut-être envoyé que d'un user vers un coach ou d'un coach vers user"]            
        ];
        
        $view = $this->view($json, 400);        
        return $this->handleView($view);        
    }

    
    /**
     * @Get(
     *  path ="api/conversation/{userId}/to/{userTwoId}",
     *  name = "conversation")      
     */
    public function conversation(int $userId, int $userTwoId, EntityManagerInterface $em, UserRepository $userRepo, MessageRepository $messageRepo) {

        //Le premier user passé dans l'url (le sender)
        $userOne = $userRepo->find($userId);
        //Le second user passé dans l'url (le receiver)
        $userTwo = $userRepo->find($userTwoId);        
        //Le user envoyé par le Token
        $currentUser = $this->getUser();

        if(is_null($userOne) || is_null($userTwo)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                  
            return $this->handleView($view);
        }
       
        if($userOne !== $currentUser) {

            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $userOne->getUsername()                   
                ]
            ];

            $view = $this->view($json, 403);                  
            return $this->handleView($view);
        } 

         $userOneRole = $userOne->getRole()->getName();
         $userTwoRole = $userTwo->getRole()->getName();
        //Verif pour savoir si c'est bien un echange de user a coach et pas de user a user ou de coach a coach
        if (($userOneRole == 'user' && $userTwoRole == 'coach') || ($userOneRole == 'coach' && $userTwoRole == 'user')) {
            
            $conversation = $messageRepo->findConversation($userOne, $userTwo);

            //Pour chaque message reçu, si j'arrive sur cette ressource çela veut dire que j'ai consulté la conversation, je passe tous les messages reçu comme lu
            foreach($conversation as $message) {

                if($message->getReceiver() == $userOne) {
                    $message->setIsRead(true);
                }
            }

            $em->flush();
            
            $view = View::create();
            $context = new Context();        
            $context->addGroup('message');    
            $view->setContext($context);               
                         
            $view->setData($conversation);       
            $view->setStatusCode(200);

            return $this->handleView($view);
        }
    }

    /**
     * @Get(
     *  path ="api/new_messages/{id}",
     *  name= "new_messages")      
     */
    public function getNewMessages(User $user = null, MessageRepository $messageRepo) {

        
        //Le user envoyé par le Token
        $currentUser = $this->getUser();

        if(is_null($user)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                  
            return $this->handleView($view);
        }

        if($user !== $currentUser) {

            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $user->getUsername()                   
                ]
            ];

            $view = $this->view($json, 403);                  
            return $this->handleView($view);
        } 
        //Requete custom
        $newMessages = $messageRepo->findNewMessages($user);
            
        $view = View::create();
        $context = new Context();        
        $context->addGroup('message');    
        $view->setContext($context);               
                        
        $view->setData($newMessages);       
        $view->setStatusCode(200);

        return $this->handleView($view);
        
    }

    /**
     * @Get(
     *  path ="api/conversations/{id}",
     *  name= "get_conversations")      
     */
    public function getConversations(User $user = null, MessageRepository $messageRepo) {

        
        //Le user envoyé par le Token
        $currentUser = $this->getUser();

        if(is_null($user)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                  
            return $this->handleView($view);
        }

        if($user !== $currentUser) {

            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $user->getUsername()                   
                ]
            ];

            $view = $this->view($json, 403);                  
            return $this->handleView($view);
        } 
        //La conception de la messagerie côté BDD est assez simpliste, j'effectue ce traitement pour pouvoir classé les messages par conversations
        //Je récupère tous les messages du User, classé par date de création ASC, les derniers messages en premier.
        $messages = $messageRepo->findMessagesByUser($user);
        //J'initialise un array pour stocker les conversations
        $conversations = [];
        
        foreach($messages as $message) {
            //Si le user est le sender, alors je stocke l'id du receiver avec comme clé son username 
            if($message->getSender() == $user) {

                $conversations += [
                    $message->getReceiver()->getUsername() => $message->getReceiver()->GetId()                 
                ];
            }
            //Si le user est le receiver, alors je stocke l'id du sender avec comme clé son username 
            if($message->getReceiver() == $user) {

                $conversations += [
                    $message->getSender()->getUsername() => $message->getSender()->GetId()                 
                ];
            }
            //Le += dans le array me permet 2 choses : 
            //Si une clé existe déjà, elle sera ecrasée, donc pas de doublon en cas de nombreux messages de la même personne. (peu importe qu'il soit sender ou receiver)
            //Si c'est un clé différente, elle sera a la suite de mon array.
            //Sachant que ma requete custom me renvoi les messages deja classés dans le bon ordre, mon tableau stockera 
            //les conversation par ordre des derniers messages reçu ;-)

            //exemple :
            //  [
            //      'toto' => 25,
            //      'bidulle' => 38,
            //      'machin' => 52
            //  ]
            //
            //Cette route servira au front pour le menu déroulant permettant de choisir la conversation à consulter
            //Le username à afficher ainsi que l'id nécessaire pour récupérer la conv dans la route "conversation"
            //
        }
        
        $view = View::create();                    
        $view->setData($conversations);       
        $view->setStatusCode(200);

        return $this->handleView($view);
        
    }
}

