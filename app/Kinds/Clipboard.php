<?php

namespace Zync\Kinds;

use Google\Cloud\Datastore\Entity;
use Zync\Helpers\Bucket;
use Zync\Helpers\Datastore;

class Clipboard {

	const CLIP_COUNT = 10;
	const EXPIRY_TIME_MIN = 60000; // 60 Seconds
	const EXPIRY_TIME_MAX = 300000; // 300 Seconds

	private static $kind = "clipboard";

	/**
	 * @var Entity
	 */
	private $data;

	private function __construct($data) {
		$this->data = $data;
	}

	/**
	 * @return Clipboard
	 */
	public static function findByUserID($id){
		$query = Datastore::get()->query()
			->kind(self::$kind)
			->filter("user", "=", $id);

		$result = Datastore::get()->runQuery($query);
		$clipboard = $result->current();

		if(is_null($clipboard)){
			return null;
		}

		return new Clipboard($clipboard);
	}

	/**
	 * @return Clipboard
	 */
	public static function create($userID, $data){
		$insert = [
			'user' => $userID,
			'clip_count' => 1,
			'clips' => [
				$data["timestamp"] => [
					"hash" => $data["hash"],
					"encryption" => $data["encryption"],
					"payload-type" => $data["payload-type"]
				]
			],
		];

		$key = Datastore::get()->key(self::$kind);
		$entity = Datastore::get()->entity($key, $insert);

		Datastore::get()->insert($entity);

		$insert["id"] = $entity->key()->pathEndIdentifier();
		return new Clipboard($entity);
	}

	/**
	 * @return Entity
	 */
	public function getData(){
		return $this->data;
	}

	public function save(){
		$transaction = Datastore::get()->transaction();
		$transaction->upsert($this->data);
		$transaction->commit();
	}

	public function saveContents($data, $timestamp){
		$path = $this->getHexPath($timestamp);

		$file = Bucket::get()->upload($data, [
			"name" => $path
		]);

		return $file;
	}

	/**
	 * @return string
	 */
	public function getHexPath($timestamp){
		$hex = dechex($this->data->key()->pathEndIdentifier());
		$padded = str_pad($hex, 14, "0");
		$path = implode("/", str_split($padded, 2));
		$path = "/data/clipboards/" . $path . "/" . $timestamp;
		return $path;
	}

	public function newClip($data){
		$clips = $this->data["clips"];

		if(!isset($clips[$data["timestamp"]]) && count($clips) + 1 > Clipboard::CLIP_COUNT){
			asort($clips);
			$removed = array_slice($clips, -1, 1, true);
			$timestamp = key($removed);

			try{
				Bucket::get()->delete([
					"name" => $this->getHexPath($timestamp)
				]);
			}catch(\Exception $e){
			}
		}

		$clips[$data["timestamp"]] = [
			"hash" => $data["hash"],
			"encryption" => $data["encryption"],
			"payload-type" => $data["payload-type"]
		];

		$this->data["clips"] = $clips;
		$this->data["clip_count"] = $clips["clip_count"] + 1;
	}

	public function exists($crc32){
		foreach($this->data["clips"] as $timestamp => $clip){
			if($clip["hash"]["crc32"] == $crc32){
				return true;
			}
		}

		return false;
	}

	public function getHistory(){
		$history = [];
		$clips = $this->data["clips"];

		foreach($clips as $timestamp => $clip){
			array_push($history, [
				"timestamp" => $timestamp,
				"hash" => $clip["hash"],
				"encryption" => $clip["encryption"],
				"payload-type" => $clip["payload-type"]
			]);
		}

		return $history;
	}

	public function getLastClipboardContents(){
		$clips = $this->data["clips"];
		return Bucket::get()->object($this->getHexPath(key($clips)))->downloadAsString();
	}

	public function getTimestampClipboardContents($timestamp){
		$clips = $this->data["clips"];

		if(!isset($clips[$timestamp])){
			return null;
		}

		return Bucket::get()->object($this->getHexPath($timestamp))->downloadAsString();
	}

	public function getLastClipboardVerification(){
		$clips = $this->data["clips"];
		$lastTimestamp = key($clips);

		return [
			"timestamp" => $lastTimestamp,
			"hash" => $clips[$lastTimestamp]["hash"],
			"encryption" => $clips[$lastTimestamp]["encryption"],
			"payload-type" => $clips[$lastTimestamp]["payload-type"]
		];
	}

	public function getTimestampClipboardVerification($timestamp){
		$clips = $this->data["clips"];

		if(!isset($clips[$timestamp])){
			return null;
		}

		return [
			"timestamp" => $timestamp,
			"hash" => $clips[$timestamp]["hash"],
			"encryption" => $clips[$timestamp]["encryption"],
			"payload-type" => $clips[$timestamp]["payload-type"]
		];
	}

}