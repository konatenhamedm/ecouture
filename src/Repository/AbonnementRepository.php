<?php

namespace App\Repository;

use App\Entity\Abonnement;
use App\Entity\Entreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Abonnement>
 */
class AbonnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Abonnement::class);
    }

    public function add(Abonnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Abonnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findActiveForEntreprise(Entreprise $entreprise): ?Abonnement
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.entreprise = :entreprise')
            ->andWhere('a.etat = :etat')
            ->andWhere('a.dateFin >= :now')
            ->setParameter('entreprise', $entreprise)
            ->setParameter('etat', 'actif')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.dateFin', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function findInactiveForEntreprise(Entreprise $entreprise): ?Abonnement
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.entreprise = :entreprise')
            ->andWhere('a.etat = :etat')
            ->andWhere('a.dateFin >= :now')
            ->setParameter('entreprise', $entreprise)
            ->setParameter('etat', 'inactif')
            ->setParameter('now', new \DateTime())
            ->orderBy('a.dateFin', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    //    /**
    //     * @return Abonnement[] Returns an array of Abonnement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Abonnement
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
