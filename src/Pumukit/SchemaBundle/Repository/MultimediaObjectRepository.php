<?php

namespace Pumukit\SchemaBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Pumukit\SchemaBundle\Document\Series;
use Pumukit\SchemaBundle\Document\Tag;
use Pumukit\SchemaBundle\Document\MultimediaObject;
use Pumukit\SchemaBundle\Document\Broadcast;

/**
 * MultimediaObjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MultimediaObjectRepository extends DocumentRepository
{
    /**
     * Find all multimedia objects in a series with given status
     *
     * @param Series $series
     * @param array $status
     * @return ArrayCollection
     */
    public function findWithStatus(Series $series, array $status, $limit = 0, $page = 0)
    {
        $qb = $this->createQueryBuilder()
        ->field('series')->references($series)
        ->field('status')->in($status)
        ->sort('rank', 1);

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()
        ->execute();
    }

    /**
     * Find multimedia object prototype
     *
     * @param Series $series
     * @param array $status
     * @return MultimediaObject
     */
    public function findPrototype(Series $series)
    {
        return $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->equals(MultimediaObject::STATUS_PROTOTYPE)
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects in a series
     * without the template (prototype)
     *
     * @param Series $series
     * @return ArrayCollection
     */
    public function findWithoutPrototype(Series $series)
    {
        $aux = $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE)
          ->sort('rank', 1)
          ->getQuery()
          ->execute();

        return $aux;
    }

    /**
     * Find multimedia objects by pic id
     *
     * @param string $picId
     * @return MultimediaObject
     */
    public function findByPicId($picId)
    {
        return $this->createQueryBuilder()
          ->field('pics._id')->equals(new \MongoId($picId))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects by person id
     *
     * @param string $personId
     * @return ArrayCollection
     */
    public function findByPersonId($personId)
    {
        return $this->createStandardQueryBuilder()
          ->field('people.people._id')->equals(new \MongoId($personId))
          ->getQuery()
          ->execute();
    }

    /**
     * Find multimedia objects by role code
     *
     * @param string $roleCode
     * @return ArrayCollection
     */
    public function findByRoleCod($roleCode)
    {
        return $this->createStandardQueryBuilder()
          ->field('people.cod')->equals($roleCode)
          ->getQuery()
          ->execute();
    }

    /**
     * Find multimedia objects by role id
     *
     * @param string $roleId
     * @return ArrayCollection
     */
    public function findByRoleId($roleId)
    {
        return $this->createStandardQueryBuilder()
          ->field('people._id')->equals(new \MongoId($roleId))
          ->getQuery()
          ->execute();
    }

    /**
     * Find multimedia objects by person id
     * with given role
     *
     * @param string $personId
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findByPersonIdWithRoleCod($personId, $roleCod)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('people')->elemMatch(
            $qb->expr()->field('people._id')->equals(new \MongoId($personId))
                ->field('cod')->equals($roleCod)
        );

        return $qb->getQuery()->execute();
    }

    /**
     * Find multimedia objects by person id
     * with given role in given series
     *
     * @param Series $series
     * @param string $personId
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findBySeriesAndPersonIdWithRoleCod($series, $personId, $roleCod)
    {
        $qb = $this->createStandardQueryBuilder()
            ->field('series')->references($series);
        $qb->field('people')->elemMatch(
            $qb->expr()->field('people._id')->equals(new \MongoId($personId))
                ->field('cod')->equals($roleCod)
        );

        return $qb->getQuery()->execute();
    }

    /**
     * Find people in multimedia objects
     * with given role
     *
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findPeopleWithRoleCode($roleCode)
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject');

        $pipeline = array(
                          array('$match' => array('people.cod' => "$roleCode")),
                          array('$project' => array('_id' => 0, 'people.cod' => 1, 'people.people._id' => 1)),
                          array('$unwind' => '$people')
                          );

        $aggregation = $collection->aggregate($pipeline);

        $people = array();

        foreach ($aggregation as $element) {
            if (null !== $element['people']) {
              if ((null !== $element['people']['cod']) && (null !== $element['people']['people'])) {
                    if (0 === strpos($element['people']['cod'], $roleCode)) {
                        foreach ($element['people']['people'] as $person) {
                            if (!in_array($person['_id']->{'$id'}, $people)) {
                                $people[] = $person['_id']->{'$id'};
                            }
                        }
                    }
                }
            }
        }

        return $people;
    }

    /**
     * Find person in multimedia objects
     * with given role and given email
     *
     * @param string $roleCod
     * @param string $email
     * @return ArrayCollection
     */
    public function findPersonWithRoleCodeAndEmail($roleCode, $email)
    {
        $dm = $this->getDocumentManager();
        $collection = $dm->getDocumentCollection('PumukitSchemaBundle:MultimediaObject');

        $pipeline = array(
                          array('$match' => array('people.cod' => "$roleCode", 'people.people.email' => "$email")),
                          array('$project' => array('_id' => 0, 'people.cod' => 1, 'people.people.email' => 1, 'people.people._id' => 1)),
                          array('$unwind' => '$people')
                          );

        $aggregation = $collection->aggregate($pipeline);

        $persons = array();

        foreach($aggregation as $element) {
            if(null !== $element['people']) {
                if ((null !== $element['people']['cod']) && (null !== $element['people']['people'])) {
                    if ((0 === strpos($element['people']['cod'], $roleCode))) {
                        foreach ($element['people']['people'] as $person) {
                            if($person['email'] === $email){
                                if (!in_array($person['_id']->{'$id'}, $persons)) {
                                    $persons[] = $person['_id']->{'$id'};
                                }
                            }
                        }
                    }
                }
            }
        }

        return $persons;
    }

    /**
     * Find series by person id
     *
     * @param string $personId
     * @return ArrayCollection
     */
    public function findSeriesFieldByPersonId($personId)
    {
        return $this->createQueryBuilder()
          ->field('people.people._id')->equals(new \MongoId($personId))
          ->distinct('series')
          ->getQuery()
          ->execute();
    }

    /**
     * Search series using text index
     *
     * @param string $text
     * @return ArrayCollection
     */
    public function searchSeriesField($text, $limit = 0, $page = 0)
    {
        $qb = $this->createQueryBuilder()
            ->field('$text')->equals(array('$search' => $text))
            ->distinct('series');

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }


    /**
     * Search series using text index or the _id
     *
     * @param string $text or _id
     * @return ArrayCollection
     */
    public function searchSeriesByIdOrField($text, $limit = 0, $page = 0)
    {
        $qb = $this->createQueryBuilder();
        $qb->addOr($qb->expr()->field('$text')->equals(array('$search' => $text)));
        $qb->addOr($qb->expr()->field('_id')->equals($text));
        $qb->distinct('series');

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find series by person id
     *
     * @param string $personId
     * @param string $roleCod
     * @return ArrayCollection
     */
    public function findSeriesFieldByPersonIdAndRoleCod($personId, $roleCod)
    {
        $qb = $this->createQueryBuilder();
        $qb->field('people')->elemMatch(
            $qb->expr()->field('people._id')->equals(new \MongoId($personId))
                ->field('cod')->equals($roleCod)
                                        );
        return $qb->distinct('series')
          ->getQuery()
          ->execute();
    }

    // Find Multimedia Objects with Tags

    /**
     * Find multimedia objects by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithTag(Tag $tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithTag($tag, $sort);

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find multimedia objects by tag id without tag children
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithGeneralTag(Tag $tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb = $this->createBuilderWithGeneralTag($tag, $sort);

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Create QueryBuilder to find multimedia objects by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return QueryBuilder
     */
    public function createBuilderWithTag(Tag $tag, $sort = array())
    {
        $qb = $this->createStandardQueryBuilder()
            ->field('tags._id')->equals(new \MongoId($tag->getId()));

        if (0 !== count($sort) ){
          $qb->sort($sort);
        }

        return $qb;
    }

    /**
     * Create QueryBuilder to find multimedia objects by series
     *
     * @param Series $series
     * @param array $sort
     * @return QueryBuilder
     */
    public function createBuilderWithSeries(Series $series, $sort = array())
    {
        $qb = $this->createStandardQueryBuilder()
          ->field('series')->references($series);

        if (0 !== count($sort) ){
          $qb->sort($sort);
        }

        return $qb;
    }

    /**
     * Create QueryBuilder to find multimedia objects by series and status
     *
     * @param Series $series
     * @param array $sort
     * @return QueryBuilder
     */
    public function createBuilderWithSeriesAndStatus(Series $series, $status = array(), $sort = array())
    {
        $qb = $this->createQueryBuilder()
          ->field('series')->references($series)
          ->field('status')->in($status);

        if (0 !== count($sort) ){
          $qb->sort($sort);
        }

        return $qb;
    }
    /**
    * Create QueryBuilder to find multimedia objects with Tag and without any Tag children.
    *
    * @param Tag|EmbeddeTag $tag
    * @param array $sort
    * @return QueryBuilder
    */
    public function createBuilderWithGeneralTag(Tag $tag, $sort = array())
    {
        $qb = $this->createStandardQueryBuilder()
            ->field('tags._id')->in(array(new \MongoId($tag->getId())))
            ->field('tags.path')->notIn(array(new \MongoRegex('/'.preg_quote($tag->getPath()). '.*\|/')));

        if (0 !== count($sort) ){
          $qb->sort($sort);
        }

        return $qb;
    }

    /**
     * Find one multimedia object by tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @return MultimediaObject
     */
    public function findOneWithTag(Tag $tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->equals(new \MongoId($tag->getId()))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects with any tag
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAnyTag($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->in($mongoIds);

        if (0 !== count($sort) ){
          $qb->sort($sort);
        }

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find multimedia objects with all tags
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->all($mongoIds);

        if (0 !== count($sort) ){
            $qb->sort($sort);
        }

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find one multimedia object with all tags
     *
     * @param array $tags
     * @return MultimediaObject
     */
    public function findOneWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->all($mongoIds);

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Find multimedia objects without tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithoutTag(Tag $tag, $sort = array(), $limit = 0, $page = 0)
    {
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->notEqual(new \MongoId($tag->getId()));

        if (0 !== count($sort) ){
            $qb->sort($sort);
        }

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find one multimedia object without tag id
     *
     * @param Tag|EmbeddedTag $tag
     * @return MultimediaObject
     */
    public function findOneWithoutTag(Tag $tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->notEqual(new \MongoId($tag->getId()))
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find multimedia objects without all tags
     *
     * @param array $tags
     * @param array $sort
     * @param int $limit
     * @param int $page
     * @return ArrayCollection
     */
    public function findWithoutAllTags($tags, $sort = array(), $limit = 0, $page = 0)
    {
        $mongoIds = $this->getMongoIds($tags);
        $qb =  $this->createStandardQueryBuilder()
          ->field('tags._id')->notIn($mongoIds);

        if (0 !== count($sort) ){
            $qb->sort($sort);
        }

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()->execute();
    }

    // End of find Multimedia Objects with Tags

    // Find Series Field with Tags

    /**
     * Find series with tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return ArrayCollection
     */
    public function findSeriesFieldWithTag(Tag $tag)
    {
        return $this->createStandardQueryBuilder()
            ->field('tags._id')->equals(new \MongoId($tag->getId()))
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find one series with tag
     *
     * @param Tag|EmbeddedTag $tag
     * @return Series
     */
    public function findOneSeriesFieldWithTag(Tag $tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags._id')->equals(new \MongoId($tag->getId()))
          ->distinct('series')
          ->getQuery()
          ->getSingleResult();
    }

    /**
     * Find series with any tag
     *
     * @param array $tags
     * @return ArrayCollection
     */
    public function findSeriesFieldWithAnyTag($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return $this->createStandardQueryBuilder()
            ->field('tags._id')->in($mongoIds)
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find series with all tags
     *
     * @param array $tags
     * @return ArrayCollection
     */
    public function findSeriesFieldWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return  $this->createStandardQueryBuilder()
            ->field('tags._id')->all($mongoIds)
            ->distinct('series')
            ->getQuery()->execute();
    }

    /**
     * Find one series with all tags
     *
     * @param array $tags
     * @return Series
     */
    public function findOneSeriesFieldWithAllTags($tags)
    {
        $mongoIds = $this->getMongoIds($tags);

        return  $this->createStandardQueryBuilder()
            ->field('tags._id')->all($mongoIds)
            ->distinct('series')
            ->getQuery()
            ->getSingleResult();
    }

    // End of find Series with Tags

    /**
     * Find distinct url pics in series
     *
     * TODO Limit and sort
     *
     * @param Series $series
     * @return ArrayCollection
     */
    public function findDistinctUrlPicsInSeries(Series $series)
    {
        return $this->createStandardQueryBuilder()
          ->field('series')->references($series)
          ->distinct('pics.url')
          ->getQuery()
          ->execute();
    }

    /**
     * Find distinct url pics
     *
     * TODO Limit and sort
     *
     * @return ArrayCollection
     */
    public function findDistinctUrlPics()
    {
        return $this->createStandardQueryBuilder()
          ->distinct('pics.url')
          ->sort('public_date', 1)
          ->getQuery()
          ->execute();
    }

    /**
     * Find by series
     *
     * @param Series $series
     * @return ArrayCollection
     */
    public function findBySeries(Series $series)
    {
        return $this->createQueryBuilder()
          ->field('series')->references($series)
          ->getQuery()
          ->execute();
    }

    /**
     * Find by series
     *
     * @param Series $series
     * @return ArrayCollection
     */
    public function findStandardBySeries(Series $series, $limit = 0, $page = 1)
    {
        $qb = $this->createStandardQueryBuilder()
        ->field('series')->references($series);

        if ($limit > 0){
            $qb->limit($limit)->skip($limit * $page);
        }

        return $qb->getQuery()
        ->execute();
    }

    /**
     * Find by series, tag code and status
     *
     * TODO not needed (Using findBySeries and filter)
     *
     * @param Series
     * @param string $tagCod
     * @param array $status
     * @return ArrayCollection
     */
    public function findBySeriesByTagCodAndStatus(Series $series, $tagCod, $status = array())
    {
        $qb = $this->createStandardQueryBuilder()
          ->field('series')->references($series)
          ->field('tags.cod')->equals($tagCod);

        if (0 !== count($status)) $qb->field('status')->in($status);

        $qb->sort('rank', 'asc');

        $aux = $qb->getQuery()->execute();

        //TODO Multimedia Objects with Broadcast public or corporative
        $multimediaObjects = array();
        foreach ($aux as $mm){
            $mmBroadcast = $mm->getBroadcast()->getBroadcastTypeId();
            if (($mmBroadcast == Broadcast::BROADCAST_TYPE_PUB) || ($mmBroadcast == Broadcast::BROADCAST_TYPE_COR)){
                $multimediaObjects[] = $mm;
            }
        }

        return $multimediaObjects;
    }


    /**
     * Find by broadcast
     *
     * @param Broadcast $broadcast
     * @return ArrayCollection
     */
    public function findByBroadcast(Broadcast $broadcast)
    {
        return $this->createQueryBuilder()
          ->field('broadcast')->references($broadcast)
          ->getQuery()
          ->execute();
    }

    /**
     * Find ordered by fieldName: asc/desc
     *
     * @param Series $series
     * @param array $sort
     * @return QueryBuilder
     */
    public function getQueryBuilderOrderedBy(Series $series, $sort = array())
    {
        $qb = $this->createStandardQueryBuilder()
          ->field('series')->references($series);
        if (0 !== count($sort)) $qb->sort($sort);
        return $qb;
    }


    /**
     * Find ordered by fieldName: asc/desc
     *
     * @param Series $series
     * @param array $sort
     * @return Cursor
     */
    public function findOrderedBy(Series $series, $sort = array())
    {
        $qb = $this->getQueryBuilderOrderedBy($series, $sort);
        return $qb->getQuery()->execute();
    }

    /**
     * Get mongo ids
     *
     * @param array $documents
     * @return array
     */
    private function getMongoIds($documents)
    {
        $mongoIds = array();
        foreach($documents as $document){
            $mongoIds[] = new \MongoId($document->getId());
        }

        return $mongoIds;
    }

    /**
     * Create standard query builder
     *
     * Creates a query builder with all multimedia objects
     * having status different than PROTOTYPE.
     * These are the multimedia objects we need to show
     * in series.
     *
     * @return QueryBuilder
     */
    public function createStandardQueryBuilder()
    {
        return $this->createQueryBuilder()
          ->field('status')->notEqual(MultimediaObject::STATUS_PROTOTYPE);
    }

    /**
     * Finds standard MultimediaObjects (not prototype) by a set of criteria.
     *
     * @param array        $criteria Query criteria
     * @param array        $sort     Sort array for Cursor::sort()
     * @param integer|null $limit    Limit for Cursor::limit()
     * @param integer|null $skip     Skip for Cursor::skip()
     *
     * @return array
     */
    public function findStandardBy(array $criteria, array $sort = null, $limit = null, $skip = null)
    {
      $criteria["status"] = MultimediaObject::STATUS_PUBLISHED;
      return $this->getDocumentPersister()->loadAll($criteria, $sort, $limit, $skip)->toArray(false);
    }

    /**
     * Finds a single standard MultimediaObject (not prototype) by a set of criteria.
     *
     * @param array $criteria
     * @return object
     */
    public function findStandardOneBy(array $criteria)
    {
      $criteria["status"] = MultimediaObject::STATUS_PUBLISHED;
      return $this->getDocumentPersister()->load($criteria);
    }

    /**
     * Find similar multimedia objects to a given one
     * with same tags, from different series,
     * broadcast public, status normal,
     * maximum 20 and random
     *
     * @param MultimediaObject $multimediaObject
     * @param array $tags
     * @return ArrayCollection
     */
    public function findRelatedMultimediaObjects(MultimediaObject $multimediaObject)
    {
        $qb = $this->createQueryBuilder()
          ->field('_id')->notEqual($multimediaObject->getId())
          ->field('series')->notEqual($multimediaObject->getSeries()->getId())
          ->field('status')->equals(MultimediaObject::STATUS_PUBLISHED);

        // Broadcast public
        $broadcastRepo = $this->dm->getRepository('PumukitSchemaBundle:Broadcast');
        $broadcast = $broadcastRepo->findPublicBroadcast();
        $qb->field('broadcast')->references($broadcast);

        // Includes PUCHWEBTV code
        $tagRepo = $this->dm->getRepository('PumukitSchemaBundle:Tag');
        $unescoTag = $tagRepo->findOneByCod('UNESCO');
        $codes = array();
        foreach ($multimediaObject->getTags() as $tag) {
            if ($unescoTag) {
                if ($tag->isDescendantOf($unescoTag)) {
                    $codes[] = $tag->getCod();
                }
            }
        }
        $qb->field('tags.cod')->in($codes);

        // Limit 20 and random order
        $qb
          ->limit(20)
          ->sort('rank', mt_rand(0, 1) ? 1 : -1);

        $aux = $qb->getQuery()->execute();

        return $aux;
    }


    /**
     * Count number of standard (not prototype) multimedia objects in the repo
     *
     * @return integer
     */
    public function count()
    {
      return $this
        ->createStandardQueryBuilder()
        ->count()
        ->getQuery()
        ->execute();
    }

    /**
     * Count total duration of standard (not prototype) multimedia objects.
     *
     * @return integer total of seconds
     */
    public function countDuration()
    {
      $result = $this
        ->createStandardQueryBuilder()
        ->group(array(), array('count' => 0))
        ->reduce('function (obj, prev) { prev.count += obj.duration; }')
        ->getQuery()
        ->execute();

      $singleResult = $result->getSingleResult();
      return $singleResult["count"];
    }

    /**
     * Count number of standard (not prototype) multimedia objects in a Series
     *
     * @param Series $series
     * @return integer
     */
    public function countInSeries($series)
    {
      return $this
        ->createStandardQueryBuilder()
        ->field('series')->references($series)
        ->count()
        ->getQuery()
        ->execute();
    }

    /**
     * Find by tag query builder
     *
     * @param Tag|EmbeddedTag $tag
     * @return QueryBuilder
     */
    public function findByTagCodQueryBuilder($tag)
    {
        return $this->createStandardQueryBuilder()
          ->field('tags.cod')->equals($tag->getCod());
    }

    /**
     * Find by tag query
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return Query
     */
    public function findByTagCodQuery($tag, $sort=array())
    {
        $qb = $this->findByTagCodQueryBuilder($tag);
        if ($sort) {
            $qb->sort($sort);
        }
        return $qb->getQuery();
    }

    /**
     * Find by tag code
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return Cursor
     */
    public function findByTagCod($tag, $sort=array())
    {
        return $this->findByTagCodQuery($tag, $sort)
          ->execute();
    }

    /**
     * Find all by tag query builder
     *
     * @param Tag|EmbeddedTag $tag
     * @return QueryBuilder
     */
    public function findAllByTagQueryBuilder($tag)
    {
        return $this->createQueryBuilder()
          ->field('tags._id')->equals(new \MongoId($tag->getId()));
    }

    /**
     * Find all by tag query
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return Query
     */
    public function findAllByTagQuery($tag, $sort=array())
    {
        $qb = $this->findAllByTagQueryBuilder($tag);
        if ($sort) {
            $qb->sort($sort);
        }
        return $qb->getQuery();
    }

    /**
     * Find all by tag
     *
     * @param Tag|EmbeddedTag $tag
     * @param array $sort
     * @return Cursor
     */
    public function findAllByTag($tag, $sort=array())
    {
        return $this->findAllByTagQuery($tag, $sort)
          ->execute();
    }
}
