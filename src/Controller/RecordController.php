<?php
namespace App\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use App\Repository\LogoRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Record;
use App\Repository\UserRepository;
use App\Entity\Logo;
use App\Form\RecordType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\RecordRepository;

class RecordController extends AbstractFOSRestController
{
    /**
     * @Get(
     *  path ="/logos",
     *  name="get_logos")      
    */
    public function getLogos(LogoRepository $logoRepo)
    {   
        $logos = $logoRepo->findAll();

        $view = View::create(); 
        $context = new Context();
        $context->addGroup('logo');
        $view->setContext($context);    
        $view->setData($logos);
        $view->setStatusCode(200);       
                 
        return $this->handleView($view);      
    }
     
    /**
     * @Post(
     *  path ="/api/add/record/{userId}/logo/{id}",
     *  name="add_record")      
     * @ParamConverter("record", converter="fos_rest.request_body")
    */
    public function addRecord(Request $request, Record $record, UserRepository $userRepo, int $userId, Logo $logo, EntityManagerInterface $em)
    {   
        //Cette route permet de créer un palmares de coach
        //Dans l'url "userID" est l'id du coach en question et "id" est l'id du logo a lié à ce palmarès, id récupéré avec la route get_logos
        $user = $userRepo->find($userId);        

        if(is_null($user)) {

            $json = [
                "log" => [
                    "error" => "Le user envoyé dans l'url est NULL",                    
                ]
            ];

            $view = $this->view($json, 404);         
            return $this->handleView($view);
        }

        if(is_null($logo)) {

            $json = [
                "log" => [
                    "error" => "Le logo envoyé dans l'url est NULL",                    
                ]
            ];

            $view = $this->view($json, 404);         
            return $this->handleView($view);
        }
        
        $currentUser = $this->getuser();
       
        if($currentUser !== $user) {

            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $user->getUsername()]                    
                
            ];

            $view = $this->view($json, 403);         
            return $this->handleView($view);

        }

        if($user->getRole()->getName() !== 'coach') {

            $json = [
                "log" => [
                    "error" => "Vous devez avoir le role coach pour effectuer cette ajout"]                    
                
            ];

            $view = $this->view($json, 403);         
            return $this->handleView($view);

        }

        $form = $this->createForm(RecordType::class, $record);
        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            //Si les champs ne sont pas conformes, le json renvoyé contiendra les messages d'erreurs correctes.
            return $this->handleView($this->view($form));                
            
        }

        $record->setLogo($logo);
        $record->setUser($user);       
        $em->persist($record);
        $em->flush();
        
        //Si je retrouve l'id de l'objet infoCoach cela veut dire qu'il est créé en bdd, alors code 201 ok
        if ($record->getId() !== null) {
           
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
                "danger" => "Une erreur c'est produite"
            ],            
        ];

        $view = $this->view($json, 400);
        return $this->handleView($view);   
        
    } 
    
    /**
     * @Patch(
     *  path ="/api/edit/record/{recordId}/logo/{id}",
     *  name= "edit_record")     
     */
    public function editRecord(Request $request, EntityManagerInterface $em, int $recordId, RecordRepository $recordRepo, Logo $logo)
    {   
        
        $record = $recordRepo->find($recordId);               

        if(is_null($record)) {

            $json = [
                "log" => [
                    "error" => "Cette ressource n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                
            return $this->handleView($view);
        }
        //Le front envoie dans l'url le logo a lier au palmares
        if(is_null($logo)) {

            $json = [
                "log" => [
                    "error" => "Ce logo n'existe pas",                    
                ]
            ];

            $view = $this->view($json, 404);                           
            return $this->handleView($view);
        }

        //Récupération du user auteur de ce palmares
        $user = $record->getUser();
        $currentUser = $this->getUser();

        if($currentUser !== $user) {

            $json = [
                "log" => [
                    "error" => "Vous ne pouvez modifier ces infos",                    
                ]
            ];

            $view = $this->view($json, 403);                    
            return $this->handleView($view);
        }
             
        $form = $this->createForm(RecordType::class, $record);
        $form->submit($request->request->all(), false);

        $record->setLogo($logo);
        $em->persist($record);
        $em->flush();

        $json = [
            "log" => [
                "success" => "Enregistrement effectué"
            ],                
        ];

        $view = $this->view($json, 201);
        return $this->handleView($view);              
        
    }
}