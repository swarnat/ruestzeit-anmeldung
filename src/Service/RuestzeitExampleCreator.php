<?php
namespace App\Service;


use App\Entity\Ruestzeit;
use App\Repository\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RuestzeitExampleCreator {
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security,
        private LocationRepository $locationRepository
        )
    {
        
    }

    public function create() {
        $entity = new Ruestzeit();

        $entity->setTitle("Beispiel Rüstzeit " . mt_rand(10000,99999));
        $entity->setRegistrationActive(false);
        $entity->setMemberlimit(10);
        $entity->setForwarder("EXAMPLE" . mt_rand(10000,99999));
        $entity->setDomain("example.com");
        $entity->setAdmin($this->security->getUser());
        $entity->setLocation($this->locationRepository->findOneBy([]));
        $entity->setDescription("Hier kann eine kurze Beschreibung eingefügt werden");
        $entity->setDateFrom((new \DateTime())->modify("+5 months"));
        $entity->setDateTo((new \DateTime())->modify("+5 months +14days"));
        
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}