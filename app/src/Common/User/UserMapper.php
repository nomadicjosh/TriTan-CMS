<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\User\UserMapperInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Exception\Exception;
use TriTan\Exception\InvalidArgumentException;
use TriTan\Common\User\User;
use Cascade\Cascade;

class UserMapper implements UserMapperInterface
{
    public $db;
    
    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    /**
     * Fetch a user object by ID
     *
     * @since 0.9.9
     * @param string $id
     * @return TriTan\Common\User\User|null Returns user object if exist and NULL otherwise.
     */
    public function findById($id)
    {
        if (!is_integer($id) || (int) $id < 1) {
            throw new InvalidArgumentException('The ID of this entity is invalid.', 'invalid_id');
        }

        $user = $this->findBy('id', $id);

        return $user;
    }

    /**
     * Return only the main user fields.
     *
     * @since 0.9.9
     * @param string $field The field to query against: 'id', 'ID', 'email' or 'login'.
     * @param string|int $value The field value
     * @return object|false Raw user object
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
                $user_id = (int) $value;
                $db_field = 'user_id';
                break;
            case 'email':
                $user_id = $this->context->obj['cache']->{'read'}($value, 'useremail');
                $db_field = 'user_email';
                break;
            case 'login':
                $value = $this->context->obj['sanitizer']->{'username'}($value);
                $user_id = $this->context->obj['cache']->{'read'}($value, 'userlogins');
                $db_field = 'user_login';
                break;
            default:
                return false;
        }

        $user = null;

        if (false !== $user_id) {
            if ($data = $this->context->obj['cache']->{'read'}($user_id, 'users')) {
                is_array($data) ? $this->context->obj['util']->{'toObject'}($data) : $data;
            }
        }

        if (!$data = $this->db->table('user')->where($db_field, sprintf('%s', $value))->first()) {
            return false;
        }

        if ($data != null) {
            $user = $this->create($data);
            $this->context->obj['usercache']->{'update'}($user);
        }

        if (is_array($user)) {
            $user = $this->context->obj['util']->{'toObject'}($user);
        }

        return $user;
    }
    
    /**
     * Fetch all users.
     * 
     * @since 0.9.9
     * @return User User data object.
     */
    public function findAll()
    {
        $data = $this->db->table('user')->all();
        $users = [];
        if($data != null) {
            foreach($data as $user) {
                $users[] = $this->create($user);
            }
        }
        return $users;
    }

    /**
     * Create a new instance of User. Optionally populating it
     * from a data array.
     *
     * @param array $data
     * @return TriTan\Common\User\User.
     */
    public function create(array $data = null) : User
    {
        $user = $this->__create();
        if ($data) {
            $user = $this->populate($user, $data);
        }
        return $user;
    }

    /**
     * Populate the User object with the data array.
     *
     * @param User $user object.
     * @param array $data User data.
     * @return TriTan\Common\User\User
     */
    public function populate(User $user, array $data) : User
    {
        $user->setId( (int) $this->context->obj['escape']->{'html'}($data['user_id']) );
        $user->setLogin( (string) $this->context->obj['escape']->{'html'}($data['user_login']) );
        $user->setFname( (string) $this->context->obj['escape']->{'html'}($data['user_fname']) );
        $user->setLname( (string) $this->context->obj['escape']->{'html'}($data['user_lname']) );
        $user->setEmail( (string) $this->context->obj['escape']->{'html'}($data['user_email']) );
        $user->setPassword( (string) $this->context->obj['html']->{'purify'}($data['user_pass']) );
        $user->setUrl( (string) $this->context->obj['escape']->{'html'}($data['user_url']) );
        $user->setAddedBy( (int) $this->context->obj['escape']->{'html'}($data['user_addedby']) );
        $user->setRegistered( (string) $this->context->obj['escape']->{'html'}($data['user_registered']) );
        $user->setModified( (string) $this->context->obj['escape']->{'html'}($data['user_modified']) );
        $user->setActivationKey( (string) $this->context->obj['escape']->{'html'}($data['user_activation_key']) );
        return $user;
    }

    /**
     * Create a new User object.
     *
     * @return TriTan\Common\User\User
     */
    protected function __create() : User
    {
        return new User();
    }

    /**
     * Inserts a new user into the user document.
     *
     * @since 0.9.9
     * @param User $user User object.
     * @return int Last insert id.
     */
    public function insert(User $user)
    {
        $sql = $this->db->table('user');
        $sql->begin();
        try {
            $sql->insert([
                'user_login' => $this->db->{'ifNull'}($user->getLogin()),
                'user_fname' => $this->db->{'ifNull'}($user->getFname()),
                'user_lname'  => $this->db->{'ifNull'}($user->getLname()),
                'user_email'  => $this->db->{'ifNull'}($user->getEmail()),
                'user_pass'   => $this->db->{'ifNull'}($user->getPassword()),
                'user_url' => $this->db->{'ifNull'}($user->getUrl()),
                'user_addedby' => (int) $user->getAddedBy(),
                'user_registered' => $this->db->{'ifNull'}($user->getRegistered()),
                'user_activation_key' => $this->db->{'ifNull'}($user->getActivationKey())
            ]);
            $sql->commit();
            return (int) $sql->lastInsertId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('USERMAPPER[insert]: %s', $ex->getMessage()));
        }
    }

    /**
     * Updates a User object.
     *
     * @since 0.9.9
     * @param User $user User object.
     * @return The user's id.
     */
    public function update(User $user)
    {
        $sql = $this->db->table('user');
        $sql->begin();
        try {
            $sql->where('user_id', (int) $user->getId())->update([
                'user_login' => $this->db->{'ifNull'}($user->getLogin()),
                'user_fname' => $this->db->{'ifNull'}($user->getFname()),
                'user_lname'  => $this->db->{'ifNull'}($user->getLname()),
                'user_email'  => $this->db->{'ifNull'}($user->getEmail()),
                'user_pass'   => $this->db->{'ifNull'}($user->getPassword()),
                'user_url' => $this->db->{'ifNull'}($user->getUrl()),
                'user_modified' => $this->db->{'ifNull'}($user->user_modified),
                'user_activation_key' => $this->db->{'ifNull'}($user->getActivationKey())
            ]);
            $sql->commit();
            return (int) $user->getId();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('USERMAPPER[update]: %s', $ex->getMessage()));
        }
    }

    /**
     * Save the User object.
     *
     * @since 0.9.9
     * @param User $user User object.
     */
    public function save(User $user)
    {
        if (is_null($user->getId())) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    /**
     * Deletes user object.
     *
     * @since 0.9.9
     * @param User $user User object.
     * @return bool True if deleted, false otherwise.
     */
    public function delete(User $user)
    {
        $sql = $this->db->table('user');
        $sql->begin();
        try {
            $sql->where('user_id', $user->getId())
                ->delete();
            $sql->commit();
        } catch (Exception $ex) {
            $sql->rollback();
            Cascade::getLogger('error')->error(sprintf('USERMAPPER[delete]: %s', $ex->getMessage()));
        }
    }
}
