<?php
namespace App\DataFixtures;

use Faker;
use Faker\Factory;
use App\Entity\Game;
use App\Entity\Logo;
use App\Entity\Role;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Record;
use App\Entity\Review;
use App\Utils\Slugger;
use App\Entity\Platform;
use App\Entity\InfoCoach;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\DataFixtures\Faker\UserRoleProvider;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Command\ImpressionDesDossier;

class AppFixtures extends Fixture
{   
    private $passwordEncoder;
    private $slugger;
    private $serverUrl;
    private $avatarDirectoryPath;
    private $defaultAvatarPath;
    private $em;
    private $userRepo;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder, Slugger $slugger, string $serverUrl, string $defaultAvatarPath, string $avatarDirectoryPath, UserRepository $userRepo, EntityManagerInterface $em)
    {   
        $this->serverUrl = $serverUrl;
        $this->defaultAvatarPath = $defaultAvatarPath;
        $this->avatarDirectoryPath = $avatarDirectoryPath;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
        $this->em = $em;
        $this->userRepo = $userRepo;
               
    }


    public function load(ObjectManager $manager)
    {
        $generator = Factory::create('fr_FR');

               
        //ajout provider custom MovieAndGenreProvider 
        //Note : $generator est attendu dans le constructeur de la classe Base de faker
        $generator->addProvider(new UserRoleProvider($generator));
        $populator = new Faker\ORM\Doctrine\Populator($generator, $manager);
        
        /*
         Faker n'apelle pas le constructeur d'origine donc genres n'est pas setté
         -> effet de bord sur adders qui utilise la methode contains sur du null
         */
        $populator->addEntity(Role::class, 3, array(          
            'name' => function() use ($generator) { return $generator->unique()->userRole(); },
            'code' => function() use ($generator) { return $generator->unique()->userRoleCode(); },               
        )); 
       
        $populator->addEntity(Game::class, 4, array(
            'name' => function() use ($generator) { return $generator->unique()->getGameName(); },            
            'description' => function() use ($generator) { return $generator->paragraph(); },
            'headerBackground' => function() use ($generator) { return $generator->url(); },
            'releaseDate' => function() use ($generator) { return $generator->dateTimeBetween("-200 days", "now"); }, 
        ));

        $populator->addEntity(Team::class, 5, array(
            'name' => function() use ($generator) { return $generator->unique()->getTeamName(); },
            'description' => function() use ($generator) { return $generator->paragraph(); },
            'logo' => function() use ($generator) { return $generator->imageUrl($width = 50, $height = 50) // 'http://lorempixel.com/640/480/'
                ; },
            'logoDescription' => function() use ($generator) {
                return $generator->  sentence($nbWords = 3, $variableNbWords = true);
            }  ,
            'youtube' => function() use ($generator) { return $generator->url(); }, 
            'insta' => function() use ($generator) { return $generator->url(); }, 
            'twitch' => function() use ($generator) { return $generator->url(); },
        ));

        $populator->addEntity(Platform::class, 4, array(
            'name' => function() use ($generator) { return $generator->unique()->platformName(); },
            
        ));
                                
        $inserted = $populator->execute();    
        $roles = $inserted['App\Entity\Role'];
        $games = $inserted['App\Entity\Game']; 
        $teams = $inserted['App\Entity\Team'];
        
        foreach ($teams as $team) {

            if($team->getName() == "Gigantti") {
                $team->setLogo('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/teams/gigantti.png');
            }
            if($team->getName() == "Vitality") {
                $team->setLogo('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/teams/vitality.png');
            }
            if($team->getName() == "Rogue") {
                $team->setLogo('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/teams/rogue.png');
            }
            if($team->getName() == "Team4") {
                $team->setLogo('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/teams/urania.png');            }
           
            if($team->getName() == "Millenium") {
                $team->setLogo('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/teams/millenium.png');
            }
        }
        
        
        $medal = new Logo();
        $medal->setUrl('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/cups/medal.svg')->setLogoDescription('Médaille');        

        $bronzeCup = new Logo();
        $bronzeCup->setUrl('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/cups/bronze-cup.svg')->setLogoDescription('Coupe de bronze');

        $silverCup = new Logo();
        $silverCup->setUrl('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/cups/silver-cup.svg')->setLogoDescription('Coupe d\'argent');

        $goldCup = new Logo();
        $goldCup->setUrl('http://92.243.9.86/projet-CoachsGaming-back/coach-gaming/public/cups/gold-cup.svg')->setLogoDescription('Coupe d\'or');
        

        $admin = new User();
        $admin->setName('Admin')->setLastname('admin')->setUsername('admin')->setEmail('admin@admin.com');
        $admin->setPassword('admin')->setAge(30)->setAvatar($this->serverUrl . $this->defaultAvatarPath);
        //--------------------------------Fake coach Overwatch---------------------------------------------------------- 
        $coach1 = new User();
        $coach1->setName('Dupuy')->setLastname('Léon')->setUsername('LeoOOxx')->setEmail('leon89@gmail.com');
        $coach1->setPassword('LeoOOxx')->setAge(26)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . '94823.png');
        $infoCoach1 = new InfoCoach();
        $infoCoach1->setPrice(30)->setDescription("Moi c'est Léon, je suis un joueur PC sur Overwatch.
        Pour ma part, j'ai commencé à jouer dès la sortie du jeu. J'ai réellement commencé à le tryhard et les compétitions esport à la moitié de la saison 3.         
        Actuellement dans l'équipe Overwatch de la structure Rogue, je propose un service de coaching assez poussé. Nous pourrons également travailler en cours les points précis de ton gameplay que tu souhaites améliorer et les points faibles que je relèverai.
        En résumé, si tu souhaites faire de gros progrès, réserve vite un cours avec moi !")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(5)->setTeam($teams[0]);
        $coach1->setInfoCoach($infoCoach1);
        $record1 = new Record();
        $record1->setDescription('Demi-Finaliste Overwatch Cup 2018')->setUser($coach1)->setLogo($medal);
        $record2 = new Record();
        $record2->setDescription('Finaliste Overwatch Cup 2017')->setUser($coach1)->setLogo($medal);

        $coach2 = new User();
        $coach2->setName('Margaux')->setLastname('Toussaint')->setUsername('Ang3l')->setEmail('angel38@gmail.com');
        $coach2->setPassword('Ang3l')->setAge(22)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'images.jpeg');
        $infoCoach2 = new InfoCoach();
        $infoCoach2->setPrice(25)->setDescription("Salut à tous, Moi c'est Ang3l, je suis un joueur PC sur Overwatch.
        J'ai commencé à jouer dès la sortie du jeu, a la saison 1.
        Ma spécialité ? 
        Les roles tanks et supports ! 
        Actuellement membre de la mailleur team FR sur overwatch Rogue ... N'hésites pas à réserver une heure de coaching avec moi !")->setYoutube("https://www.youtube.com/watch?v=5EXFilTUiko&list=RD3SDBTVcBUVs&index=3")->setRating(5)->setTeam($teams[1]);
        $coach2->setInfoCoach($infoCoach2);
        $record3 = new Record();
        $record3->setDescription('Main Healer Overwatch League "Vancouver Titan" 2019')->setUser($coach2)->setLogo($bronzeCup);
        $record4 = new Record();
        $record4->setDescription('Off Healer Overwatch Contenders')->setUser($coach2)->setLogo($medal);


        $coach3 = new User();
        $coach3->setName('Olivie')->setLastname('Renard')->setUsername('Widoww')->setEmail('admin@admin.com');
        $coach3->setPassword('Widoww')->setAge(23)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'widoww-photo.jpeg');
        $infoCoach3 = new InfoCoach();
        $infoCoach3->setPrice(30)->setDescription("Bonjour à tous,
        Je suis Olivie, plus connu sous le nom de Widoww “In Game”. Je suis joueur compétitif sur Overwatch depuis le démarrage des compétitions sur le jeu. Je me qualifie comme une personne avec beaucoup d’expérience. Pour résumer mon palmarès ,j’ai réalisé avec mon ex-équipe Deus eSport (équipe semi-pro) ainsi qu'en solo de bonnes performances online. J'ai depuis rejoint la structure Rogue !        
        Je vous donnerais les astuces pour améliorer votre aim, ou encore les prises de décisions à prendre en Squad. Partager mon savoir et de rendre les gens meilleurs est une des choses que j’affectionne. Pendant 1h de coaching avec moi vous en apprendrez beaucoup sur le jeu et sur ma spécialité ! Vous ne serez pas déçu ! 
        Avec moi tu es certain d'être au top et ta progression est ma priorité !")->setYoutube("https://www.youtube.com/watch?v=Gcshz4lBJPQ&list=RD3SDBTVcBUVs&index=2")->setRating(5)->setTeam($teams[3]);
        $coach3->setInfoCoach($infoCoach3);
        $record5 = new Record();
        $record5->setDescription('Widow Tournament Winner 2018')->setUser($coach3)->setLogo($goldCup);
        $record6 = new Record();
        $record6->setDescription('Overwatch Contenders 2017 Quart de finale')->setUser($coach3)->setLogo($medal);

        $coach4 = new User();
        $coach4->setName('Noël')->setLastname('Germain')->setUsername('HeadShotOnly')->setEmail('kikou83@hotmail.com');
        $coach4->setPassword('HeadShotOnly')->setAge(24)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'dva.jpg');
        $infoCoach4 = new InfoCoach();
        $infoCoach4->setPrice(35)->setDescription("Salut !
        Je me présente HeadShotOnly , 24 ans, je suis actuellement joueur professionnel chez Paris Eternal, l'une des nouvelles équipes de l'Overwatch League.        
        Anciennement joueur sur ShootMania Storm (jeu sur lequel j’ai été champion du monde suite à mes victoires aux ESWC 2013, 2014 et 2015 ainsi qu’à la GA 2014 et 2015), j'ai commencé ma carrière sur Overwatch depuis 2016 !        
        J'ai débuté dans le rôle de support, notamment avec Lucio, puis en off-tank avec Zarya et Chopper, pour enfin arriver au rôle que je joue aujourd'hui, celui de DPS. J'ai pu évoluer dans ces différents rôles au sein de plusieurs équipes, notamment chez Rogue où j'ai commencé à me faire connaître sur la scène internationale. J'ai ensuite eu la chance de pouvoir jouer chez Los Angeles Valiant lors de la première saison de l'Overwatch League.")->setYoutube("https://www.youtube.com/watch?v=YcwdjuQ3UR4&list=RD3SDBTVcBUVs&index=4")->setRating(4)->setTeam($teams[4]);
        $coach4->setInfoCoach($infoCoach4);
        $record7 = new Record();
        $record7->setDescription('Titulaire Paris Eternal')->setUser($coach4)->setLogo($goldCup);
        $record8 = new Record();
        $record8->setDescription('Main Dps OWL')->setUser($coach4)->setLogo($goldCup);

        $coach5 = new User();
        $coach5->setName('Alice')->setLastname('Parmentier')->setUsername('alixXx')->setEmail('alice@hotmail.com');
        $coach5->setPassword('alixXx')->setAge(25)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'junkrat.jpg');
        $infoCoach5 = new InfoCoach();
        $infoCoach5->setPrice(20)->setDescription("Bonjour à toutes et à tous ! Je me présente, je m'appelle Alice
        Coach sur Overwatch depuis maintenant presque 2 ans, j'ai eu l'occasion de pouvoir aider pendant de nombreux mois des teams comme :        
        Team Nemesis : la meilleure équipe européenne Féminine sur Overwatch.
         orKsGP White : La meilleure équipe française non professionnelle sur Overwatch.
        Angry Titans : Une des meilleure équipe européenne que j'ai rejoins en tant qu'assistant coach et analyste et avec qui j'ai été vice champion d'europe début 2019 et Champion d'Europe dernièrement !
        Ayant un gros background d'encadrement d'équipe, que ce soit sportif ou e-sportif, depuis des années, j'ai acquis de grandes compétences dans l'encadrement, l'analyse et bien entendu la communication au sein d'un collectif de joueurs et joueuses.")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(5)->setTeam($teams[3]);
        $coach5->setInfoCoach($infoCoach5);
        $record9 = new Record();
        $record9->setDescription('Coaching staff OWL')->setUser($coach5)->setLogo($medal);
        $record10 = new Record();
        $record10->setDescription('Main Dps OWL')->setUser($coach5)->setLogo($silverCup);
        //--------------------------------Fake coach Fifa----------------------------------------------------------  

        $coach6 = new User();
        $coach6->setName('Corentin')->setLastname('Thuillier')->setUsername('MaestroSquad')->setEmail('corentin@hotmail.com');
        $coach6->setPassword('MaestroSquad')->setAge(20)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'foot.jpeg');
        $infoCoach6 = new InfoCoach();
        $infoCoach6->setPrice(30)->setDescription("Salut à tous :)
        Moi c'est Corentin Thuillier, plus connu sous le pseudo de MaestroSquad !
        Je suis actuellement joueur professionnel pour la Team Vitality et sélectionné pour la 2ème année consécutive en équipe de France eFoot.         
        Après plusieurs années de compétition au plus haut niveau, j'ai engrangé beaucoup d'expérience que je souhaite désormais vous transmettre en devenant coach FIFA sur CoachGaming !
        En cours, j'analyserai ton gameplay pour déceler tes points faibles et les travailler.
        Nous pourrons également travailler ton mental et la gestion d'un match. 
        Alors si tu es prêt à écouter mes conseils pour franchir un cap, réserve vite une heure de coaching avec moi !"
        )->setYoutube("https://www.youtube.com/watch?v=RMWBriHwVrI&list=RDRMWBriHwVrI&start_radio=1")->setRating(5)->setTeam($teams[3]);
        $coach6->setInfoCoach($infoCoach6);
        $recordFIFA1 = new Record();
        $recordFIFA1->setDescription('FIFA eWorld Cup 2019')->setUser($coach6)->setLogo($medal);
        $recordFIFA2 = new Record();
        $recordFIFA2->setDescription('FIFA eWorld Cup 2018')->setUser($coach6)->setLogo($bronzeCup);
        $recordFIFA3 = new Record();
        $recordFIFA3->setDescription('Fifa Global Submit Quarter Finalist')->setUser($coach6)->setLogo($medal);
        

        $coach7 = new User();
        $coach7->setName('Julien')->setLastname('Genies')->setUsername('steveO')->setEmail('steve@genies.com');
        $coach7->setPassword('steveO')->setAge(33)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . '4032d8cc514317898ec0e107c9853a69.jpg');
        $infoCoach7 = new InfoCoach();
        $infoCoach7->setPrice(25)->setDescription("Salut et bienvenue sur mon profil :) 
        Je m'appelle Julien, plus connu sous le pseudo de steveO. 
        J'évolue chaque jour au plus haut niveau (Qualifié pour plusieurs événements regroupant les 8 meilleurs joueurs du monde) et je suis là pour te faire franchir un véritable palier !        
        Avec mes techniques de jeu et ma stratégie de joueur pro, je vais t'aider à remplir des objectifs que tu pensais jusqu'ici impossible à atteindre.
        Prêt à te surpasser et à devenir un futur champion ? Je t'attends pour 1h de coaching intensif. ")->setYoutube("https://www.youtube.com/watch?v=mMfxI3r_LyA&list=RDRMWBriHwVrI&index=2")->setRating(4)->setTeam($teams[0]);
        $coach7->setInfoCoach($infoCoach7);
        $recordFIFA4 = new Record();
        $recordFIFA4->setDescription('FIFA Global Series')->setUser($coach7)->setLogo($medal);
        $recordFIFA5 = new Record();
        $recordFIFA5->setDescription('FIFA eWorld Cup 2018')->setUser($coach7)->setLogo($bronzeCup);
        $recordFIFA6 = new Record();
        $recordFIFA6->setDescription('eChampions League Quarter Finalist')->setUser($coach7)->setLogo($medal);

        $coach8 = new User();
        $coach8->setName('Rodrigue')->setLastname('Carbonaro')->setUsername('cr7')->setEmail('cr7@hotmail.com');
        $coach8->setPassword('cr7')->setAge(25)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . '109201.jpg');
        $infoCoach8 = new InfoCoach();
        $infoCoach8->setPrice(40)->setDescription("Salut, je m’appelle Rodrigue « cr7 » 
        Joueur esport FIFA depuis 3 ans. 
        Je suis là pour t’aider sur tous les éléments qui font un bon joueur FIFA et surtout travailler en cours avec toi sur tous tes points faibles pour que tu deviennes un monstre sur FIFA 19. 
        Pour cela, je suis disponible H24 7j/7 que ce soit pour une heure de coaching ou pour que je devienne ton coach sur la durée.")->setYoutube("https://www.youtube.com/watch?v=bpOSxM0rNPM&list=RDRMWBriHwVrI&index=3")->setRating(5)->setTeam($teams[2]);
        $coach8->setInfoCoach($infoCoach8);
        $recordFIFA7 = new Record();
        $recordFIFA7->setDescription('FIFA eWorld Cup 2017')->setUser($coach8)->setLogo($medal);
        $recordFIFA8 = new Record();
        $recordFIFA8->setDescription('FIFA eWorld Cup 2018')->setUser($coach8)->setLogo($medal);
        $recordFIFA9 = new Record();
        $recordFIFA9->setDescription('FIFA Global Series')->setUser($coach8)->setLogo($medal);

        $coach9 = new User();
        $coach9->setName('Samy')->setLastname('Marmoud')->setUsername('Samsam')->setEmail('samygrossexe@hotmail.com');
        $coach9->setPassword('Samsam')->setAge(29)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . '238490.png');
        $infoCoach9 = new InfoCoach();
        $infoCoach9->setPrice(30)->setDescription("Salut à tous :) 
        Je m'appelle Samy, plus connu sous le pseudo de Samsam.        
        Je suis un joueur très technique et polyvalent. Je peux tout aussi bien développer un football lent et minutieux que rapide et technique ! Tout dépend de l'adversaire que j'ai en face de moi.         
        Je suis prêt à te fournir l'entrainement nécessaire pour toi aussi, devenir un joueur polyvalent et ainsi pouvoir défier n'importe qui ! Quelque soit ton objectif, je peux t'aider à l'atteindre.
        Alors n'attends plus et réserve vite pour une heure de coaching intense avec moi ")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(4)->setTeam($teams[4]);
        $coach9->setInfoCoach($infoCoach9);
        $recordFIFA10 = new Record();
        $recordFIFA10->setDescription('FIFA eWorld Cup 2017')->setUser($coach9)->setLogo($medal);
        $recordFIFA11 = new Record();
        $recordFIFA11->setDescription('FIFA eWorld Cup 2018')->setUser($coach9)->setLogo($medal);
        $recordFIFA12 = new Record();
        $recordFIFA12->setDescription('FIFA Global Series')->setUser($coach9)->setLogo($medal);

        $coach10 = new User();
        $coach10->setName('Michel')->setLastname('Jackie')->setUsername('Michmich')->setEmail('michou@hotmail.com');
        $coach10->setPassword('Michmich')->setAge(40)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'avatar/poj_2x.jpg');
        $infoCoach10 = new InfoCoach();
        $infoCoach10->setPrice(25)->setDescription("Salut ! Moi c'est Michel, j'ai 40 ans et je suis un coach professionnel FIFA et coach personnel du joueur pro Famsinho, vice champion de France eLigue 1. 
        Avant tout je suis un joueur très expérimenté sur FIFA, j'ai commencé au temps de PES et je suis venu sur FIFA à partir de FIFA11. Je suis rentré officiellement dans le monde de la compétition sur FIFA 17 ou j'ai commencé à coacher un ami à moi. Inconnu dans le monde de la compétition nous avons réussi à devenir vice-champion de France PS4 UTQF, tournoi qui à eu lieu sur la scène de l'Olympia et diffusé en live sur Canal + !         
        Ensuite, j'ai coaché Famsinho, ensemble, nous avons enchaîné les performances sur plusieurs petits tournois et sur FIFA 18 nous avons réussi à atteindre la finale de l'Orange e-Ligue1 édition hiver ! Nous avons ensuite participé au championnat du monde ou Famshino à échoué aux portes de la phase finale qui aurait eu lieu à Londres. Cependant, il reste un des meilleurs joueurs car il est actuellement top 32 mondial. 
        Ces performances exceptionnelles ont été possible car il a su écouter mes conseils et mes analyses sur son jeu in-game. 
        Famsinho n'est pas le seul joueur que j'ai entrainé sur FIFA, et ne sera pas le dernier ;) ....         
        Avec cette grosse expérience sur Fifa et plus de 20 000€ de cash price remporté par les joueurs que j'ai coaché, je serais en mesure de te donner les meilleurs conseils sur FIFA afin d'améliorer ton niveau de jeu et pourquoi pas devenir pro player toi aussi et remporter cette somme  alors, à qui le tour ?")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(5)->setTeam($teams[0]);
        $coach10->setInfoCoach($infoCoach10);
        $recordFIFA13 = new Record();
        $recordFIFA13->setDescription('E-Ligue1 semi-finalist')->setUser($coach10)->setLogo($medal);
        $recordFIFA14 = new Record();
        $recordFIFA14->setDescription('FIFA eWorld Cup 2018')->setUser($coach10)->setLogo($medal);
        $recordFIFA15 = new Record();
        $recordFIFA15->setDescription('Gfinity LQE April 2017')->setUser($coach10)->setLogo($goldCup);

        //--------------------------------Fake coach PUBG----------------------------------------------------------  

        $coach11 = new User();
        $coach11->setName('Paul')->setLastname('Patalano')->setUsername('Seth')->setEmail('paul38@hotmail.com');
        $coach11->setPassword('Seth')->setAge(20)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'noob.jpg');
        $infoCoach11 = new InfoCoach();
        $infoCoach11->setPrice(30)->setDescription("Salut à tous :)
        Moi c'est Paul, je suis un joueur PC sur PUBG.
        Pour ma part, j'ai commencé à jouer dès la sortie du jeu. J'ai réellement commencé à le tryhard et les compétitions esport à la moitié de la saison 3.         
        Je propose un service de coaching assez poussé. Nous pourrons également travailler en cours les points précis de ton gameplay que tu souhaites améliorer et les points faibles que je relèverai.
        En résumé, si tu souhaites faire de gros progrès, réserve vite un cours avec moi !")->setYoutube("https://www.youtube.com/watch?v=RMWBriHwVrI&list=RDRMWBriHwVrI&start_radio=1")->setRating(4)->setTeam($teams[4]);
        $coach11->setInfoCoach($infoCoach11);
        $recordPUBG1 = new Record();
        $recordPUBG1->setDescription('FaceIt Global Submit Quarter Finalist')->setUser($coach11)->setLogo($medal);
        $recordPUBG2 = new Record();
        $recordPUBG2->setDescription('GLL Grand Slam Semi-Final')->setUser($coach11)->setLogo($silverCup);

        $coach12 = new User();
        $coach12->setName('Julien')->setLastname('Tartampion')->setUsername('JUL')->setEmail('steve@genies.com');
        $coach12->setPassword('JUL')->setAge(33)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'b933c4d69ca3fe22cbe93dfdd0dcf27b.jpg');
        $infoCoach12 = new InfoCoach();
        $infoCoach12->setPrice(25)->setDescription("Salut à tous, Moi c'est JUL, je suis un joueur PC sur PUBG.
        J'ai commencé à jouer dès la sortie du jeu, a la saison 1.
        Ma spécialité ? 
        Les snipes ! 
        Actuellement membre de la mailleur team FR sur PUBG ... N'hésites pas à réserver une heure de coaching avec moi !")->setYoutube("https://www.youtube.com/watch?v=mMfxI3r_LyA&list=RDRMWBriHwVrI&index=2")->setRating(5)->setTeam($teams[2]);
        $coach12->setInfoCoach($infoCoach12);
        $recordPUBG3 = new Record();
        $recordPUBG3->setDescription('Met Asia Series Champion')->setUser($coach12)->setLogo($goldCup);
        $recordPUBG4 = new Record();
        $recordPUBG4->setDescription('GLL Grand Slam Semi-Final')->setUser($coach12)->setLogo($silverCup);
        
        $coach13 = new User();
        $coach13->setName('Michael')->setLastname('schwarzenegger')->setUsername('micha')->setEmail('kikoumicha@hotmail.com');
        $coach13->setPassword('micha')->setAge(25)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'rsz_fifty.jpg');
        $infoCoach13 = new InfoCoach();
        $infoCoach13->setPrice(40)->setDescription("Bonjour à tous,
        Je suis Michael, plus connu sous le nom de micha “In Game”. Je suis joueur compétitif sur PUBG depuis le démarrage des compétitions sur le jeu. Je me qualifie comme une personne avec beaucoup d’expérience. Pour résumer mon palmarès ,j’ai réalisé avec mon ex-équipe Deus eSport (équipe semi-pro) ainsi qu'en solo de bonnes performances online. J'ai depuis rejoint la structure Rogue !        
        Je vous donnerais les astuces pour améliorer votre aim, ou encore les prises de décisions à prendre en Squad. Partager mon savoir et de rendre les gens meilleurs est une des choses que j’affectionne. Pendant 1h de coaching avec moi vous en apprendrez beaucoup sur le jeu et sur ma spécialité ! Vous ne serez pas déçu ! 
        Avec moi tu es certain d'être au top et ta progression est ma priorité !")->setYoutube("https://www.youtube.com/watch?v=bpOSxM0rNPM&list=RDRMWBriHwVrI&index=3")->setRating(5)->setTeam($teams[1]);
        $coach13->setInfoCoach($infoCoach13);
        $recordPUBG5 = new Record();
        $recordPUBG5->setDescription('Met Asia Series Contenders 2019')->setUser($coach13)->setLogo($medal);
        $recordPUBG6 = new Record();
        $recordPUBG6->setDescription('PUBG Nation Cup 2018')->setUser($coach13)->setLogo($silverCup);

        $coach14 = new User();
        $coach14->setName('Bernard')->setLastname('Blond')->setUsername('eYesOnyOu')->setEmail('nardbe@hotmail.com');
        $coach14->setPassword('eYesOnyOu')->setAge(24)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'thumb-193085.jpg');
        $infoCoach14 = new InfoCoach();
        $infoCoach14->setPrice(30)->setDescription("Salut !
        Je me présente eYesOnyOu , 24 ans, je suis actuellement joueur professionnel.        
        Anciennement joueur sur ShootMania Storm (jeu sur lequel j’ai été champion du monde suite à mes victoires aux ESWC 2013, 2014 et 2015 ainsi qu’à la GA 2014 et 2015), j'ai commencé ma carrière sur PUBG depuis 2016 !        
        J'ai pu évoluer dans ces différents rôles au sein de plusieurs équipes, notamment chez Rogue où j'ai commencé à me faire connaître sur la scène internationale. J'ai ensuite eu la chance de pouvoir jouer chez Los Angeles Valiant lors de la première saison de l'Overwatch League.")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(5)->setTeam($teams[0]);
        $coach14->setInfoCoach($infoCoach14);
        $recordPUBG7 = new Record();
        $recordPUBG7->setDescription('PUBG Global Championship 2019')->setUser($coach14)->setLogo($medal);
        $recordPUBG8 = new Record();
        $recordPUBG8->setDescription('PUBG Nation Cup 2018 Champion')->setUser($coach14)->setLogo($goldCup);

        $coach15 = new User();
        $coach15->setName('Pauline')->setLastname('Joly')->setUsername('Zaria')->setEmail('zaria@hotmail.com');
        $coach15->setPassword('zaria')->setAge(40)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'soldier.jpg');
        $infoCoach15 = new InfoCoach();
        $infoCoach15->setPrice(25)->setDescription("Bonjour à toutes et à tous ! Je me présente, je m'appelle Pauline
        Coach sur PUBG depuis maintenant presque 2 ans, j'ai eu l'occasion de pouvoir aider pendant de nombreux mois des teams comme :        
        Team Nemesis : la meilleure équipe européenne Féminine sur PUBG.
         orKsGP White : La meilleure équipe française non professionnelle sur PUBG.
        Angry Titans : Une des meilleure équipe européenne que j'ai rejoins en tant qu'assistant coach et analyste et avec qui j'ai été vice champion d'europe début 2019 et Champion d'Europe dernièrement !
        Ayant un gros background d'encadrement d'équipe, que ce soit sportif ou e-sportif, depuis des années, j'ai acquis de grandes compétences dans l'encadrement, l'analyse et bien entendu la communication au sein d'un collectif de joueurs et joueuses.")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(4)->setTeam($teams[2]);
        $coach15->setInfoCoach($infoCoach15);
        $recordPUBG9 = new Record();
        $recordPUBG9->setDescription('PUBG Global Championship 2018')->setUser($coach15)->setLogo($medal);
        $recordPUBG10 = new Record();
        $recordPUBG10->setDescription('FaceIt Global Summit')->setUser($coach15)->setLogo($goldCup);

        //------------------------------------------Fake coach Super Smash Bros Ultimate-------------------------------------------------------------------------------//

        $coach16 = new User();
        $coach16->setName('kevin')->setLastname('Kikou')->setUsername('Griffith')->setEmail('paul79@hotmail.com');
        $coach16->setPassword('Grifith')->setAge(25)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'griffith.jpg');
        $infoCoach16 = new InfoCoach();
        $infoCoach16->setPrice(25)->setDescription("Bonjour à tous
        Je suis Griffith, joueur smash de la Team Relapse ! Véteran de la scène smash avec un palmarès conséquent sur smash4 (1er stunfest, Gamers Assembly, Rof) top 8 à un major US!        
        Je suis connu pour avoir joué à haut niveau pleins de persos sur smash4. Je serai parfait pour enseigner les joueurs débutants et intermédiaires sur smash Ultimate, car étant bon pédagogue je pourrai vous faire les bases de smash et les défauts de votre jeu !   
        N'hésitez pas à poser des questions/ requêtes si vous avez des demandes particulières ")->setYoutube("https://www.youtube.com/watch?v=RMWBriHwVrI&list=RDRMWBriHwVrI&start_radio=1")->setRating(5)->setTeam($teams[2]);
        $coach16->setInfoCoach($infoCoach16);
        $recordSSB11 = new Record();
        $recordSSB11->setDescription('SSB Ultimate Duel')->setUser($coach16)->setLogo($medal);
        $recordSSB12 = new Record();
        $recordSSB12->setDescription('SSBU 2v2')->setUser($coach16)->setLogo($goldCup);

        $coach17 = new User();
        $coach17->setName('Dan')->setLastname('Chong')->setUsername('Enki')->setEmail('steve@genies.com');
        $coach17->setPassword('Enki')->setAge(31)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'mario.jpeg');
        $infoCoach17 = new InfoCoach();
        $infoCoach17->setPrice(29)->setDescription("Salut à tous ! Je suis Enki joueur compétitif Smash depuis déjà plus de 10 ans. Je suis actuellement le meilleur Pikachu européen mais je peux vous aider à vous améliorer sur d'autres personnages ainsi que sur la compréhension globale du jeu.
        J'ai beaucoup d'expérience dans le domaine et je suis prêt à vous partager mes connaissances afin que vous soyez les meilleurs !  
        N'hésitez pas à me contacter si besoin pour plus d'infos !")->setYoutube("https://www.youtube.com/watch?v=mMfxI3r_LyA&list=RDRMWBriHwVrI&index=2")->setRating(5)->setTeam($teams[3]);
        $coach17->setInfoCoach($infoCoach17);
        $recordSSB13 = new Record();
        $recordSSB13->setDescription('SSB Ultimate Duel')->setUser($coach17)->setLogo($medal);
        $recordSSB14 = new Record();
        $recordSSB14->setDescription('SSBU 2v2')->setUser($coach17)->setLogo($goldCup);

        $coach18 = new User();
        $coach18->setName('Désiré')->setLastname('Bamboula')->setUsername('elaxio')->setEmail('kikoumicha@hotmail.com');
        $coach18->setPassword('elaxio')->setAge(20)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . '166155.jpg');
        $infoCoach18 = new InfoCoach();
        $infoCoach18->setPrice(40)->setDescription("Yo !
        Joueur de Super Smash Bros depuis de nombreuses années, je peux vous proposer une analyse détaillée et solutions sur vos problèmes rencontrés, que ce soit sur l'aspect technique ou mental.        
        N'hésitez pas à me preciser vos lacunes dans la feuille de cours, je suis là pour ça ! ")->setYoutube("https://www.youtube.com/watch?v=bpOSxM0rNPM&list=RDRMWBriHwVrI&index=3")->setRating(5)->setTeam($teams[1]);
        $coach18->setInfoCoach($infoCoach18);
        $recordSSB15 = new Record();
        $recordSSB15->setDescription('SSB Ultimate Duel 2019 Champion')->setUser($coach18)->setLogo($goldCup);
        $recordSSB16 = new Record();
        $recordSSB16->setDescription('SSBU 2v2 Playoff')->setUser($coach18)->setLogo($medal);

        $coach19 = new User();
        $coach19->setName('Jackie')->setLastname('Benji')->setUsername('Myollnir')->setEmail('nardbe@hotmail.com');
        $coach19->setPassword('Myollnir')->setAge(18)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'dAmhk3v.jpg');
        $infoCoach19 = new InfoCoach();
        $infoCoach19->setPrice(30)->setDescription("Yo!
        Je me présente, Myollnir, joueur de Super Smash Bros. depuis 2009, j'ai d'abord commencé sur SSBB, où j'ai atteint le Top 5 français, puis j'ai repris la compétition avec Smash4 en 2016 et j'ai atteint le Top10 en un peu moins d'1 an, pour finalement être classé 4ème à l'annonce de la sortie d'Ultimate. Egalement classé 27ème sur le ranking Européen de Smash4.        
        Etant très passionné par le jeu et possédant beaucoup de connaissances sur Smash, j'ai toujours apprécié aider les gens à progresser. Je peux vous faire progresser de façon générale sur le jeu et vous en expliquer les différents concepts mais aussi rentrer dans les détails de votre personnage à travers les combos et autres techniques avancées pour vous aider à l'exploiter au mieux.        
        Merci d'avoir lu & à bientôt sur le champ de bataille!")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(4)->setTeam($teams[4]);
        $coach19->setInfoCoach($infoCoach19);
        $recordSSB17 = new Record();
        $recordSSB17->setDescription('SSB Ultimate Duel Contenders')->setUser($coach19)->setLogo($medal);
        $recordSSB18 = new Record();
        $recordSSB18->setDescription('SSBU 2v2 Playoff')->setUser($coach19)->setLogo($medal);

        $coach20 = new User();
        $coach20->setName('Rafael')->setLastname('Nadal')->setUsername('Snixx,')->setEmail('snikers@hotmail.com');
        $coach20->setPassword('Snixx')->setAge(20)->setAvatar($this->serverUrl . $this->avatarDirectoryPath . 'link.png');
        $infoCoach20 = new InfoCoach();
        $infoCoach20->setPrice(9)->setDescription("Salut ! Je suis Snixx, joueur de Pikachu français, je vis en région parisienne ! Je suis un joueur très rapide et agressif, alors je pourrais t'aider à améliorer facette de ton jeu pour que tu deveniennes un monstre en compétition ! Mes gros points forts sont mon Advantage et mon Neutral, alors si tu souhaites en savoir plus sur ces aspects là (Juggle, Combo Game, Ledge Trap, Edge Guard, Baits, Conditionning...) Prends un cours avec moi !

        Lors de ma carrière de joueur il m'est arrivé de nombreuses fois de prendre des joueurs sous mon aile pour les aiguiller, les conseiller, les aider à devenir meilleurs, et ce sont ces expériences qui m'ont donné envie de devenir coach sur snowball.gg. Si tu es déterminé à devenir un bon joueur, peu importe quel est ton niveau, je tâcherai de répondre à tes attentes en te délivrant un coaching complet pour faire de toi un joueur confirmé.")->setYoutube("https://www.youtube.com/watch?v=3SDBTVcBUVs&list=RD3SDBTVcBUVs&start_radio=1")->setRating(5)->setTeam($teams[2]);
        $coach20->setInfoCoach($infoCoach20);
        $recordSSB19 = new Record();
        $recordSSB19->setDescription('SSB Ultimate Duel Contenders')->setUser($coach20)->setLogo($medal);
        $recordSSB20 = new Record();
        $recordSSB20->setDescription('SSBU 2v2 Playoff')->setUser($coach20)->setLogo($medal);

        //------------------------------------------------------------------------------------------------------------------------------//

        $user1 = new User();
        $user1->setName('Ulrich')->setLastname('Rodrigues')->setUsername('Ulrichinator')->setEmail('ulrich.rodrigues@gmail.com');
        $user1->setPassword('Ulrichinator')->setAge(49)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user2 = new User();
        $user2->setName('Ambroise')->setLastname('Verdier')->setUsername('AmbroiseVVV')->setEmail('ambroise.verdier@gmail.com');
        $user2->setPassword('AmbroiseVVV')->setAge(26)->setAvatar($this->serverUrl . $this->defaultAvatarPath);
        
        $user3 = new User();
        $user3->setName('Justine')->setLastname('Alexandre')->setUsername('kaxilec')->setEmail('ju.alexandre@gmail.com');
        $user3->setPassword('kaxilec')->setAge(28)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user4 = new User();
        $user4->setName('Victoire')->setLastname('Jacquet')->setUsername('Dovoz45')->setEmail('dovoz45@gmail.com');
        $user4->setPassword('Dovoz45')->setAge(18)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user5 = new User();
        $user5->setName('Augustin')->setLastname('Simon')->setUsername('Pebuvam13')->setEmail('pebuvam13@gmail.com');
        $user5->setPassword('Pebuvam13')->setAge(18)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user6 = new User();
        $user6->setName('Julienne')->setLastname('Lemaitre')->setUsername('JuPodoja')->setEmail('julienne.lemaitre@gmail.com');
        $user6->setPassword('JuPodoja')->setAge(21)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user7 = new User();
        $user7->setName('Esther')->setLastname('Guyon')->setUsername('4quDim4')->setEmail('esther.guyon@gmail.com');
        $user7->setPassword('4quDim4')->setAge(34)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user8 = new User();
        $user8->setName('Etienne')->setLastname('Gil')->setUsername('Nisaha')->setEmail('nisaha@gmail.com');
        $user8->setPassword('Nisaha')->setAge(23)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user9 = new User();
        $user9->setName('Arnaude')->setLastname('Guibert')->setUsername('Senicca')->setEmail('arnaude.guibert@gmail.com');
        $user9->setPassword('Senicca')->setAge(26)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user10 = new User();
        $user10->setName('Valentin')->setLastname('Ferreira')->setUsername('Danuxak')->setEmail('ferreira.valentin@gmail.com');
        $user10->setPassword('Danuxak')->setAge(17)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user11 = new User();
        $user11->setName('Simon')->setLastname('Fernandez')->setUsername('SimFerr')->setEmail('fernandez.simon@gmail.com');
        $user11->setPassword('SimFerr')->setAge(23)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user12 = new User();
        $user12->setName('Romain')->setLastname('Diop')->setUsername('Gorub')->setEmail('gorub@gmail.com');
        $user12->setPassword('Gorub')->setAge(30)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user13 = new User();
        $user13->setName('Felix')->setLastname('Pires')->setUsername('muzem')->setEmail('muzem56@gmail.com');
        $user13->setPassword('muzem')->setAge(32)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user14 = new User();
        $user14->setName('Ariane')->setLastname('Royer')->setUsername('Arroyyy')->setEmail('ariane.royer@gmail.com');
        $user14->setPassword('Arroyyy')->setAge(23)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user15 = new User();
        $user15->setName('Juliette')->setLastname('Crespoune')->setUsername('Crespounettt')->setEmail('crespounettt@gmail.com');
        $user15->setPassword('Crespounettt')->setAge(20)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user16 = new User();
        $user16->setName('Aurelie')->setLastname('Dumont')->setUsername('Kafuc')->setEmail('aurelie.dumont@gmail.com');
        $user16->setPassword('Kafuc')->setAge(33)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user17 = new User();
        $user17->setName('Amelie')->setLastname('Jolly')->setUsername('Amell86')->setEmail('amell86@gmail.com');
        $user17->setPassword('Amell86')->setAge(20)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user18 = new User();
        $user18->setName('Hector')->setLastname('Morel')->setUsername('kaxilec')->setEmail('hector.morel@gmail.com');
        $user18->setPassword('kaxilec')->setAge(28)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user19 = new User();
        $user19->setName('Ariane')->setLastname('Gicquel')->setUsername('Ariagi')->setEmail('ariagi@gmail.com');
        $user19->setPassword('Ariagi')->setAge(16)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user20 = new User();
        $user20->setName('Pierre')->setLastname('Binet')->setUsername('Binatrox')->setEmail('binatrox@gmail.com');
        $user20->setPassword('Binatrox')->setAge(24)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user21 = new User();
        $user21->setName('Gaston')->setLastname('Langlois')->setUsername('48jawal48')->setEmail('gaston.langlois@gmail.com');
        $user21->setPassword('48jawal48')->setAge(21)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user22 = new User();
        $user22->setName('Felicien')->setLastname('Salmon')->setUsername('Felisale')->setEmail('felicien.salmon@gmail.com');
        $user22->setPassword('Felisale')->setAge(28)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user23 = new User();
        $user23->setName('Isabelle')->setLastname('Perez')->setUsername('sedaw')->setEmail('sedaw55@gmail.com');
        $user23->setPassword('sedaw')->setAge(17)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user24 = new User();
        $user24->setName('Romain')->setLastname('Barbe')->setUsername('covebok')->setEmail('covebok@gmail.com');
        $user24->setPassword('covebok')->setAge(16)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user25 = new User();
        $user25->setName('Philippe')->setLastname('Sauvage')->setUsername('Sauvarxxx')->setEmail('sauvarxxx@gmail.com');
        $user25->setPassword('Sauvarxxx')->setAge(37)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user26 = new User();
        $user26->setName('Benoit')->setLastname('Suzette')->setUsername('kasib')->setEmail('suzette.benoit@gmail.com');
        $user26->setPassword('kasib')->setAge(23)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user27 = new User();
        $user27->setName('Gerald')->setLastname('Brault')->setUsername('MOSUS')->setEmail('mosus@gmail.com');
        $user27->setPassword('MOSUS')->setAge(19)->setAvatar($this->serverUrl . $this->defaultAvatarPath);
        $user28 = new User();
        $user28->setName('Eugene')->setLastname('Bailly')->setUsername('WoNeseD')->setEmail('wonesed@gmail.com');
        $user28->setPassword('WoNeseD')->setAge(22)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user29 = new User();
        $user29->setName('Brigitte')->setLastname('Bourdon')->setUsername('CekiKPT')->setEmail('ckikpt@gmail.com');
        $user29->setPassword('CekiKPT')->setAge(23)->setAvatar($this->serverUrl . $this->defaultAvatarPath);

        $user30 = new User();
        $user30->setName('Maximilien')->setLastname('Berthier')->setUsername('Picccoovv')->setEmail('picccoovv@gmail.com');
        $user30->setPassword('Picccoovv')->setAge(20)->setAvatar($this->serverUrl . $this->defaultAvatarPath); 
        
        $review1 = new Review();
        $review1->setComment("Coaching de compète le Sharingan de snixx n'a pas mis longtemps à percevoir mes erreurs.grace à son entraînement jai mis un pied en dehors de la matrice ,je vais reprendre un cour c'est suuuuur.merci a toi jeune zigoto des îles et force pour la suite")->setRating(5)->setUser($user1)->setInfoCoach($infoCoach16);

        $review2 = new Review();
        $review2->setComment("Je recommande les yeux fermés! Une heure de coaching très bien organisé et des explications au top ! Début d'une longue série de leçon !")->setRating(5)->setUser($user2)->setInfoCoach($infoCoach16);

        $review3 = new Review();
        $review3->setComment("Attentif, expert de greninja, et pédagogue, j'ai pu avoir réponse à toutes mes questions, c'était parfait! Plus qu'à appliquer les conseils pour voir ce que ça donne ;) Si vous avez envie de consolider vos connaissances n'hésitez pas Elexiao a tout ce qu'il faut en plus d'être chill ! 10/10 would recommend")->setRating(5)->setUser($user2)->setInfoCoach($infoCoach17);

        $review4 = new Review();
        $review4->setComment("J'ai pu sentir beaucoup de progression et des resultats grace au conseil du 1er cours et maintenant voila le deuxieme cours avec Elex, toujours un plaisir d'avoir pu apprendre encore sur mon personnage. On sent beaucoup de preparation sur les themes souhaitant etre aborder, et un bon partage de connaissance, ensuite mise en pratique a travers des parties. Il a su deceler mes differentes erreurs durant nos parties et me dire sur quoi encore travailler. Merci encore pour les conseils et le suivi cela est vraiment plaisant :)")->setRating(4)->setUser($user4)->setInfoCoach($infoCoach17);

        $review5 = new Review();
        $review5->setComment("J'ai vraiment beaucoup apprecié l'heure de coaching , il n'a pas hesité à prolonger le cours de 30 minutes afin de bien aborder tout les points que je voulais travailler. Un coach vraiment à l'écoute, attentionné. Il a su pointer ce qui n'allait pas dans mon jeu et proposer des solutions afin de l'améliorer. De plus, un suivi sur mon évolution m'a été proposé, que je trouve être une excellente idée. En bref, je n'hésiterai clairement pas à reprendre d'autres heures avec Elexiao car une seule heure n'est pas suffisant :)")->setRating(5)->setUser($user9)->setInfoCoach($infoCoach11);

        $review6 = new Review();
        $review6->setComment("Une excellente première session dans laquelle Tartampion à mis en avant mes principaux défauts, en m'indiquant les manières de les corriger afin de m'améliorer, un vrai plaisir de prendre un cours avec lui")->setRating(4)->setUser($user10)->setInfoCoach($infoCoach18);

        $review7 = new Review();
        $review7->setComment("Première séance avec Bidul, il n'a pas hésité à faire durer un peu le cours pour pouvoir parler des différents points définis. Il explique vraiment bien, très agréable et donc ça en fait un excellent coach. Début un long parcourt de coaching pour ma part ^^")->setRating(4)->setUser($user13)->setInfoCoach($infoCoach18);

        $review8 = new Review();
        $review8->setComment("Cours nickel. Il ne regarde pas l'heure du coup aucune pression, on a pu discuté des points tranquillement, plein de conseils et d'exemples, et puis les liens ! C'était vraiment bien, merci encore !")->setRating(5)->setUser($user20)->setInfoCoach($infoCoach19);

        $review9 = new Review();
        $review9->setComment("Merci NtK pour cette Gaming Session d'anniversaire :)))")->setRating(5)->setUser($user19)->setInfoCoach($infoCoach19);

        $review10 = new Review();
        $review10->setComment("NTK a mis en lumière les points que je dois améliorer et il ne compte pas ses heures. Ils m'a mis en relation avec des personnes pour m'améliorer. Il est passionné et passionnant, je recommande !")->setRating(3)->setUser($user21)->setInfoCoach($infoCoach19);

        $review11 = new Review();
        $review11->setComment("Super coaching , très pédago' , a l'écoute , cependant donne beaucoup de devoir mais")->setRating(5)->setUser($user8)->setInfoCoach($infoCoach1);

        $review12 = new Review();
        $review12->setComment("Super coaching ! Je recommande, j'ai le sentiment d'avoir appris beaucoup de choses qui me manquaient pour mon niveau. Très satisfait, je reprendrai une session une fois que j'aurai le sentiment d'avoir assimilé ce qu'on a souligné et d'avoir besoin de nouveau pour progresser")->setRating(5)->setUser($user4)->setInfoCoach($infoCoach1);

        $review13 = new Review();
        $review13->setComment("Compte tenu de mon niveau de débutant, il m'a proposé un coaching vraiment différent de ceux qu'ils fait normalement, coaching qui était beaucoup plus adapté à mes erreurs du coup, si ça, ce n'est pas un coaching personnalisé.. :) les explications sont très claires et donnent envie de s'investir dans le jeu, je compte reprendre des sessions de coaching régulièrement !")->setRating(5)->setUser($user24)->setInfoCoach($infoCoach2);

        $review14 = new Review();
        $review14->setComment("Il a bien repris les points de l'entraînement d'avant et a regardé la progression et ce qu'il reste a travailler, très bonne session de coaching !")->setRating(4)->setUser($user22)->setInfoCoach($infoCoach2);

        $review15 = new Review();
        $review15->setComment("Très sympathique ! Donne envie de progresser")->setRating(4)->setUser($user7)->setInfoCoach($infoCoach2);

        $review16 = new Review();
        $review16->setComment("Deuxième rendez-vous ! Toujours la même impression qu'au premier, il sait mettre à l'aise ce qui décoince légèrement ! Agréablement surpris qu'il ai un suivi pour chaque élève aussi, ce qui prouve son engagement et son sérieux dans sa démarche de coaching, évidemment ... Je le recommande !")->setRating(4)->setUser($user3)->setInfoCoach($infoCoach2);

        $review17 = new Review();
        $review17->setComment("coach au top. pedagoge, bons conseils, je vais reprendre :)")->setRating(5)->setUser($user28)->setInfoCoach($infoCoach3);

        $review18 = new Review();
        $review18->setComment("Coach instructif et calme ! Aucune prétention dans ses paroles ! Analyse de défauts très bien trouvés ! Je recommande x 2")->setRating(5)->setUser($user26)->setInfoCoach($infoCoach4);

        $review19 = new Review();
        $review19->setComment("Comme la fois d'avant, coach était à l'écoute et nous avons bien analyser ce qui à été fait comme travaille et aussi ce qu'il restait a améliorer. Toujours de bonne humeur avec une ambiance Joviale! A bientôt Coach.")->setRating(3)->setUser($user14)->setInfoCoach($infoCoach5);

        $review20 = new Review();
        $review20->setComment("Coach qui est à l'écoute et qui n'hésite pas a prendre le temps nécessaire pour expliquer/montrer les points important lié à l'évolution du joueur. La session a été bénéfique aussi bien dans le côté technique que dans le côté tactique, le tout dans une bonne ambiance! Merci à Coach FuuRy et j'espère à bientôt")->setRating(5)->setUser($user16)->setInfoCoach($infoCoach6);

        $review21 = new Review();
        $review21->setComment("Très pro, à l'écoute et de bons conseils. Je vous recommande la méthode Un33d sans hésiter.")->setRating(4)->setUser($user10)->setInfoCoach($infoCoach7);

        $review22 = new Review();
        $review22->setComment("Coaching Hyper instructif et rapport tarif/prestation incroyable. A recommander les yeux fermés")->setRating(5)->setUser($user19)->setInfoCoach($infoCoach7);

        $review23 = new Review();
        $review23->setComment("Sympa et d'excellents conseils. Un coaching OP ;-)")->setRating(5)->setUser($user1)->setInfoCoach($infoCoach8);

        $review24 = new Review();
        $review24->setComment("Excellent cours, adapté à mes besoins, sans perte de temps et blabla inutile. Parfait ! Gros travail sur le mulligan.")->setRating(5)->setUser($user17)->setInfoCoach($infoCoach8);

        $review25 = new Review();
        $review25->setComment("Une bonne première session de coaching, Un33d a su être à mon écoute et aborder avec brio les différentes thématiques demandées. Seul bémol, une heure c'est court, surtout losqu'on joue des decks très contrôle comme moi, la prochaine fois je ferais une session de 2 ou 3h selon les disponibilités d'Un33d")->setRating(5)->setUser($user5)->setInfoCoach($infoCoach8);

        $review26 = new Review();
        $review26->setComment("Excellent coach, et ce à tous les niveaux! Posé au calme, session fortement instructive, je le recommande vivement à tous les joueurs d’hearthstone qui souhaitent progresser!!!")->setRating(4)->setUser($user8)->setInfoCoach($infoCoach9);
        
        $review27 = new Review();
        $review27->setComment("Très pédagogue ce monsieur ! Il a su me montrer ce que je cherchais au travers des games que l'on a fait ensemble. Merci ! :)")->setRating(5)->setUser($user28)->setInfoCoach($infoCoach9);

        $review28 = new Review();
        $review28->setComment("Très bon cours, j'ai appris beaucoup de choses, beaucoup de bons conseils, sympathique, avenant, et accessible. Depuis que ce cours, je top deck à tous les tours, et je choisis mon compagnon animal. Merci !")->setRating(4)->setUser($user18)->setInfoCoach($infoCoach10);

        $review29 = new Review();
        $review29->setComment("Super coach, conseils clairs et progrès fulgurants durant la séance.")->setRating(5)->setUser($user22)->setInfoCoach($infoCoach10);

        $review30 = new Review();
        $review30->setComment("Un coaching très instructif, capacité à enseigner naturel et pédagogue. Compétant, humain et sait trouver les points faibles. Je le conseille fortement !")->setRating(5)->setUser($user16)->setInfoCoach($infoCoach3);

        $review31 = new Review();
        $review31->setComment("Coach à la fois pédagogique et accessible. Il met à l'aise dès le début du cours, cours qui lui est passé vite! Beaucoup de connaissance du jeu qui sont visibles aussi bien dans ses analyses que dans ses conseils.")->setRating(5)->setUser($user8)->setInfoCoach($infoCoach10);

        $review32 = new Review();
        $review32->setComment("Session très instructive. Des conseils simples et ludiques. Coach très à l'écoute de nos questions et apportant soit des réponses soit un début de réflexion. Merci infiniment")->setRating(5)->setUser($user20)->setInfoCoach($infoCoach11);

        $review33 = new Review();
        $review33->setComment("Une session assez générale mais utile sur de nombreux points, le coach a un sens de la pédagogi")->setRating(5)->setUser($user22)->setInfoCoach($infoCoach11);

        $review34 = new Review();
        $review34->setComment("Très bon cours! T'as su cerner mes attentes, me donner de vrais bons conseils concrets (facilement compréhensibles et facilement applicables). Très ouvert à mes petites remarques personnelles. Tu donnes plusieurs pistes d'amélioration (sites, streamers, exercices) et le petit finish en FàQ pour des questions plus précises, j'ai pas pensé à le préparer du coup j'ai improvisé mais ça peut être très utile si anticipé. Rien à redire de mon côté pour ce premier cours! :D")->setRating(4)->setUser($user22)->setInfoCoach($infoCoach12);

        $review35 = new Review();
        $review35->setComment("Un coach génial qui a énormément d’expérience dans le milieu et qui est capable de donner des conseilles utile dans tout les domaines et qui peut que vous faire progresser vous et votre team je vous le conseille +++")->setRating(4)->setUser($user5)->setInfoCoach($infoCoach13);

        $review36 = new Review();
        $review36->setComment("Merci au Coach pour ces deux heures de coaching auprès de notre équipe académique plus que bénéfique. Il a été très agréable et très pédagogique dans la façon dont il a aidé notre équipe. Et vous recommandons de le sollicité. Un coach excellent")->setRating(4)->setUser($user17)->setInfoCoach($infoCoach14);

        $review37 = new Review();
        $review37->setComment("Coach très concis et va directement à l'essentiel afin de capter l'attention totale de chacun des joueurs. Je recommandes fortement ces services, très pédagogue rien à redire.")->setRating(5)->setUser($user15)->setInfoCoach($infoCoach15);

        $review38 = new Review();
        $review38->setComment("Gentil, Passionnant, Formidable !")->setRating(5)->setUser($user12)->setInfoCoach($infoCoach15);

        $review39 = new Review();
        $review39->setComment("Excellent coach, très pédagogue, très pro. Sait s'adapter en fonction des différents besoins et n'hésite pas à passer du temps supplémentaire. Elle a vue des grosses erreurs dans mon gameplay que je n'arrivais pas à voir ! Je recommande !")->setRating(5)->setUser($user11)->setInfoCoach($infoCoach14);

        $review40 = new Review();
        $review40->setComment("Coach que je recommande pour son sérieux et son envie de partage")->setRating(5)->setUser($user2)->setInfoCoach($infoCoach13);
        
       
        $manager->persist($medal);        
        $manager->persist($bronzeCup);
        $manager->persist($silverCup);
        $manager->persist($goldCup);
        $manager->persist($admin);
        $manager->persist($coach1);
        $manager->persist($coach2);
        $manager->persist($coach3);
        $manager->persist($coach4);
        $manager->persist($coach5); 
        $manager->persist($coach6);
        $manager->persist($coach7);
        $manager->persist($coach8);
        $manager->persist($coach9);
        $manager->persist($coach10);        
        $manager->persist($coach11);
        $manager->persist($coach12);
        $manager->persist($coach13);
        $manager->persist($coach14);
        $manager->persist($coach15); 
        $manager->persist($coach16);
        $manager->persist($coach17);
        $manager->persist($coach18);
        $manager->persist($coach19);
        $manager->persist($coach20);        
        $manager->persist($infoCoach1);
        $manager->persist($infoCoach2);
        $manager->persist($infoCoach3);
        $manager->persist($infoCoach4);
        $manager->persist($infoCoach5);          
        $manager->persist($infoCoach6);
        $manager->persist($infoCoach7);
        $manager->persist($infoCoach8);
        $manager->persist($infoCoach9);
        $manager->persist($infoCoach10); 
        $manager->persist($infoCoach11);
        $manager->persist($infoCoach12);
        $manager->persist($infoCoach13);
        $manager->persist($infoCoach14);
        $manager->persist($infoCoach15);          
        $manager->persist($infoCoach16);
        $manager->persist($infoCoach17);
        $manager->persist($infoCoach18);
        $manager->persist($infoCoach19);
        $manager->persist($infoCoach20);
        $manager->persist($user1);
        $manager->persist($user2);
        $manager->persist($user3);
        $manager->persist($user4);
        $manager->persist($user5);
        $manager->persist($user6);
        $manager->persist($user7);
        $manager->persist($user8);
        $manager->persist($user9);
        $manager->persist($user10);
        $manager->persist($user11);
        $manager->persist($user12);
        $manager->persist($user13);
        $manager->persist($user14);
        $manager->persist($user15);
        $manager->persist($user16);
        $manager->persist($user17);
        $manager->persist($user18);
        $manager->persist($user19);
        $manager->persist($user20);
        $manager->persist($user21);
        $manager->persist($user22);
        $manager->persist($user23);
        $manager->persist($user24);
        $manager->persist($user25);
        $manager->persist($user26);
        $manager->persist($user27);
        $manager->persist($user28);
        $manager->persist($user29);
        $manager->persist($user30);
        $manager->persist($review1);
        $manager->persist($review2);
        $manager->persist($review3);
        $manager->persist($review4);
        $manager->persist($review5);
        $manager->persist($review6);
        $manager->persist($review7);
        $manager->persist($review8);
        $manager->persist($review9);
        $manager->persist($review10);
        $manager->persist($review11);
        $manager->persist($review12);
        $manager->persist($review13);
        $manager->persist($review14);
        $manager->persist($review15);
        $manager->persist($review16);
        $manager->persist($review17);
        $manager->persist($review18);
        $manager->persist($review19);
        $manager->persist($review20);
        $manager->persist($review21);        
        $manager->persist($review22);
        $manager->persist($review23);
        $manager->persist($review24);
        $manager->persist($review25);
        $manager->persist($review26);
        $manager->persist($review27);
        $manager->persist($review28);
        $manager->persist($review29);
        $manager->persist($review30);
        $manager->persist($review31);
        $manager->persist($review32);
        $manager->persist($review33);
        $manager->persist($review34);
        $manager->persist($review35);
        $manager->persist($review36);
        $manager->persist($review37);
        $manager->persist($review38);
        $manager->persist($review39);
        $manager->persist($review40);
        $manager->persist($record1);
        $manager->persist($record2);
        $manager->persist($record3);
        $manager->persist($record4);
        $manager->persist($record5);
        $manager->persist($record6);
        $manager->persist($record7);
        $manager->persist($record8);
        $manager->persist($record9);
        $manager->persist($record10);
        $manager->persist($recordPUBG1);
        $manager->persist($recordPUBG2);
        $manager->persist($recordPUBG3);
        $manager->persist($recordPUBG4);
        $manager->persist($recordPUBG5);
        $manager->persist($recordPUBG6);
        $manager->persist($recordPUBG7);
        $manager->persist($recordPUBG8);
        $manager->persist($recordPUBG9);
        $manager->persist($recordPUBG10);
        $manager->persist($recordSSB11);
        $manager->persist($recordSSB12);
        $manager->persist($recordSSB13);
        $manager->persist($recordSSB14);
        $manager->persist($recordSSB15);
        $manager->persist($recordSSB16);
        $manager->persist($recordSSB17);
        $manager->persist($recordSSB18);
        $manager->persist($recordSSB19);
        $manager->persist($recordSSB20);
        $manager->persist($recordFIFA1);
        $manager->persist($recordFIFA2);
        $manager->persist($recordFIFA3);
        $manager->persist($recordFIFA4);
        $manager->persist($recordFIFA5);
        $manager->persist($recordFIFA6);
        $manager->persist($recordFIFA7);
        $manager->persist($recordFIFA8);
        $manager->persist($recordFIFA9);
        $manager->persist($recordFIFA10);
        $manager->persist($recordFIFA11);
        $manager->persist($recordFIFA12);
        $manager->persist($recordFIFA13);
        $manager->persist($recordFIFA14);
        $manager->persist($recordFIFA15);        
        
        
        foreach ($games as $game) {

            if($game->getName() == 'Overwatch') {
                $game->setEditor("Blizzard");
                $game->setHeaderBackground('https://www.asgard.gg/wp-content/uploads/2015/11/overwatch-1.jpg');
                $game->setDescription("Composez votre team de 6 héros et remportez les objectifs !");
                $infoCoach1->setGame($game);
                $infoCoach2->setGame($game);
                $infoCoach3->setGame($game);
                $infoCoach4->setGame($game);
                $infoCoach5->setGame($game);
            } 
            if($game->getName() == 'FIFA 19') {
                $game->setEditor("EA Sports");
                $game->setHeaderBackground('https://compass-ssl.xbox.com/assets/5e/5e/5e5e79d1-1ea9-4280-8ead-86dc202122f3.jpg?n=FIFA-19_Multi-Feature-1084_FUT_1600x600.jpg');
                $game->setDescription("Le meilleur jeu de Football de tous les temps");
                $infoCoach6->setGame($game);
                $infoCoach7->setGame($game);
                $infoCoach8->setGame($game);
                $infoCoach9->setGame($game);
                $infoCoach10->setGame($game);
            }
            if($game->getName() == 'PUBG') {
                $game->setEditor("pubg corporation");
                $game->setHeaderBackground('https://mon-set-up-gaming.fr/wp-content/uploads/2017/10/visual_main.jpg');
                $game->setDescription("Soyez le dernier debout sur le champ de bataille");
                $infoCoach11->setGame($game);
                $infoCoach12->setGame($game);
                $infoCoach13->setGame($game);
                $infoCoach14->setGame($game);
                $infoCoach15->setGame($game);
            }
            if($game->getName() == 'Super Smash Bros Ultimate') {
                $game->setEditor("Nintendo");
                $game->setHeaderBackground('https://images2.alphacoders.com/927/thumb-1920-927337.png');
                $game->setDescription("Des mondes de jeux et des combattants légendaires se retrouvent pour l’affrontement ultime dans le nouvel opus de la série Super Smash Bros"); 
                $infoCoach16->setGame($game);
                $infoCoach17->setGame($game);
                $infoCoach18->setGame($game);
                $infoCoach19->setGame($game);
                $infoCoach20->setGame($game);               
            }
        }   

        foreach ($roles as $role) {

            if ($role->getCode() == 'ROLE_ADMIN') {
                $role->setName('admin');
                $admin->setRole($role);
            }

            if ($role->getCode() == 'ROLE_COACH') {
                $role->setName('coach');
                $coach1->setRole($role);
                $coach2->setRole($role);
                $coach3->setRole($role);
                $coach4->setRole($role);
                $coach5->setRole($role);
                $coach6->setRole($role);
                $coach7->setRole($role);
                $coach8->setRole($role);
                $coach9->setRole($role);
                $coach10->setRole($role);
                $coach11->setRole($role);
                $coach12->setRole($role);
                $coach13->setRole($role);
                $coach14->setRole($role);
                $coach15->setRole($role);
                $coach16->setRole($role);
                $coach17->setRole($role);
                $coach18->setRole($role);
                $coach19->setRole($role);
                $coach20->setRole($role);
            } 

            if ($role->getCode() == 'ROLE_USER') {
                $role->setName('user');
                
            } 
        }
               
          
        $manager->flush();
    }
}
