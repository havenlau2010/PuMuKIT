<?php

namespace Pumukit\SchemaBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pumukit\SchemaBundle\Document\SeriesType;

/**
 * SeriesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SeriesRepository extends DocumentRepository
{
    /**
     * Find series by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithTag($tag, $sort);

        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Create QueryBuilder to find series by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return QueryBuilder
     */
    public function createBuilderWithTag($tag, $sort = array())
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithTag($tag);

        $qb = $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray());

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }
        return $qb;
    }


    /**
     * Find one series with tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return Series
     */
    public function findOneWithTag($tag)
    {
        $referencedOneSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findOneSeriesFieldWithTag($tag);

        return $this->createQueryBuilder()
            ->field('_id')->equals($referencedOneSeries)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Find series with any tag
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAnyTag($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithAnyTag($tags);

        $qb = $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray());

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }

        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find series with all tags
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithAllTags($tags);

        $qb = $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray());

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }

        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }
        
        return $qb->getQuery()->execute();
    }

    /**
     * Find one series with all tags
     *
     * @param array $tags
     * @return Series
     */
    public function findOneWithAllTags($tags)
    {
        $referencedOneSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findOneSeriesFieldWithAllTags($tags);

        return $this->createQueryBuilder()
            ->field('_id')->equals($referencedOneSeries)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Find series without tag
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithoutTag($tag, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithTag($tag);
        
        $qb = $this->createQueryBuilder()
            ->field('_id')->notIn($referencedSeries->toArray());

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }
        
        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }
        
        return $qb->getQuery()->execute();
    }

    /**
     * Find one series without tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return Series
     */
    public function findOneWithoutTag($tag)
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithTag($tag);

        return $this->createQueryBuilder()
            ->field('_id')->notIn($referencedSeries->toArray())
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Find series without all tags
     *
     * @param array tags
     * @param array $sort
     * @return ArrayCollection
     */
    public function findWithoutAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithAllTags($tags);

        $qb = $this->createQueryBuilder()
            ->field('_id')->notIn($referencedSeries->toArray());

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }

        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }
        
        return $qb->getQuery()->execute();
    }

    /**
     * Find series by pic id
     *
     * @param string $picId
     * @return Series
     */
    public function findByPicId($picId)
    {
        return $this->createQueryBuilder()
          ->field('pics._id')->equals(new \MongoId($picId))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find series by person id
     *
     * @param string $personId
     * @return ArrayCollection
     */
    public function findSeriesByPersonId($personId)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        
        $referencedSeries = $repoMmobj->findSeriesFieldByPersonId($personId);
        
        return $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray())
            ->getQuery()
            ->execute();
    }

    /**
     * Find series by person id and role cod
     *
     * @param string $personId
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findByPersonIdAndRoleCod($personId, $roleCod)
    {
        $repoMmobj = $this->getDocumentManager()->getRepository('PumukitSchemaBundle:MultimediaObject');
        $referencedSeries = $repoMmobj->findSeriesFieldByPersonIdAndRoleCod($personId, $roleCod);

        return $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray())
            ->getQuery()
            ->execute();
    }

    /**
     * Find series with given series type
     *
     * @param SeriesType $series_type
     * @return ArrayCollection
     */
    public function findBySeriesType(SeriesType $series_type)
    {
        return $this->createQueryBuilder()
            ->field('series_type')->references($series_type)
            ->getQuery()
            ->execute();
    }

    /**
     * Count number of series in the repo.
     *
     * @return integer
     */
    public function count()
    {
        return $this
        ->createQueryBuilder()
        ->count()
        ->getQuery()
        ->execute();
    }

    /**
     * Count number of series in the repo.
     *
     * @return integer
     */
    public function countPublic()
    {
        return $this
        ->getDocumentManager()
        ->getRepository('PumukitSchemaBundle:MultimediaObject')
        ->createStandardQueryBuilder()
        ->distinct('series')
        ->getQuery()
        ->execute()
        ->count();
    }

    /**
     * Find series with tag and series type
     *
     * @param Tag|EmbeddedTag $tag
     * @param SeriesType $seriesType
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithTagAndSeriesType($tag, $seriesType, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithTagAndSeriesType($tag, $seriesType,  $sort);

        if ($limit > 0) {
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Create QueryBuilder to find series with tag and series type
     *
     * @param Tag|EmbeddedTag $tag
     * @param SeriesType $seriesType
     * @param array $sort
     * @return QueryBuilder
     */
    public function createBuilderWithTagAndSeriesType($tag, $seriesType,  $sort = array())
    {
        $referencedSeries = $this->getDocumentManager()
            ->getRepository('PumukitSchemaBundle:MultimediaObject')
            ->findSeriesFieldWithTag($tag);

        $qb = $this->createQueryBuilder()
            ->field('_id')->in($referencedSeries->toArray())
            ->field('series_type')->references($seriesType);

        if (0 !== count($sort)) {
            $qb->sort($sort);
        }
        return $qb;
    }

    /**
     * Find series with the same propertyName
     *
     * @param string $propertyName
     * @param string $propertyValue
     * @return QueryBuilder
     */
    public function findOneBySeriesProperty($propertyName, $propertyValue)
    {
        return $this->createQueryBuilder()
          ->field('properties.'.$propertyName)->equals($propertyValue)
          ->getQuery()
          ->getSingleResult();
    }
}
