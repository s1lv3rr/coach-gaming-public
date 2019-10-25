<?php

namespace App\EventListener;

// for Doctrine < 2.4: use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\RoleRepository;
use App\Entity\User;
use App\Utils\Slugger;
use App\Entity\Game;
use App\Entity\Team;
use App\Entity\Message;
use App\Entity\Review;
use App\Entity\Record;

class EntitiesListener {

    private $roleRepo;
    private $slugger;
    private $passwordEncoder;
    private $serverUrl;
    private $defaultAvatarPath;

    public function __construct(RoleRepository $roleRepo, Slugger $slugger, UserPasswordEncoderInterface $passwordEncoder, string $serverUrl, string $defaultAvatarPath) {

        $this->roleRepo = $roleRepo;
        $this->slugger = $slugger;
        $this->passwordEncoder = $passwordEncoder;
        $this->serverUrl = $serverUrl;
        $this->defaultAvatarPath = $defaultAvatarPath;
    }

    public function prePersist(LifecycleEventArgs $args){

        $entity = $args->getObject();        
        $entity->setCreatedAt(new \Datetime());

        

        if($entity instanceof User) {
            
            //Si création d'un user, je lui met le role user directement, pour eviter le role_Id can not be Null a la creation
            //findRoleByName requete custom RoleRepository
            $userRole = $this->roleRepo->findRoleByCode('ROLE_USER');
            $entity->setRole($userRole);

            //Puis je fais un slug de son username et je setSlug directement.
            $usernameSlug = $this->slugger->slugify($entity->getUsername());
            $entity->setSlug($usernameSlug);

            //J'encode le mot de passe récupéré dans le JSON envoyé depuis le front, attention j'ai passé la taille max de password de la table user a 100!
            $encodedPassword = $this->passwordEncoder->encodePassword($entity, $entity->getPassword());
            $entity->setPassword($encodedPassword);

            //!\----------/!\-----------/!\----------/!\----------/!\---------/!\---------/!\---------/!\---------/!\----------/!\/
            //A mettre en commentaire ci dessous au moment du chargement des fixtures pour ne pas réécrire par dessus les avatars//
            $entity->setAvatar($this->serverUrl . $this->defaultAvatarPath);
            //-------------------------------------------------------------------------------------------------------------------//

            $entity->setIsActive(true);            
        }

        if($entity instanceof Game) {
            //Automatisation des slug a la creation
            $gameSlug = $this->slugger->slugify($entity->getName());
            $entity->setSlug($gameSlug);
            $entity->setIsActive(true);
            
        }

        if($entity instanceof Team) {
            //Automatisation des slug a la creation
            $teamSlug = $this->slugger->slugify($entity->getName());
            $entity->setSlug($teamSlug);
            $entity->setIsActive(true);
        }

        if($entity instanceof Message || $entity instanceof Review || $entity instanceof Record) {
            
            $entity->setIsActive(true);
        }
                
    }
 
     public function preUpdate(LifecycleEventArgs $args){
        
        $entity = $args->getObject();      

        $entity->setUpdatedAt(new \Datetime());
        
        if($entity instanceof User) {          
                       
            $usernameSlug = $this->slugger->slugify($entity->getUsername());
            $entity->setSlug($usernameSlug);            
        }
         
    }
}    