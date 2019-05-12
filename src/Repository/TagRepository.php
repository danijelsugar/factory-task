<?php

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function tagsById($lang,$id)
    {
        return $this->createQueryBuilder('t')
            ->select('t.id,t.slug,tt.title')
            ->innerJoin('App\Entity\TagTranslation', 'tt', 'WITH', 't.id=tt.tag')
            ->where('t.id IN (:id)')
            ->andWhere('tt.language=:lang')
            ->setParameter('lang', $lang)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
