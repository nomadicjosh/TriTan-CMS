<?php
namespace TriTan\Common\Posttype;

use TriTan\Container as c;
use TriTan\Interfaces\Posttype\PosttypeMapperInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Exception\Exception;
use TriTan\Exception\InvalidArgumentException;
use TriTan\Common\Posttype\Posttype;
use Cascade\Cascade;

class PosttypeMapper implements PosttypeMapperInterface
{
    public $db;

    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    /**
     * Fetch a posttype object by ID
     *
     * @since 0.9.9
     * @param string $id
     * @return TriTan\Common\Posttype\Posttype|null Returns posttype object if exist and NULL otherwise.
     */
    public function findById($id)
    {
        if (!is_integer($id) || (int) $id < 1) {
            throw new InvalidArgumentException('The ID of this entity is invalid.', 'invalid_id');
        }

        $posttype = null;

        if (!$data = $this->db->table(c::getInstance()->get('tbl_prefix') . 'posttype')->where('posttype_id', $id)->first()) {
            return false;
        }

        if ($data != null) {
            $posttype = $this->create($data);
        }

        if (is_array($posttype)) {
            $posttype = $this->context->obj['util']->{'toObject'}($posttype);
        }

        return $posttype;
    }

    /**
     * Fetch all posttypes.
     *
     * @since 0.9.9
     * @return object Posttype data object.
     */
    public function findAll()
    {
        $data = $this->db->table(c::getInstance()->get('tbl_prefix') . 'posttype')->all();
        $posttypes = [];
        if ($data != null) {
            foreach ($data as $posttype) {
                $posttypes[] = $this->create($posttype);
            }
        }
        return $posttypes;
    }

    /**
     * Create a new instance of Posttype. Optionally populating it
     * from a data array.
     *
     * @param array $data
     * @return TriTan\Common\Posttype\Posttype.
     */
    public function create(array $data = null) : Posttype
    {
        $posttype = $this->__create();
        if ($data) {
            $posttype = $this->populate($posttype, $data);
        }
        return $posttype;
    }

    /**
     * Populate the Posttype object with the data array.
     *
     * @param Posttype $posttype object.
     * @param array $data Posttype data.
     * @return TriTan\Common\Posttype\Posttype
     */
    public function populate(Posttype $posttype, array $data) : Posttype
    {
        $posttype->setId((int) $this->context->obj['escape']->{'html'}($data['posttype_id']));
        $posttype->setTitle((string) $this->context->obj['escape']->{'html'}($data['posttype_title']));
        $posttype->setSlug((string) $this->context->obj['escape']->{'html'}($data['posttype_slug']));
        $posttype->setDescription((string) $this->context->obj['escape']->{'textarea'}($data['posttype_description']));
        return $posttype;
    }

    /**
     * Create a new Posttype object.
     *
     * @return TriTan\Common\Posttype\Posttype
     */
    protected function __create() : Posttype
    {
        return new Posttype();
    }

    /**
     * Inserts a new posttype into the posttype document.
     *
     * @since 0.9.9
     * @param Posttype $posttype Posttype object.
     * @return int Last insert id.
     */
    public function insert(Posttype $posttype)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'posttype');
        $sql->begin();
        try {
            $sql->insert([
                'posttype_title' => $this->db->{'ifNull'}($posttype->getTitle()),
                'posttype_slug' => $this->db->{'ifNull'}($posttype->getSlug()),
                'posttype_description' => $this->db->{'ifNull'}($posttype->getDescription())
            ]);
            $sql->commit();
            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTTYPEMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    /**
     * Updates a Posttype object.
     *
     * @since 0.9.9
     * @param Posttype $posttype Posttype object.
     * @return Posttype id.
     */
    public function update(Posttype $posttype)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'posttype');
        $sql->begin();
        try {
            $sql->where('posttype_id', (int) $posttype->getId())->update([
                'posttype_title' => $this->db->{'ifNull'}($posttype->getTitle()),
                'posttype_slug' => $this->db->{'ifNull'}($posttype->getSlug()),
                'posttype_description' => $this->db->{'ifNull'}($posttype->getDescription())
            ]);

            $sql->commit();
            return (int) $posttype->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTTYPEMAPPER[update]: %s', $ex->getMessage()));
        }
    }

    /**
     * Save the Posttype object.
     *
     * @since 0.9.9
     * @param Posttype $posttype Posttype object.
     */
    public function save(Posttype $posttype)
    {
        if (is_null($posttype->getId())) {
            $this->insert($posttype);
        } else {
            $this->update($posttype);
        }
    }

    /**
     * Deletes posttype object.
     *
     * @since 0.9.9
     * @param Posttype $posttype Posttype object.
     * @return bool True if deleted, false otherwise.
     */
    public function delete(Posttype $posttype)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'posttype');
        $sql->begin();
        try {
            $sql->where('posttype_id', (int) $posttype->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTTYPEMAPPER[delete]: %s', $ex->getMessage()));
        }

        $query = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post');
        $query->begin();
        try {
            $query->where('post_type.posttype_id', (int) $posttype->getId())
                    ->delete();
            $query->commit();
        } catch (Exception $ex) {
            $query->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTTYPEMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
