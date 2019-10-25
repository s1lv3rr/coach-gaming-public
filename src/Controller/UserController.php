<?php
namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\InfoCoach;
use App\Form\InfoCoachType;
use FOS\RestBundle\View\View;
use App\Repository\UserRepository;
use FOS\RestBundle\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractFOSRestController
{
    private $serverUrl;
    private $defaultAvatarPath;
    private $avatarDirectoryPath;

    public function __construct(string $serverUrl, string $defaultAvatarPath, string $avatarDirectoryPath) {

        $this->serverUrl = $serverUrl;
        $this->defaultAvatarPath = $defaultAvatarPath;
        $this->avatarDirectoryPath = $avatarDirectoryPath;
    }

    /**
     * @Post(
     *  path ="/signup",
     *  name ="signup")
     * @ParamConverter("user", converter="fos_rest.request_body")
     */
    public function signUp(Request $request, User $user, EntityManagerInterface $em)
    {

        //creation du form, je passe l'objet user recupéré grace au paramconverter en second argument
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());
        
        //Les contraintes de champ (validator symfony) sont définies dans l'entité User, dans les annotations @Assert
        if (false === $form->isValid()) {
            //Si les champs ne sont pas conformes, le json renvoyé contiendra les messages d'erreurs correctes.
            $view = $this->view($form, 400);
              
            return $this->handleView(
                $this->view($form)
            );
        }
        //Voir Src/EventListener/EntitiesListener.php pour le traitement en préPersist
        $em->persist($user);        
        $em->flush();
        //Si je retrouve l'id de l'objet user cela veut dire qu'il est créé en bdd, alors code 201 ok
        if ($user->getId() !== null) {
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
     * @Get(
     *  path ="/api/profil/{slug}",
     *  name="get_profil")
    */
    public function getProfil(User $user = null)
    {
        if (is_null($user)) {
            $json = [
                "log" => [
                    "error" => "Ce user n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);            
            return $this->handleView($view);
        }
        
        $currentUser = $this->getUser();
        //Pour eviter le cas où un user tente d'acceder a un profil qui n'est pas le sien par l'url.. Je compare le user passé en url avec le user identifié par token
        if ($currentUser !== $user) {
            $json = [
                "log" => [
                    "error" => "Vous n'avez pas accès à ce profil",
                ]
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }
       
        $view = View::create();
        $context = new Context();
        $context->addGroup('profil');
        $view->setContext($context);

        $json = $user;
        $view->setData($json);
        $view->setStatusCode(200);
        
               
        return $this->handleView($view);
    }

          
    /**
     * @Post(
     *  path ="api/add/info_coach/{userSlug}/game/{id}",
     *  name ="add_infocoach")
     * @ParamConverter("infocoach", converter="fos_rest.request_body")
     */
    public function addInfoCoach(Request $request, InfoCoach $infocoach, EntityManagerInterface $em, string $userSlug, UserRepository $userRepo, Game $game = null)
    {   
        //Route qui sert a un coach fraichement inscrit de renseigner ses données et le jeu auquel il est rataché (id url)
        $user = $userRepo->findOneBySlug($userSlug);

        if (is_null($user)) {
            $json = [
                "log" => [
                    "error" => "Le user envoyé par l'url est NULL",
                ]
            ];

            $view = $this->view($json, 404);
            return $this->handleView($view);
        }

        if (is_null($game)) {
            $json = [
                "log" => [
                    "error" => "Le jeu envoyé par l'url est NULL",
                ]
            ];

            $view = $this->view($json, 404);           
            return $this->handleView($view);
        }
        
        $currentUser = $this->getuser();
       
        if ($currentUser !== $user) {
            $json = [
                "log" => [
                    "error" => "Vous n'avez pas les droits pour accéder à cette route, veuillez vous connecter en tant que " . $user->getUsername()]
                
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        if ($user->getRole()->getName() !== 'coach') {
            $json = [
                "log" => [
                    "error" => "Vous devez avoir le role coach pour effectuer cette ajout"
                    ]                
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        if (!is_null($user->getInfoCoach())) {
            $json = [
                "log" => [
                    "error" => "Une info coach est déja lié a ce user, veuillez emprunter la route d'édition"
                    ]                
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        $form = $this->createForm(InfoCoachType::class, $infocoach);
        $form->submit($request->request->all());

        if (false === $form->isValid()) {
            //Si les champs ne sont pas conformes, le json renvoyé contiendra les messages d'erreurs correctes.
            return $this->handleView($this->view($form));
        }

        $infocoach->setGame($game);
        $em->persist($infocoach);
        $em->flush();
        
        //Si je retrouve l'id de l'objet infoCoach cela veut dire qu'il est créé en bdd, alors code 201 ok
        if ($infocoach->getId() !== null) {
            //J'associe l'info coach au user envoyé dans l'url
            $user->setInfoCoach($infocoach);
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
                "error" => "Une erreur c'est produite"
            ],
        ];

        $view = $this->view($json, 400);       
        return $this->handleView($view);
    }
   
    /**
     * @Patch(
     *  path ="api/edit/info_coach/{userId}/game/{id}",
     *  name= "edit_infocoach")
     */
    public function editInfoCoach(Request $request, EntityManagerInterface $em, UserRepository $userRepo, int $userId, Game $game)
    {
        $user = $userRepo->find($userId);

        if (is_null($user)) {
            $json = [
                "log" => [
                    "error" => "Ce coach n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);            
            return $this->handleView($view);
        }

        if (is_null($game)) {
            $json = [
                "log" => [
                    "error" => "Ce jeu n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);
            return $this->handleView($view);
        }

        $currentUser = $this->getUser();

        if ($currentUser !== $user) {
            $json = [
                "log" => [
                    "error" => "Vous ne pouvez modifier ces infos",
                ]
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        
        $infoCoachToEdit = $user->getInfoCoach();
        
        $form = $this->createForm(InfoCoachType::class, $infoCoachToEdit);
        $form->submit($request->request->all(), false);
        
        $em->persist($infoCoachToEdit);
        $infoCoachToEdit->setGame($game);
        $em->flush();

        $json = [
            "log" => [
                "success" => "Enregistrement effectué"
            ],
        ];

        $view = $this->view($json, 201);        
        return $this->handleView($view);
    }

    //Ceci est la VRAI route edit profil et avatar, le front n'arrivant pas a utiliser le multipart/form-data, j'ai remis l'ancienne en service par manque de temps pour la présentation

    /**
     * @Post(
     *  path ="api/edit/profil/{id}",
     *  name ="edit_profil")
     * @Rest\FileParam(name="imageAvatar", image=true, nullable=true)
     * @Rest\RequestParam(name="userObject", nullable=true)
     * @param ParamFetcher $paramFetcher
     * @param Int $id
     * 
     */
    public function editProfil(ParamFetcher $paramFetcher ,Request $request, int $id, EntityManagerInterface $em, UserRepository $userRepo, UserPasswordEncoderInterface $passwordEncoder)
    {   
        //Cette route est différente des autres route update
        //A utiliser en multipart/form-data
        //Permet d'edititer le profil et la photo d'avatar, ensemble ou indépendamment. 
        $user = $userRepo->find($id);        
        $currentUser = $this->getUser();

        if (is_null($user)) {
            $json = [
                "log" => [
                    "error" => "Ce user n'existe pas",
                ]
            ];

            $view = $this->view($json, 404);            
            return $this->handleView($view);
        }
                     
        if ($currentUser !== $user) {
            $json = [
                "log" => [
                    "error" => "Vous ne pouvez pas modfier l'avatar de ce profil",
                ]
            ];

            $view = $this->view($json, 403);            
            return $this->handleView($view);
        }

        //Je récupère la photo envoyé dans le form grace au ParamFetcher de FOSrest
        $avatarFile = $paramFetcher->get('imageAvatar');
        //Je récupère le json envoyé dans la requête a la clé "userObject"
        $userJson = $paramFetcher->get("userObject");        
        //Je le converti en simple array php, je ne veux pas directement hydraté un objet de la classe User car je veux pouvoir conservé les données déjà stockées
        //que l'utilisateur ne souhaite pas forcément modifier.        
        $phpUserArray =  json_decode($userJson, true);
        
        //La photo d'avatar comme le json sont optionnel dans le formulaire, je fais le traitement si dessous seulement si le front m'envoie des champ a modifier
        if (null !== $userJson) {
            //Pour chaque tour de boucle, si mon array php a une clé "username", je stocke sa valeur dans l'objet User récupéré par l'id dans l'url..
            //J'ai 6 cas différents, si je rentre pas dans l'un de ces cas, une des propriétés envoyé dans le json n'existe pas (dans la classe User) ou est mal ecrite.
            //J'initialise un variable qui passera a True seulement si on passe dans le cas d'un nouveau mot de passe envoyé
            $isNewPassword = false;
            foreach ($phpUserArray as $property => $value) {
                
                switch ($property) {
                    //Si le json envoyé par le front contient un clé "name", alors je stocke sa valeur dans l'objet User que j'édite
                    case 'name':
                    $user->setName($value);
                    break;
                    //Same here
                    case 'lastname':
                    $user->setLastname($value);
                    break;
                
                    case 'username':
                    $user->setUsername($value);
                    break;
                    //Je converti la string age en int
                    case 'age':
                    $age = intval($value);
                    $user->setAge($age);
                    break;
                
                    case 'email':
                    $user->setEmail($value);
                    break;
                    //Ici je set le nouveau password en clair dans l'objet pour lui faire passer les verif de contraintes dans mon form                    
                    case 'password':
                    $isNewPassword = true;
                    $user->setPassword($value);
                    break;

                    default: $json = [
                        "log" => [
                            "error" => "Une propriété du json a mal été ecrite"
                        ],
                    ];
                    //Ici on rentre dans aucun des 6 cas du dessus, le json est mal renseigné
                    $view = $this->view($json, 400);
                    return $this->handleView($view);
                    break;
                }
            }
        }       
        //Je soumet l'objet User nouvellement hydraté au formulaire pour lui faire passer les contraintes      
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $form->submit($request->request->all(), false);
         
        if ($form->isValid()) {
            //Seulement si je reçois un json du front et si le mot de passe a bien était modifier, sinon je risque d'encoder un message déjà encoder en bdd.
            if (null !== $userJson && $isNewPassword == true) { //Ici normalement $isNewPassword est undefinied mais php me saoul pas trop apparement et arrete le test a la premiere condition, j'ai laissé ça comme ça pour le fun
                //Seulement si les contraintes sont passées, j'encode le mot de passe, (un mot de passe encodé a l'avance dépassera toujours ma contrainte de 5 characteres minimum)
                $encodedPassword = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($encodedPassword);
            }
            //Je récupère le chemin de l'avatar déjà lié au user dans la BDD, pour supprimer l'ancienne photo et la remplacer par la nouvelle sur le serveur.  
            $registeredAvatarUrl = $user->getAvatar();
            $explodedRegisteredAvatarUrl = explode($this->serverUrl, $registeredAvatarUrl);            
            $avatarImagePathToDelete = "/var/www/html" . $explodedRegisteredAvatarUrl[1];
            
            //Si j'ai bien une photo envoyé dans le form alors je continu le traitement de renommage de fichier envoyer dans la requête....
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();
            
                try {
                    $avatarFile->move(
                        $this->getParameter('avatar_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                //Concaténation de l'url vers le dossier contenant les avatars avec le nouveau nom du fichier pour le sauver en BDD
                $avatarUrlToSave = $this->serverUrl . $this->avatarDirectoryPath . $newFilename;
                //j'enregistre l'url renvoyant a la photo sur le serveur en BDD
                $user->setAvatar($avatarUrlToSave);

                //Ici j'effectue le remplacement entre la nouvelle et l'ancienne photo, sauf si la photo d'avatar en BDD est l'avatar par default.
                //Je ne veux pas effacer l'avatar de base car tous les nouveaux compte en ont besoin.
                if($registeredAvatarUrl !== $this->serverUrl . $this->defaultAvatarPath) {
                    unlink($avatarImagePathToDelete);
                }
                
            }
                       
            $em->flush();
            //Après le flush, je récupère l'url du nouvel avatar fraichement enregistré en BDD, et vérifie que le fichier existe bien sur mon serveur
            $newAvatarExplodedPath = explode($this->serverUrl,$user->getAvatar());       
            $newServerAvatarPath = '/var/www/html' . $newAvatarExplodedPath[1]; 
            $filesystem = new Filesystem;        
            
            //Si le fichier existe bien, il y a concordance entre le fichier sur le serveur et l'url stocké en BDD !
            if ($filesystem->exists($newServerAvatarPath)) {

                $json = [
                    "log" => [
                        "success" => "Modifications effectuées"
                    ],
                ];

                $view = $this->view($json, 200);
                return $this->handleView($view);

            } else {

                $json = [
                    "log" => [
                        "error" => "Une erreur c'est produite"
                    ],
                ];

                $view = $this->view($json, 400);
                return $this->handleView($view);

            }

        } elseif(!$form->isValid()) {
            //Si les contraintes de form ne sont pas passées je renvoi les champs concernés
            return $this->handleView($this->view($form));
        }
                           
    }
}


   
