<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Appointment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appointment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appointment[]    findAll()
 * @method Appointment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findAvailableAppointmentsByParamsSplited(array $params)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.booked = :booked')
            ->setParameter('booked', false)
            ->orderBy('a.bookingDate', 'desc')
            ;

        if (isset($params['bookingDate'])) {
            $query->andWhere("a.bookingDate = :bookingDate")
                ->setParameter('bookingDate', $params['bookingDate']);
        }

        if (isset($params['location'])) {
            $query->andWhere('a.location LIKE :location')
                ->setParameter('location', $params['location']);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function findAvailableAppointmentsByDate(array $params)
    {
        $query = $this->createQueryBuilder('a')
            ->where('a.booked = :booked')
            ->setParameter('booked', false)
            ->orderBy('a.bookingDate', 'desc')
        ;

        if (isset($params['date_filter'])) {
            $query->andWhere("a.bookingDate = :bookingDate")
                ->setParameter('bookingDate', $params['date_filter']);
        }

        if (isset($params['location_filter'])) {
            $query->andWhere('a.location LIKE :location')
                ->setParameter('location', $params['location_filter']);
        }

        return $query
            ->getQuery()
            ->getResult();
    }

    public function findAvailableAppointments()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.bookingDate', 'asc')
            ->where('a.booked = :booked')
            ->setParameter('booked', false)
            //->andWhere('a.bookingStart > :now')
            //->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Appointment[] Returns an array of Appointment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Appointment
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
