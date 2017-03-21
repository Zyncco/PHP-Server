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

	static function array_diff_key_recursive(array $arr1, array $arr2){
		$diff = array_diff_key($arr1, $arr2);
		$intersect = array_intersect_key($arr1, $arr2);

		foreach($intersect as $k => $v){
			if(is_array($arr1[$k]) && is_array($arr2[$k])){
				$d = Utils::array_diff_key_recursive($arr1[$k], $arr2[$k]);

				if($d){
					$diff[$k] = $d;
				}
			}
		}

		return $diff;
	}

}
