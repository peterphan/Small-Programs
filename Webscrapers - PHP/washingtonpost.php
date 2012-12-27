<?php 
	/* 2012 Peter Phan
	 * Scrapes the Washington Post for articles and adds it to the database
	 */
?>
<?php header('Content-Type: text/html; charset=utf-8'); ?>

<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<?php
	$washingtonPost = new WashingtonPostScraper();
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/sports');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/politics');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/national');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/world');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/business');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/business/technology');
	$washingtonPost->updateDatabase('http://www.washingtonpost.com/entertainment');
	$washingtonpost->closeConnection();

	class WashingtonPostScraper {
		protected $articles = array();
		protected $domain;
		protected $category;

		// Set actions to run when the class is instantiated
		function __construct() {
			// set time limit to unlimited
			set_time_limit(0);
			//include('../../connect.php');
		}

		public function updateDatabase($url) {
			mb_internal_encoding("UTF-8");
			// Set the root domain of the URL to concatinate with URLs later
	
			$this->domain   = explode("/", $url);
			$this->category = $this->domain[3];
			$this->domain   = 'http://' . $this->domain[2];

			$this->getArticleUrls($url);
			include('../../connect.php');
			$stmt = $mysqli->stmt_init();
			$stmt = $mysqli->stmt_init();
			$stmt->prepare("INSERT INTO washingtonpost(title, author, url, description, category, duration, content)
				VALUES (?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('sssssis', $title, $author, $url, $description, $category, $duration, $content);
			foreach($this->articles as $article) {
				$title       = str_replace("'", "\'", $article['title']);
				$author      = $article['author'];
				$url         = $article['url'];
				$category    = $article['category'];
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

		private function getArticleUrls($url) {
			include('../../connect.php');
			$xpath = $this->getUrlDOM($url);
			if(!$xpath) return 0;
			// Get a list of articles from the section page
			$articleList = $xpath->query("//div[@class='module s1 img-border ']");
			$stmt = $mysqli->stmt_init();
			if(!$stmt->prepare("SELECT id FROM washingtonpost WHERE url=?")) die($stmt->error);
			$stmt->bind_param('s', $link);
			$stmt->bind_result($result);

			// store all article and article contents in array
			foreach($articleList as $article) {
				$link        = $this->domain . $article->getElementsByTagName('a')->item(0)->getAttribute('href');
				$title       = $article->getElementsByTagName('h2')->item(0)->nodeValue;
				$paragraphs  = $article->getElementsByTagName('p');
				$author      = $paragraphs->item(0)->nodeValue;
				$description = $paragraphs->item(1)->nodeValue;
				$stmt->execute();
				if($stmt->fetch()) {
					// article already inserted in database
					continue;
				}
				$duration = 0;
				$contents = "";

				$category    = $this->getArticleContent($link, $duration, $contents);
				if(!$category) continue;

				$duration = floor($duration / 250) * 60;

				$this->articles[]  = array(
					'title'       => $title,
					'author'      => $author,
					'url'         => $link,
					'category'    => $category,
					'description' => $description,
					'duration'    => $duration,
					'contents'    => $contents
					);
			}
			$stmt->close();
			$mysqli->close();
		}

		public function getArticleContent($link, &$duration, &$contents) {
			$xpath = $this->getURLDOM($link);
			if(!$xpath) return 0;
			$articleBody        = $xpath->query("//div[@class='article_body'] | //div[@class='article_body entry-content']");
			$category            = explode("/", $link);
			$category            = $category[3];

			// don't add in blogs
			if($category == "blogs") return 0;

			foreach($articleBody as $article) {
				$paragraphs         = $article->getElementsByTagName('p');
	
				foreach($paragraphs as $paragraph) {
					$contents .= '<p> ' . $paragraph->nodeValue . ' </p>';
					$duration += str_word_count($paragraph->nodeValue);
				}
			}

			//Check to see if the Next link is active
			$nextPageUrl = $xpath->query("//div[@class='pagination-list']/ul/li/a[@class='next-page']");
			foreach($nextPageUrl as $nextPage) {
				$this->getArticleContent($this->domain . $nextPage->getAttribute('href'), $duration, $contents);
			}

			return $category;
		}

		private function getUserAgent() {
			return 'Googlebot/2.1 (+http://www.google.com/bot.html)';
		}

	}
?>