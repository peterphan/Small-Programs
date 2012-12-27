<?php
/* 2012 Peter Phan
 * Scrapes Youtube for videos and adds it to a mysql database
 * Uses the Youtube Zend API.
 * Youtube API has some bugs and results in some buggy queries
 */
	$yt = new YoutubeScraper();
	$yt->updateDatabase('Autos', 'medium');
	$yt->updateDatabase('Autos', 'short');
	$yt->updateDatabase('Animals', 'medium');
	$yt->updateDatabase('Animals', 'short');
	$yt->updateDatabase('Sports', 'medium');
	$yt->updateDatabase('Sports', 'short');
	$yt->updateDatabase('Travel', 'medium');
	$yt->updateDatabase('Travel', 'short');
	$yt->updateDatabase('Games', 'medium');
	$yt->updateDatabase('Games', 'short');
	$yt->updateDatabase('People', 'medium');
	$yt->updateDatabase('People', 'short');
	$yt->updateDatabase('Comedy', 'short');
	$yt->updateDatabase('Comedy', 'medium');
	$yt->updateDatabase('Entertainment', 'short');
	$yt->updateDatabase('Entertainment', 'medium');
	$yt->updateDatabase('News', 'short');
	$yt->updateDatabase('News', 'medium');
	$yt->updateDatabase('Howto', 'short');
	$yt->updateDatabase('Howto', 'medium');
	$yt->updateDatabase('Movies', 'short');
	$yt->updateDatabase('Movies', 'medium');
	$yt->updateDatabase('Trailers', 'short');
	$yt->updateDatabase('Trailers', 'medium');


	class YoutubeScraper {
		protected $yt;
		protected $videos = array();

		function __construct() {
			require_once 'Zend/Loader.php';
			Zend_Loader::loadClass('Zend_Gdata_YouTube');
			$this->yt = new Zend_Gdata_YouTube();
			$this->yt->setMajorProtocolVersion(2);
		}

		public function updateDatabase($category, $duration) {
			include('../../connect.php');
			
			$this->getVideos($category, $duration);

			$stmt = $mysqli->stmt_init();
			$stmt->prepare("INSERT INTO youtube(title, category, videoId, description, smallThumb, largeThumb, duration, views, rating) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)");

			$stmt->bind_param('ssssssiis', $title, $category, $videoId, $description, $smallThumb, $largeThumb, $duration, $views, $rating);

			// loop through all videos scraped and add into database
			// TODO: Check for duplicates in database
			foreach($this->videos as $video) {
				$title       = $video['title'];
				$videoId     = $video['videoId'];
				$description = $video['description'];
				$smallThumb  = $video['smallThumb'];
				$largeThumb  = $video['largeThumb'];
				$duration    = $video['duration'];
				$views       = $video['views'];
				$rating      = $video['rating'];

				if(!$stmt->execute()) {
					echo 'Error inserting ' . $stmt->error;
				}
			}
			echo 'DONE!';
			$stmt->close();
		}

		private function getVideos($category, $duration) {
			$feedUrl = 'http://gdata.youtube.com/feeds/api/videos/?max-results=5&category=' . $category . '&duration='.$duration.'&v=2';
			$sxml = simplexml_load_file($feedUrl);

			foreach($sxml->entry as $entry) {
				// get nodes in media: namespace for media information
				$media = $entry->children('http://search.yahoo.com/mrss/');
		      	
		      	// get title
		      	$title = $entry->title;

				// get video player URL
				$attrs    = $media->group->player->attributes();
				$videoUrl = $attrs['url'];
				$start    = strpos($videoUrl, '=');
				$end      = strpos($videoUrl, '&', $start+1);
				$videoId  = substr($videoUrl, $start+1, $end-$start-1);

				// description
				$description = $media->group->description;

				// thumbnails in serialized format
		      	$thumbnails = $media->group->thumbnail;

		      	$smallThumb = $thumbnails[0]->attributes();
		      	$largeThumb = $thumbnails[2]->attributes();

				// get <yt:duration> node for video length
				$yt     = $media->children('http://gdata.youtube.com/schemas/2007');
				$attrs  = $yt->duration->attributes();
				$length = $attrs['seconds']; 
		      	
		      	// get view count
				$yt    = $entry->children('http://gdata.youtube.com/schemas/2007');
				$views = $yt->statistics->attributes();
				$views = $views['viewCount'];

		      	// get rating
				$gd     = $entry->children('http://schemas.google.com/g/2005');
				$rating = $gd->rating->attributes();
				$rating = $rating['average'];

		      	$this->videos[] = array(
						'title'      => $title,
						'videoId'    => $videoId,
						'description' => $description,
						'smallThumb' => $smallThumb,
						'largeThumb' => $largeThumb,
						'duration'   => $length,
						'views'      => $views,
						'rating'     => $rating);
			}
		}
	}