<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Dette;
use App\Entity\Client;
use App\Entity\Detail;
use App\Entity\Statut;
use App\Entity\Article;
use App\Entity\Demande;
use App\Entity\Payment;
use App\Entity\Paiement;
use App\Entity\Dette_Article;
use App\Entity\Demande_Article;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{

    
    public function __construct(private UserPasswordHasherInterface $encoder)
    {
    }



public function load(ObjectManager $manager): void
{
    $articles = [];
    
    for ($i = 1; $i <= 6; $i++) {
        $article = new Article();
        $article->setNomArticle("Article_$i");
        $article->setQteStock(50 + $i * 10);
        $article->setPrix(1000.0 * $i); 
        $manager->persist($article);
        $articles[] = $article;
    }

    for ($i = 1; $i <= 20; $i++) {
        $client = new Client();
        $client->setSurname("surname" . $i);
        $client->setAdresse("adresse" . $i);
        $client->setTelephone("77100101" . $i);

        if ($i % 2 == 0) { 
            $user = new User();
            $user->setLogin('login' . $i);
            $passwordHasher = $this->encoder->hashPassword($user, 'password');
            $user->setPassword($passwordHasher);
            $user->setNom('nom' . $i);
            $user->setPreNom('prenom' . $i);
            $roles = ($i % 2 == 0) ? ['ROLE_BOUTIQIER', 'ROLE_CLIENT'] : ['ROLE_CLIENT'];
            $user->setRoles($roles);
            $client->setUser($user);

           
            for ($j = 1; $j <= $i; $j++) {
                $dette = new Dette();
                $dette->setDateAt(new \DateTimeImmutable());
                $dette->setMontant(10000 * $j * $i);
            
                if ($j % 3 == 0) { 
                    $dette->setMontantVerser($dette->getMontant()); 
                } elseif ($j % 2 == 0) {
                    $dette->setMontantVerser(10000 * $j * $i - 1000);
                } else {
                    $dette->setMontantVerser(1000 * $j * $i);
                }
            
                
                $dette->setMontantRestant($dette->getMontant() - $dette->getMontantVerser());

                
                if ($j % 2 == 0) {
                    $payment1 = new Payment();
                    $payment1->setDateAt(new \DateTimeImmutable());
                    $payment1->setMontant(1000);
                    $dette->addPayment($payment1);

                    $payment2 = new Payment();
                    $payment2->setDateAt(new \DateTimeImmutable());
                    $payment2->setMontant(1000 * $j * $i - 2000);
                    $dette->addPayment($payment2);
                } else {
                    $payment = new Payment();
                    $payment->setDateAt(new \DateTimeImmutable());
                    $payment->setMontant(1000 * $j * $i);
                    $dette->addPayment($payment);
                }

                foreach (array_slice($articles, 0, rand(1, count($articles))) as $article) {
                    $detail = new Dette_Article();
                    $detail->setDette($dette);
                    $detail->setArticle($article);
                    $detail->setQte(rand(1, 10)); 
                    $manager->persist($detail);
                }

                $client->addDette($dette);
                $manager->persist($dette);
            }


            for ($k = 1; $k <= rand(1, 3); $k++) {
                $demande = new Demande();
                $demande->setDateAt(new \DateTimeImmutable());
                $demande->setStatut(Statut::ENCOURS);
                $demande->setClient($client);
            
                $montant = rand(100, 1000); 
                $demande->setMontant($montant);
            
                $description = "Demande #$k pour client " . $client->getSurname();
                $demande->setDescription($description);
            
                foreach (array_slice($articles, 0, rand(1, count($articles))) as $article) {
                    $detailDemande = new Demande_Article();
                    $detailDemande->setDemande($demande);
                    $detailDemande->setArticle($article);
                    $detailDemande->setQte(rand(1, 5)); 
                    $manager->persist($detailDemande);
                }
            
                $manager->persist($demande);
            }
            
           
        }

        $manager->persist($client);
    }

    $manager->flush();
}

}






