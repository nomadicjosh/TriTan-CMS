<?php
namespace TriTan\Common\Post;

use TriTan\Container as c;
use TriTan\Interfaces\Post\PostMapperInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Exception\Exception;
use TriTan\Exception\InvalidArgumentException;
use TriTan\Common\Post\Post;
use Cascade\Cascade;

class PostMapper implements PostMapperInterface
{
    public $db;
    
    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    /**
     * Fetch a post object by ID
     *
     * @since 0.9.9
     * @param string $id
     * @return TriTan\Common\Post\Post|null Returns post object if exist and NULL otherwise.
     */
    public function findById($id)
    {
        if (!is_integer($id) || (int) $id < 1) {
            throw new InvalidArgumentException(
                'The ID of this entity is invalid.',
                'invalid_id'
            );
        }

        $post = $this->findBy('id', $id);

        return $post;
    }

    /**
     * Return only the main post fields.
     *
     * @since 0.9.9
     * @param string $field The field to query against: 'id', 'ID', 'email' or 'login'.
     * @param string|int $value The field value
     * @return object|false Raw post object
     */
    public function findBy($field, $value)
    {
        // 'ID' is an alias of 'id'.
        if ('ID' === $field) {
            $field = 'id';
        }

        if ('id' == $field) {
            // Make sure the value is numeric to avoid casting objects, for example,
            // to int 1.
            if (!is_numeric($value)) {
                return false;
            }
            $value = intval($value);
            if ($value < 1) {
                return false;
            }
        } else {
            $value = $this->context->obj['util']->{'trim'}($value);
        }

        if (!$value) {
            return false;
        }

        switch ($field) {
            case 'id':
                $post_id = (int) $value;
                $db_field = 'post_id';
                break;
            case 'slug':
                $post_id = $this->context->obj['cache']->{'read'}($value, 'postslugs');
                $db_field = 'post_slug';
                break;
            case 'type':
                $value = $this->context->obj['sanitizer']->{'item'}($value, '', '');
                $post_id = $this->context->obj['cache']->{'read'}($value, 'post_posttypes');
                $db_field = 'post_type';
                break;
            default:
                return false;
        }

        $post = null;

        if (false !== $post_id) {
            if ($data = $this->context->obj['cache']->{'read'}($post_id, 'posts')) {
                is_array($data) ? $this->context->obj['util']->{'toObject'}($data) : $data;
            }
        }

        if (!$data = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post')->where($db_field, sprintf('%s', $value))->first()) {
            return false;
        }

        if ($data != null) {
            $post = $this->create($data);
            $this->context->obj['postcache']->{'update'}($post);
        }

        if (is_array($post)) {
            $post = $this->context->obj['util']->{'toObject'}($post);
        }

        return $post;
    }
    
    /**
     * Fetch all posts by particular type.
     * 
     * @since 0.9.9
     * @param string $type
     * @return object Post data object.
     */
    public function findByType(string $type)
    {
        $data = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post')->where('post_type.post_posttype', $type)->get();
        $posts = [];
        if($data != null) {
            foreach($data as $post) {
                $posts[] = $this->create($post);
            }
        }
        return $posts;
    }
    
    /**
     * Fetch all posts.
     * 
     * @since 0.9.9
     * @return object Post data object.
     */
    public function findAll()
    {
        $data = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post')->all();
        $posts = [];
        if($data != null) {
            foreach($data as $post) {
                $posts[] = $this->create($post);
            }
        }
        return $posts;
    }

    /**
     * Create a new instance of Post. Optionally populating it
     * from a data array.
     *
     * @param array $data
     * @return TriTan\Common\Post\Post.
     */
    public function create(array $data = null) : Post
    {
        $post = $this->__create();
        if ($data) {
            $post = $this->populate($post, $data);
        }
        return $post;
    }

    /**
     * Populate the Post object with the data array.
     *
     * @param Post $post object.
     * @param array $data Post data.
     * @return TriTan\Common\Post\Post
     */
    public function populate(Post $post, array $data) : Post
    {
        $post->setId((int) $this->context->obj['escape']->{'html'}($data['post_id']));
        $post->setTitle((string) $this->context->obj['escape']->{'html'}($data['post_title']));
        $post->setSlug((string) $this->context->obj['escape']->{'html'}($data['post_slug']));
        $post->setContent((string) $this->context->obj['html']->{'purify'}($data['post_content']));
        $post->setAuthor((int) $this->context->obj['escape']->{'html'}($data['post_author']));
        $post->setPosttypeId((int) $this->context->obj['escape']->{'html'}($data['post_type']['posttype_id']));
        $post->setPosttype((string) $this->context->obj['escape']->{'html'}($data['post_type']['post_posttype']));
        $post->setParentId((int) $this->context->obj['escape']->{'html'}($data['post_attributes']['parent']['parent_id']));
        $post->setParent((string) $this->context->obj['escape']->{'html'}($data['post_attributes']['parent']['post_parent']));
        $post->setSidebar((int) $this->context->obj['escape']->{'html'}($data['post_attributes']['post_sidebar']));
        $post->setShowInMenu((int) $this->context->obj['escape']->{'html'}($data['post_attributes']['post_show_in_menu']));
        $post->setShowInSearch((int) $this->context->obj['escape']->{'html'}($data['post_attributes']['post_show_in_search']));
        $post->setRelativeUrl((string) $this->context->obj['escape']->{'html'}($data['post_relative_url']));
        $post->setFeaturedImage((string) $this->context->obj['escape']->{'html'}($data['post_featured_image']));
        $post->setStatus((string) $this->context->obj['escape']->{'html'}($data['post_status']));
        $post->setCreated((string) $this->context->obj['escape']->{'html'}($data['post_created']));
        $post->setPublished((string) $this->context->obj['escape']->{'html'}($data['post_published']));
        $post->setModified((string) $this->context->obj['escape']->{'html'}($data['post_modified']));
        return $post;
    }

    /**
     * Create a new Post object.
     *
     * @return TriTan\Common\Post\Post
     */
    protected function __create() : Post
    {
        return new Post();
    }

    /**
     * Inserts a new post into the post document.
     *
     * @since 0.9.9
     * @param Post $post Post object.
     * @return int Last insert id.
     */
    public function insert(Post $post)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post');
        $sql->begin();
        try {
            $sql->insert([
                'post_title' => $this->db->{'ifNull'}($post->getTitle()),
                'post_slug' => $this->db->{'ifNull'}($post->getSlug()),
                'post_content' => $this->db->{'ifNull'}($post->getContent()),
                'post_author' => (int) $post->getAuthor(),
                'post_type' => [
                    'posttype_id' => (int) $post->getPosttypeId(),
                    'post_posttype' => $this->db->{'ifNull'}($post->getPosttype())
                ],
                'post_attributes' => [
                    'parent' => [
                        'parent_id' => (int) $post->getParentId(),
                        'post_parent' => $this->db->{'ifNull'}($post->getParent())
                    ],
                    'post_sidebar' => (int) $post->getSidebar(),
                    'post_show_in_menu' => (int) $post->getShowInMenu(),
                    'post_show_in_search' => (int) $post->getShowInSearch()
                ],
                'post_relative_url' => $this->db->{'ifNull'}($post->getRelativeUrl()),
                'post_featured_image' => $this->db->{'ifNull'}($post->getFeaturedImage()),
                'post_status' => $this->db->{'ifNull'}($post->getStatus()),
                'post_created' => $this->db->{'ifNull'}($post->getCreated()),
                'post_published' => $this->db->{'ifNull'}($post->getPublished()),
            ]);
            $sql->commit();

            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    /**
     * Updates a Post object.
     *
     * @since 0.9.9
     * @param Post $post Post object.
     */
    public function update(Post $post)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post');
        $sql->begin();
        try {
            $sql->where('post_id', (int) $post->getId())->update([
                'post_title' => $this->db->{'ifNull'}($post->getTitle()),
                'post_slug' => $this->db->{'ifNull'}($post->getSlug()),
                'post_content' => $this->db->{'ifNull'}($post->getContent()),
                'post_author' => (int) $post->getAuthor(),
                'post_type' => [
                    'posttype_id' => (int) $post->getPosttypeId(),
                    'post_posttype' => $this->db->{'ifNull'}($post->getPosttype())
                ],
                'post_attributes' => [
                    'parent' => [
                        'parent_id' => (int) $post->getParentId(),
                        'post_parent' => $this->db->{'ifNull'}($post->getParent())
                    ],
                    'post_sidebar' => (int) $post->getSidebar(),
                    'post_show_in_menu' => (int) $post->getShowInMenu(),
                    'post_show_in_search' => (int) $post->getShowInSearch()
                ],
                'post_relative_url' => $this->db->{'ifNull'}($post->getRelativeUrl()),
                'post_featured_image' => $this->db->{'ifNull'}($post->getFeaturedImage()),
                'post_status' => $this->db->{'ifNull'}($post->getStatus()),
                'post_published' => $this->db->{'ifNull'}($post->getPublished()),
                'post_modified' => (string) $post->getModified()
            ]);
                
            $parent = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post');
            $parent->where('post_attributes.parent.parent_id', (int) $post->getId())
                    ->update([
                        'post_attributes.parent.post_parent' => $post->getSlug()
                    ]);
            
            $sql->commit();
            return (int) $post->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTMAPPER[update]: %s', $ex->getMessage()));
        }
    }

    /**
     * Save the Post object.
     *
     * @since 0.9.9
     * @param Post $post Post object.
     */
    public function save(Post $post)
    {
        if (is_null($post->getId())) {
            $this->insert($post);
        } else {
            $this->update($post);
        }
    }

    /**
     * Deletes post object.
     *
     * @since 0.9.9
     * @param Post $post Post object.
     * @return bool True if deleted, false otherwise.
     */
    public function delete(Post $post)
    {
        $sql = $this->db->table(c::getInstance()->get('tbl_prefix') . 'post');
        $sql->begin();
        try {
            $sql->where('post_id', $post->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('POSTMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
