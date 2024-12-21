<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202162040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article ADD prix DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7440455E7769B0F ON client (surname)');
        $this->addSql('ALTER TABLE demande_article DROP INDEX UNIQ_100989B380E95E18, ADD INDEX IDX_32CDB5C980E95E18 (demande_id)');
        $this->addSql('ALTER TABLE demande_article DROP INDEX UNIQ_100989B37294869C, ADD INDEX IDX_32CDB5C97294869C (article_id)');
        $this->addSql('ALTER TABLE demande_article MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE demande_article DROP FOREIGN KEY FK_100989B37294869C');
        $this->addSql('ALTER TABLE demande_article DROP FOREIGN KEY FK_100989B380E95E18');
        $this->addSql('DROP INDEX `primary` ON demande_article');
        $this->addSql('ALTER TABLE demande_article DROP id, DROP qte');
        $this->addSql('ALTER TABLE demande_article ADD CONSTRAINT FK_32CDB5C980E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande_article ADD CONSTRAINT FK_32CDB5C97294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE demande_article ADD PRIMARY KEY (demande_id, article_id)');
        $this->addSql('ALTER TABLE dette_article DROP INDEX UNIQ_2E067F93E11400A1, ADD INDEX IDX_C5321D58E11400A1 (dette_id)');
        $this->addSql('ALTER TABLE dette_article DROP INDEX UNIQ_2E067F937294869C, ADD INDEX IDX_C5321D587294869C (article_id)');
        $this->addSql('ALTER TABLE dette_article MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE dette_article DROP FOREIGN KEY FK_2E067F937294869C');
        $this->addSql('ALTER TABLE dette_article DROP FOREIGN KEY FK_2E067F93E11400A1');
        $this->addSql('DROP INDEX `primary` ON dette_article');
        $this->addSql('ALTER TABLE dette_article DROP id, DROP qte');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D58E11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_C5321D587294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dette_article ADD PRIMARY KEY (dette_id, article_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP prix');
        $this->addSql('DROP INDEX UNIQ_C7440455E7769B0F ON client');
        $this->addSql('ALTER TABLE dette_article DROP INDEX IDX_C5321D58E11400A1, ADD UNIQUE INDEX UNIQ_2E067F93E11400A1 (dette_id)');
        $this->addSql('ALTER TABLE dette_article DROP INDEX IDX_C5321D587294869C, ADD UNIQUE INDEX UNIQ_2E067F937294869C (article_id)');
        $this->addSql('ALTER TABLE dette_article DROP FOREIGN KEY FK_C5321D58E11400A1');
        $this->addSql('ALTER TABLE dette_article DROP FOREIGN KEY FK_C5321D587294869C');
        $this->addSql('ALTER TABLE dette_article ADD id INT AUTO_INCREMENT NOT NULL, ADD qte INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_2E067F937294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE dette_article ADD CONSTRAINT FK_2E067F93E11400A1 FOREIGN KEY (dette_id) REFERENCES dette (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE demande_article DROP INDEX IDX_32CDB5C980E95E18, ADD UNIQUE INDEX UNIQ_100989B380E95E18 (demande_id)');
        $this->addSql('ALTER TABLE demande_article DROP INDEX IDX_32CDB5C97294869C, ADD UNIQUE INDEX UNIQ_100989B37294869C (article_id)');
        $this->addSql('ALTER TABLE demande_article DROP FOREIGN KEY FK_32CDB5C980E95E18');
        $this->addSql('ALTER TABLE demande_article DROP FOREIGN KEY FK_32CDB5C97294869C');
        $this->addSql('ALTER TABLE demande_article ADD id INT AUTO_INCREMENT NOT NULL, ADD qte INT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE demande_article ADD CONSTRAINT FK_100989B37294869C FOREIGN KEY (article_id) REFERENCES article (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE demande_article ADD CONSTRAINT FK_100989B380E95E18 FOREIGN KEY (demande_id) REFERENCES demande (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
