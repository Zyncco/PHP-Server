<?php

/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Zync\Helpers;

class Utils {

	private static $data_types = ["boolean", "integer", "double", "string", "array", "object", "resource", "NULL"];

	static function array_diff_key_recursive(array $model, array $array){
		if(count($model) != count($array)){
			return false;
		}

		$diff = array_diff_key($model, $array);
		$intersect = array_intersect_key($model, $array);

		foreach($intersect as $k => $v){
			if(is_array($model[$k]) && is_array($array[$k])){
				$d = Utils::array_diff_key_recursive($model[$k], $array[$k]);

				if($d){
					$diff[$k] = $d;
				}
			}
		}

		return $diff;
	}

	static function array_validate_data_types(array $model, array $array){
		foreach($model as $key => $type){
			if(is_array($model[$key])){
				if(!Utils::array_validate_data_types($model[$key], $array[$key])){
					return false;
				}
			}else{
				if(in_array($type, Utils::$data_types)){
					if($type != gettype($array[$key])){
						var_dump($array[$key] . " NOT IN " . $type);
						return false;
					}
				}else{
					if(!in_array($array[$key], explode("|", $type))){
						var_dump($array[$key] . " NOT IN " . $type);
						return false;
					}
				}
			}
		}

		return true;
	}

	static function array_validate_model(array $model, array $array){
		return (count(Utils::array_diff_key_recursive($model, $array)) == 0)
			&& Utils::array_validate_data_types($model, $array);
	}

}
