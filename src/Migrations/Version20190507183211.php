<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190507183211 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE tag_meal (id INT AUTO_INCREMENT NOT NULL, tag_id INT NOT NULL, meal_id INT NOT NULL, INDEX IDX_70F9312ABAD26311 (tag_id), INDEX IDX_70F9312A639666D6 (meal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ingredient_meal (id INT AUTO_INCREMENT NOT NULL, ingredient_id INT NOT NULL, meal_id INT NOT NULL, INDEX IDX_C0A73E0A933FE08C (ingredient_id), INDEX IDX_C0A73E0A639666D6 (meal_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag_meal ADD CONSTRAINT FK_70F9312ABAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE tag_meal ADD CONSTRAINT FK_70F9312A639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id)');
        $this->addSql('ALTER TABLE ingredient_meal ADD CONSTRAINT FK_C0A73E0A933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredients (id)');
        $this->addSql('ALTER TABLE ingredient_meal ADD CONSTRAINT FK_C0A73E0A639666D6 FOREIGN KEY (meal_id) REFERENCES meal (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE tag_meal');
        $this->addSql('DROP TABLE ingredient_meal');
    }
}
