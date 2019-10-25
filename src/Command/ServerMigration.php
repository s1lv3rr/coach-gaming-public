<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServerMigration extends Command {
    
    protected static $defaultName = 'app:server-migration';

    public function configure() {

        $this->setDescription('Replacing old avatar images URL to new server URL in database');
    }

    private $databaseAvatarUrl;
    private $users;
    private $em;
    private $wrongUrlArray = [];
    private $allBaseUrlAreTheSame = true;
    //Ici on défini la nouvelle URL vers le dossier des avatar !
    private $newAvatarDirectoryUrl;


    public function __construct(UserRepository $userRepo, string $serverUrl, string $avatarDirectoryPath, EntityManagerInterface $em, string $formerServerUrl){

        $this->databaseAvatarUrl = $formerServerUrl . $avatarDirectoryPath;
        $this->newAvatarDirectoryUrl = $serverUrl . $avatarDirectoryPath;        
        $this->em = $em;        
        $this->users = $userRepo->findAll();        

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output) {

        $this->databaseAvatarUrlCheck($this->users);

        foreach ($this->wrongUrlArray as $key => $value) {
            
            $output->writeLn([
                    'Check user : ' . $key . ' id ' . $value['id'] . ' with avatar URL = ' . $value['currentAvatarUrl']
                ]);
        }        

    }

    public function databaseAvatarUrlCheck($users) {

                 
        foreach ($users as $user) {

            $savedUrl = $user->getAvatar();
            $explodedUrl = explode($this->databaseAvatarUrl, $savedUrl);            

            if ($explodedUrl[0] !== "") {

                $this->allBaseUrlAreTheSame = false;

                $this->wrongUrlArray += [
                        $user->getUsername() => [
                                'id' => $user->getId(),
                                'currentAvatarUrl' => $user->getAvatar()
                                ]
                        ];                
            }
        }

        if($this->allBaseUrlAreTheSame) {
            $this->databaseAvatarUrlReplace($users, $this->em, $this->newAvatarDirectoryUrl);
        }
                
    }

    public function databaseAvatarUrlReplace($users, $em, $newAvatarDirectoryUrl) {

        foreach ($users as $user) {

            $savedUrl = $user->getAvatar();
            $explodedUrl = explode($this->databaseAvatarUrl, $savedUrl);                       
            $newUrlToSave = $newAvatarDirectoryUrl . $explodedUrl[1];
            $user->setAvatar($newUrlToSave);              
        }

        $em->flush();
    }   

}