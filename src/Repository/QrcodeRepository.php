<?php

namespace Tourze\TrainClassroomBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\TrainClassroomBundle\Entity\Qrcode;

/**
 * @method Qrcode|null find($id, $lockMode = null, $lockVersion = null)
 * @method Qrcode|null findOneBy(array $criteria, array $orderBy = null)
 * @method Qrcode[]    findAll()
 * @method Qrcode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QrcodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Qrcode::class);
    }
}
