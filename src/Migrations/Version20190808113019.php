<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190808113019 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE platform (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, role_id INT NOT NULL, info_coach_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, lastname VARCHAR(50) NOT NULL, username VARCHAR(50) NOT NULL, age SMALLINT NOT NULL, avatar VARCHAR(255) DEFAULT NULL, password VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, slug VARCHAR(50) DEFAULT NULL, INDEX IDX_88BDF3E9D60322AC (role_id), UNIQUE INDEX UNIQ_88BDF3E92B252DFB (info_coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE unavailability (id INT AUTO_INCREMENT NOT NULL, info_coach_id INT NOT NULL, client_id INT DEFAULT NULL, start DATETIME NOT NULL, end DATETIME NOT NULL, start_unix INT NOT NULL, end_unix INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_F0016D12B252DFB (info_coach_id), INDEX IDX_F0016D119EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, info_coach_id INT NOT NULL, rating SMALLINT NOT NULL, comment LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_794381C6A76ED395 (user_id), INDEX IDX_794381C62B252DFB (info_coach_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B6BD307FF624B39D (sender_id), INDEX IDX_B6BD307FCD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, editor VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, header_background VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL, is_active TINYINT(1) NOT NULL, slug VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game_platform (game_id INT NOT NULL, platform_id INT NOT NULL, INDEX IDX_92162FEDE48FD905 (game_id), INDEX IDX_92162FEDFFE6496F (platform_id), PRIMARY KEY(game_id, platform_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, logo_id INT NOT NULL, description VARCHAR(150) NOT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_9B349F91A76ED395 (user_id), INDEX IDX_9B349F91F98F144A (logo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE team (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, logo VARCHAR(255) DEFAULT NULL, logo_description VARCHAR(50) DEFAULT NULL, youtube VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, insta VARCHAR(255) DEFAULT NULL, twitch VARCHAR(255) DEFAULT NULL, is_active TINYINT(1) NOT NULL, slug VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE info_coach (id INT AUTO_INCREMENT NOT NULL, team_id INT DEFAULT NULL, game_id INT NOT NULL, price SMALLINT NOT NULL, description LONGTEXT NOT NULL, rating SMALLINT DEFAULT NULL, youtube VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, insta VARCHAR(255) DEFAULT NULL, twitch VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_3500A839296CD8AE (team_id), INDEX IDX_3500A839E48FD905 (game_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE logo (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, logo_description VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E9D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E92B252DFB FOREIGN KEY (info_coach_id) REFERENCES info_coach (id)');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D12B252DFB FOREIGN KEY (info_coach_id) REFERENCES info_coach (id)');
        $this->addSql('ALTER TABLE unavailability ADD CONSTRAINT FK_F0016D119EB6921 FOREIGN KEY (client_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C62B252DFB FOREIGN KEY (info_coach_id) REFERENCES info_coach (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE game_platform ADD CONSTRAINT FK_92162FEDE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE game_platform ADD CONSTRAINT FK_92162FEDFFE6496F FOREIGN KEY (platform_id) REFERENCES platform (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE record ADD CONSTRAINT FK_9B349F91F98F144A FOREIGN KEY (logo_id) REFERENCES logo (id)');
        $this->addSql('ALTER TABLE info_coach ADD CONSTRAINT FK_3500A839296CD8AE FOREIGN KEY (team_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE info_coach ADD CONSTRAINT FK_3500A839E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E9D60322AC');
        $this->addSql('ALTER TABLE game_platform DROP FOREIGN KEY FK_92162FEDFFE6496F');
        $this->addSql('ALTER TABLE unavailability DROP FOREIGN KEY FK_F0016D119EB6921');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FCD53EDB6');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F91A76ED395');
        $this->addSql('ALTER TABLE game_platform DROP FOREIGN KEY FK_92162FEDE48FD905');
        $this->addSql('ALTER TABLE info_coach DROP FOREIGN KEY FK_3500A839E48FD905');
        $this->addSql('ALTER TABLE info_coach DROP FOREIGN KEY FK_3500A839296CD8AE');
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E92B252DFB');
        $this->addSql('ALTER TABLE unavailability DROP FOREIGN KEY FK_F0016D12B252DFB');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C62B252DFB');
        $this->addSql('ALTER TABLE record DROP FOREIGN KEY FK_9B349F91F98F144A');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE platform');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE unavailability');
        $this->addSql('DROP TABLE review');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE game_platform');
        $this->addSql('DROP TABLE record');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE info_coach');
        $this->addSql('DROP TABLE logo');
    }
}
