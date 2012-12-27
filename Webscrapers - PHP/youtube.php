<?php 
	/* 2012 Peter Phan
	 * Scrapes the Youtube for articles and adds it to the database
	 *
	 * Note: Unfinished.  Used Youtube API instead of the normal scraping.
	 * This method has more accurate queries but fails to get some more important
	 * meta data like view cout
	 */
?>
<?php header('Content-Type: text/html; charset=utf-8'); ?>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<?php
	$youtube = new NYTimesScraper();
	$youtube->updateDatabase('http://www.youtube.com/autos');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/national/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/nyregion/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/business/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/technology/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/health/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/sports/index.html');
	// $youtube->updateDatabase('http://www.nytimes.com/pages/arts/index.html');
	$youtube->closeConnection();
	
	class NYTimesScraper {
		protected $articles = array();
		protected $domain;
		protected $category;

		// Set actions to run when the class is instantiated
		function __construct() {
			// set time limit to unlimited
			set_time_limit(0);
			include('../../connect.php');
		}

		public function updateDatabase($url) {
			mb_internal_encoding("UTF-8");
			// Set the root domain of the URL to concatinate with URLs later
			include('../../connect.php');
			$this->domain   = explode("/", $url);
			$this->category = $this->domain[3];
			$this->domain   = 'http://' . $this->domain[2];

			$this->getArticleUrls($url);

			$stmt = $mysqli->stmt_init();
			$stmt->prepare("INSERT INTO nytimes(title, author, url, description, category, subcategory, duration, content)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('ssssssis', $title, $author, $url, $description, $category, $subcategory, $duration, $content);
			foreach($this->articles as $article) {
				$title       = str_replace("'", "\'", $article['title']);
				$author      = $article['author'];
				$url         = $article['url'];
				$category    = $article['category'];
				$subcategory = $article['subcategory'];
				$description = str_replace("'", "\'", $article['description']);
				$duration    = $article['duration'];
				$content     = str_replace("'", "\'", $article['contents']);

				if(!$stmt->execute()) {
					echo 'Error inserting ' .$stmt->error;
				}
			}

			echo 'Done adding to database! <br />';
			$stmt->close();
		}

		public function closeConnection() {
			$mysqli->close();
		}

		private function getUrlDOM($url) {
			// Instantiate cURL to grab the HTML page.
			$c = curl_init($url);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_USERAGENT, $this->getUserAgent());
			curl_setopt($c, CURLOPT_FAILONERROR, true);
			curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($c, CURLOPT_AUTOREFERER, true);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_TIMEOUT, 20);
			// Add curl_setopt here to grab a proxy from your proxy list so that you don't get 403 errors from your IP being banned by the site

			// Grab the data.
			$html = curl_exec($c);
			// Check if the HTML didn't load right, if it didn't - report an error
			if (!$html) {
				echo 'cURL error number: ' . curl_errno($c) . ' on URL: ' . $url . '<br />';
				echo 'cURL error: ' . curl_error($c) . '<br />';
				return 0;
			}
			// Close connection.
			curl_close($c);
			$html = @mb_convert_encoding($html, 'HTML-ENTITIES', 'utf-8'); 
			
			// Parse the HTML information and return the results.
			$dom = new DOMDocument();
			@$dom->loadHtml($html);

			$xpath = new DOMXPath($dom);
			return $xpath;
		}

		// Start Get Article Urls
		// $startTag should look something like "section[@class='something']"
		private function getArticleUrls($url) {

			$xpath = $this->getUrlDOM($url);
			if(!$xpath) return 0;
			// Get a list of articles from the section page
			$articleList = $xpath->query("//div[@class='browse-collection  has-box-ad']|//div[@class='browse-collection ']");

			// store all article and article contents in array
			foreach($articleList as $article) {
				$divs = $article->getElementsByTagName('div');
				foreach($divs as $div) {
					if($div->getAttribute('class') == 'browse-item-row ytg-box') {
						$videos = $div->getElementsByTagName('div');
						// div containing each video's information
						foreach($videos as $video) {
							if(($video->getAttribute('class') == 'browse-item yt-tile-default ') || ($video->getAttribute('class') == 'browse-item yt-tile-default last')) {
								$thumbnail = $video->getElementsByTagName('img');
								$imgUrl      = $thumbnail->item(0)->getAttribute('src');

								// need to extract substring for video Id
								$videoUrl    = $video->getElementsByTagName('a')->item(0)->getAttribute('href');
								$start       = strpos($videoUrl, '=');
								$end         = strpos($videoUrl, '&', $start+1);
								$videoId     = substr($videoUrl, $start+1, $end-$start-1);


								$videoName   = $video->getElementsByTagName('h3')->item(0)->nodeValue;
								$itemContent = $video->getElementsByTagName('div')->item(0);
								$spans       = $itemContent->getElementsByTagName('span');

								// views and date
								$views       = $spans->item(0)->nodeValue;
								$date        = $spans->item(2)->nodeValue;
								
							}

						}
					}
				}
				die();
				$aTag        = $article->getElementsByTagName('h3')->item(0);
				if(!$aTag) continue;
				$aTag       = $aTag->getElementsByTagName('a')->item(0);
				$link       = $aTag->getAttribute('href');
				$title      = $aTag->nodeValue;
				$author     = $article->getElementsByTagName('h6')->item(0)->nodeValue;
				$category   = explode('/', $url);
				$category   = $category[4];
				
				$paragraphs = $article->getElementsByTagName('p');
				
				$description = $paragraphs->item(0)->nodeValue;
				
				$query       = "SELECT id FROM nytimes WHERE title='$title' AND author='$author'";
				$query       = mysql_query($query);

				if(@mysql_fetch_assoc($query)) {
					// article already inserted in database
					continue;
				}
				$duration    = 0;
				$contents    = "";
				$subcategory = $this->getArticleContent($link, $duration, $contents);
				$duration    = floor($duration / 250)*60;

				if(!$subcategory) continue;

				$this->articles[]  = array(
					'title'       => $title,
					'author'      => $author,
					'url'         => $link,
					'category'    => $category,
					'subcategory' => $subcategory, 
					'description' => $description,
					'duration'    => $duration,
					'contents'    => $contents
					);
			}

		}

		// duration and contents, returns subcategory
		public function getArticleContent($link, &$wordCount, &$contents) {
			$xpath = $this->getURLDOM($link);

			if(!$xpath) return 0;
			$articleBody = $xpath->query("//div[@class='articleBody']");
			$subcategory = explode("/", $link);

			// don't add blogs
			if($subcategory[2] == "blogs") return 0;
			$subcategory = $subcategory[7];


			// don't add in blogs
			if($category == "blogs") return 0;

			foreach($articleBody as $article) {

				$paragraphs = $article->getElementsByTagName('p');

				foreach($paragraphs as $paragraph) {
					$contents .= '<p> ' . $paragraph->nodeValue . ' </p>';
					$wordCount += str_word_count($paragraph->nodeValue);
				}
			}

			//Check to see if the Next link is active
			$nextPageUrl = $xpath->query("//div[@id='pageLinks']/a[@class='next']");
			foreach($nextPageUrl as $nextPage) {
				$this->getArticleContent($this->domain . $nextPage->getAttribute('href'), $wordCount, $contents);
			}

			return $subcategory;
		}

		private function getUserAgent(){
			return 'Googlebot/2.1 (+http://www.google.com/bot.html)';
		}

	}
?>