<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools\Objects;

/**
 * Open Graph protocol person (profile)
 *
 * @link http://ogp.me/ Open Graph protocol
 */
class Profile extends Object
{
    /**
     * Property prefix
     * @var string
     */
    const PREFIX = 'profile';

    /**
     * prefix namespace
     * @var string
     */
    const NS = 'http://ogp.me/ns/profile#';

    /**
     * A person's given name
     * @var string
     */
    protected $first_name;

    /**
     * A person's last name
     * @var string
     */
    protected $last_name;

    /**
     * The profile's unique username
     * @var string
     */
    protected $username;

    /**
     * Gender: male or female
     */
    protected $gender;

    /**
     * Get the person's given name
     * @return string given name
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set the person's given name
     * @param string $first_name given name
     */
    public function setFirstName($first_name)
    {
        if (is_string($first_name) && !empty($first_name)) {
            $this->first_name = $first_name;
        }

        return $this;
    }

    /**
     * The person's family name
     * @return string famil name
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set the person's family name
     * @param string $last_name family name
     */
    public function setLastName($last_name)
    {
        if (is_string($last_name) && !empty($last_name)) {
            $this->last_name = $last_name;
        }

        return $this;
    }

    /**
     * Person's username on your site
     * @return string account username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the account username
     * @param string $username username
     */
    public function setUsername($username)
    {
        if (is_string($username) && !empty($username)) {
            $this->username = $username;
        }

        return $this;
    }

    /**
     * The person's gender. male|female
     * @return string male|female
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set the person's gender
     * @param string $gender male|female
     */
    public function setGender($gender)
    {
        if (is_string($gender) && ($gender === 'male' || $gender === 'female')) {
            $this->gender = $gender;
        }

        return $this;
    }
}
