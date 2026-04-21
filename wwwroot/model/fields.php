<?php
	class Meta {
		public $key;
		public $name;
		public $value; // Always of type string
		
		public function __construct($key, $name, $value) {
			$this->key = $key;
			$this->name = $name;
			$this->value = $value;
		}
		
		public function hasValue() {
			return is_array($this->value) || strlen($this->value) > 0;
		}
	}

	abstract class BaseFields {
		protected function cleanTable($table) {
			for ($i = 0; $i < count($table); $i++) {
				if (!$table[$i]->hasValue()) {
					array_splice($table, $i, 1);
					$i--;
				}
			}
			return $table;
		}
		
		abstract public function createListSubtitle();
		
		protected function formatAsString($values, $separator) {
			$answer = "";
			foreach ($values as $v) {
				if (strlen($answer) > 0) {
					$answer .= $separator;
				}
				$answer .= $v;
			}
			return $answer;
		}
	}
	
	class Fields extends BaseFields {
		public $parent;
		public $parentPost;
		public $miles;
		public $elevationGain;
		public $highestElevation;
		public $yds_class;
		public $yds_rating;
		public $pitches;
		public $table;
		
		public $caltopo;
		public $caltopoLink;
		public $gpx;
		public $mapsTable;
		
		public $summit;
		
		public $mountainForecast;
		public $roamWeather;
		public $weatherTable;
		
		public function __construct($post) {
			$this->parent = getMeta($post, "parent", "Parent");
			if ($this->parent->hasValue()) {
				$this->parentPost = get_post($this->parent->value);
				$this->parent->value = '<a href="'.get_permalink($this->parentPost).'">'.$this->parentPost->post_title.'</a>';
			}
			$this->miles = getMeta($post, "miles", "Miles");
			$this->elevationGain = getMeta($post, "elevation_gain", "Elevation gain");
			$this->highestElevation = getMeta($post, "highest_elevation", "Highest elevation", $this->parentPost);
			$this->yds_class = getMeta($post, "yds_class", "Class");
			$this->yds_rating = getMeta($post, "yds_rating", "YDS rating");
			if ($this->yds_rating->hasValue()) {
				$this->yds_rating->value = "5." . $this->yds_rating->value;
				$subRating = getMeta($post, "yds_sub_rating", "YDS sub-rating");
				if ($subRating->hasValue() && $subRating->value != "none") {
					$this->yds_rating->value .= $subRating->value;
				}
			}
			$this->pitches = getMeta($post, "pitches", "Pitches");
			
			$this->table = [
				$this->parent,
				$this->miles,
				$this->elevationGain,
				$this->highestElevation
			];
			
			if ($this->yds_class->value == 5) {
				array_push($this->table, $this->yds_rating, $this->pitches);
			} else {
				array_push($this->table, $this->yds_class);
			}
			
			$this->table = $this->cleanTable($this->table);
			
			$this->caltopo = getMeta($post, "caltopo", "CalTopo");
			if ($this->caltopo->hasValue()) {
				$this->caltopoLink = $this->caltopo->value;
				$this->caltopo->value = '<a href="'.$this->caltopo->value.'" target="_blank">View map</a>';
			}
			$this->gpx = getMeta($post, "gpx", "GPX");
			if ($this->gpx->hasValue()) {
				$this->gpx->value = wp_get_attachment_link($this->gpx->value, '' , false, false, 'Download GPX');
			}
			
			$this->mapsTable = [
				$this->caltopo,
				$this->gpx	
			];
			
		  	$this->mapsTable = $this->cleanTable($this->mapsTable);
			  
			$this->summit = getMeta($post, "summit", "Summit", $this->parentPost);
			
			$this->mountainForecast = getMeta($post, "mountain_forecast", "Mountain Forecast", $this->parentPost);
			$this->roamWeather = new Meta("roam_weather", "Roam Weather", "");
			if ($this->summit->hasValue()) {
				$this->roamWeather->value = "https://weather.roamapps.com/forecast/".trim($this->summit->value["lat"])."/".trim($this->summit->value["lng"])."?name=".urlencode($post->post_title)."&profile=default-1";
			}
			
			$this->weatherTable = $this->cleanTable([
				$this->mountainForecast,
				$this->roamWeather
			]);
			
			foreach ($this->weatherTable as $weatherRow) {
				$weatherRow->value = '<a href="'.$weatherRow->value.'" target="_blank">View forecast</a>';
			}
		}

		public function createListSubtitle() {
			if ($this->yds_rating->hasValue()) {
				$items = [];
				array_push($items, $this->yds_rating->value);
				if ($this->pitches->hasValue()) {
					if ($this->pitches->value == 1) {
						array_push($items, "1 pitch");
					} else {
						array_push($items, $this->pitches->value . " pitches");
					}
				}
				return $this->formatAsString($items, ", ");
			} else if ($this->yds_class->hasValue()) {
				return "Class ".$this->yds_class->value;
			} else {
				return "";
			}
		}

		public static function get($post) {
			switch ($post->post_type) {
				case "plans":
				   return new FieldsForPlan($post);
				   
			   case "reports":
			   		return new FieldsForReport($post);
		   
			   default:
				   return new Fields($post);
			}
		}
	}

	class FieldsForPlan extends BaseFields {
        public $destinations;
        public $startDate;
        public $endDate;

        public $table;
		
		public function __construct($post) {
			// $this->peak = getMeta($post, "peak", "Peak");
			// if ($this->peak->hasValue()) {
			// 	$this->peakPost = get_post($this->peak->value);
			// 	$this->peak->value = '<a href="'.get_permalink($this->peakPost).'">'.$this->peakPost->post_title.'</a>';
            // }
            $this->startDate = getMeta($post, "start_date", "Start date");
            $this->endDate = getMeta($post, "end_date", "End date");
			
			$this->table = [
				$this->startDate,
				$this->endDate
			];
			
			$this->table = $this->cleanTable($this->table);
		}

		public function createListSubtitle() {
			// We don't need a subtitle for these
			return "";
		}

		public function getDateString() {
			// This is used for displaying in the list view
			if ($this->startDate->hasValue()) {
				if ($this->endDate->hasValue()) {
					return $this->formatDate($this->startDate->value) . " to " . $this->formatDate($this->endDate->value);
				}
				return $this->formatDate($this->startDate->value);
			} else {
				return "No date";
			}
		}

		private function formatDate($date) {
			return date("F j, Y", strtotime($date)); // July 30, 2019
		}
	}
	
	class FieldsForReport extends BaseFields {
		public $startDate;
        public $endDate;

        public $table;
		
		public function __construct($post) {
            $this->startDate = getMeta($post, "start_date", "Start date");
            $this->endDate = getMeta($post, "end_date", "End date");
			
			$this->table = [
				$this->startDate,
				$this->endDate
			];
			
			$this->table = $this->cleanTable($this->table);
		}

		public function createListSubtitle() {
			// We don't need a subtitle for these
			return "";
		}

		public function getDateString() {
			// This is used for displaying in the list view
			if ($this->startDate->hasValue()) {
				if ($this->endDate->hasValue()) {
					return $this->formatDate($this->startDate->value) . " to " . $this->formatDate($this->endDate->value);
				}
				return $this->formatDate($this->startDate->value);
			} else {
				return "No date";
			}
		}

		private function formatDate($date) {
			return date("F j, Y", strtotime($date)); // July 30, 2019
		}
	}
		
		
	
			
		function getMeta($post, $key, $name, $parentPostFallback = null) {
			$value = get_post_meta($post->ID, $key, true);
			$answer = new Meta($key, $name, $value);
			if (!$answer->hasValue() && !is_null($parentPostFallback)) {
				return getMeta($parentPostFallback, $key, $name);
			}
			return $answer;
		}
	
	
?>