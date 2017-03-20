<?php

namespace Zync\Kinds;

use Google\Cloud\Datastore\Key;
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

    private $data;

    private function __construct($data) {
        $this->data = $data;
    }

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

    public function getData(){
        return $this->data;
    }

    public function getHexPath(){
        $hex = dechex($this->data["id"]);
        $padded = str_pad($hex, 14, "0");
        $path = implode("/", str_split($padded, 2));
        return $path;
    }

    public function setGoogleToken($token){
        $this->data["google_token"] = $token;
    }

    public function save(){
        $transaction = Datastore::get()->transaction();
        $transaction->upsert($this->data);
        $transaction->commit();
    }

}