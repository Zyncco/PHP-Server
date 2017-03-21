<?php

namespace Zync\Kinds;

use Google\Cloud\Datastore\Entity;
use Zync\Helpers\Datastore;

class User {

    private static $kind = "user";

    private static $columns = [
        'id' => 'integer',
        'email' => 'string',
        'google_token' => 'string',
        'zync_token' => 'string',
        'clip_count' => 'integer'
    ];

	/**
	 * @var Entity
	 */
    private $data;

    private function __construct($data) {
        $this->data = $data;
    }

	/**
	 * @return null|User
	 */
    public static function findByZyncToken($token){
        $query = Datastore::get()->query()
            ->kind(self::$kind)
            ->filter("zync_token", "=", $token);

        $result = Datastore::get()->runQuery($query);
        $user = $result->current();

        if(is_null($user)){
            return null;
        }

        return new User($user);
    }

	/**
	 * @return null|User
	 */
    public static function findByEmail($email) {
        $query = Datastore::get()->query()
            ->kind(self::$kind)
            ->filter("email", "=", $email);

        $result = Datastore::get()->runQuery($query);
        $user = $result->current();

        if(is_null($user)){
            return null;
        }

        return new User($user);
    }

	/**
	 * @return User
	 */
    public static function create($data){
        self::verifyData($data);

        $key = Datastore::get()->key(self::$kind);
        $entity = Datastore::get()->entity($key, $data);

        Datastore::get()->insert($entity);

        $data["id"] = $entity->key()->pathEndIdentifier();
        return new User($data);
    }

    private static function verifyData($data) {
        if ($invalid = array_diff_key($data, User::$columns)) {
            throw new \InvalidArgumentException(sprintf(
                'unsupported properties: "%s"',
                implode(', ', $invalid)
            ));
        }
    }

	/**
	 * @return Entity
	 */
    public function getData(){
        return $this->data;
    }

    public function setGoogleToken($token){
        $this->data["google_token"] = $token;
    }

    public function save(){
        $transaction = Datastore::get()->transaction();
        $transaction->upsert($this->data);
        $transaction->commit();
    }

	/**
	 * @return Clipboard
	 */
    public function getClipboard(){
		return Clipboard::findByUserID($this->data->key()->pathEndIdentifier());
    }

}