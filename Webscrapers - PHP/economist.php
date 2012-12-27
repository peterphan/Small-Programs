<?php 
	/* 2012 Peter Phan
	 * Scrapes the Economist for articles and adds it to the database
	 */
?>

<?php header('Content-Type: text/html; charset=utf-8'); ?>
<-- set charset to avoid funky characters being inserted into database -->
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
</head>

<?php
	$economist = new EconomistScraper();
	$economist->updateDatabase('http://www.economist.com/world/united-states');
	$economist->updateDatabase('http://www.economist.com/world/china');
	$economist->updateDatabase('http://www.economist.com/world/europe');
	$economist->updateDatabase('http://www.economist.com/culture');
	$economist->updateDatabase('http://www.economist.com/world/china');
	$economist->updateDatabase('http://www.economist.com/world/middle-east-africa');
	$economist->updateDatabase('http://www.economist.com/business-finance');
	$economist->updateDatabase('http://www.economist.com/economics');
	$economist->updateDatabase('http://www.economist.com/science-technology');
	$economist->closeConnection();
	
	class EconomistScraper {
		protected $articles = array();
		protected $domain;
		protected $category;

		// Set actions to run when the class is instantiated
		// edit to use prepared statements
		function __construct() {
			// set time limit to unlimited
			set_time_limit(0);
			include('../../connect.php');
		}

		public function updateDatabase($url) {
			mb_internal_encoding("UTF-8");
			// Set the root domain of the URL to concatinate with URLs later
			$this->domain   = explode("/", $url);
			
			$this->category = $this->domain[3];
			$this->domain   = 'http://' . $this->domain[2];
			$this->getArticleUrls($url);

			$stmt = $mysqli->stmt_init();
			$stmt->prepare("INSERT INTO economist(title, headline, url, description, category, subcategory, duration, content)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param('ssssssis', $title, $headline, $url, $description, $category, $subcategory, $duration, $content);
			
			// loop through all articles scraped and add it to the database
			foreach($this->articles as $article) {
				// escape all quotes before inserting into mysql database
				$title       = str_replace("'", "\'", $article['title']);

				$headline    = str_replace("'", "\'", $article['headline']);
				
				$url         = $article['url'];
				$description = str_replace("'", "\'", $article['description']);
				$category    = $article['category'];
				$subcategory = $article['subcategory'];
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
				die();
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
			$xpath = $this->getUrlDOM($url);
			// Get a list of articles from the section page
			$articleList = $xpath->query("//section[@class='ec-news-package node node-type-news_package node-published'] | //section[@class='ec-news-package node node-type-news_package node-published node-teaser']");

			// store all article and article contents in array
			foreach($articleList as $article) {
				$articleTag = $article->getElementsByTagName('article')->item(0);
				$title      = $article->getElementsByTagName('h1')->item(0)->nodeValue;
				$headline   = $articleTag->getElementsByTagName('h2')->item(0)->nodeValue;
				$query      = "SELECT id FROM economist WHERE title='$title' AND headline='$headline'";
				$query      = mysql_query($query);

				if(mysql_fetch_assoc($query)) {
					// article already inserted in database
					continue;
				}

				$description = $articleTag->getElementsByTagName('p')->item(0)->nodeValue;
				$spanLength  = mb_strlen($articleTag->getElementsByTagName('span')->item(0)->nodeValue);
				$description = substr_replace($description, "", -1*$spanLength-2);
				
				$link        = $this->domain . $articleTag->getElementsByTagName('a')->item(0)->getAttribute('href');
				$contents    = $this->getArticleContent($link);
				$subcategory = $contents['category'];
				$duration    = $contents['duration'];
				$content     = $contents['content'];

				$this->articles[]  = array(
					'title'       => $title,
					'headline'    => $headline,
					'url'         => $link,
					'description' => $description,
					'category'    => $this->category,
					'subcategory' => $subcategory,
					'duration'    => $duration,
					'contents'     => $content
					);
			}

		}

		public function getArticleContent($link) {
			$xpath = $this->getURLDOM($link);
			
			$articleBody        = $xpath->query("//div[@id='ec-article-body'] | //div[@class='node-blog-tpl']");
			$content            = array();
			$content['content'] = "";
			$array              = explode("/", $link);
			$wordCount          = 0;

			foreach($articleBody as $article) {
				$paragraphs         = $article->getElementsByTagName('p');
			}
	
			foreach($paragraphs as $paragraph) {
				// only add article paragraphs to the content
				// ignore every other tag like imgs
				$content['content'] .= '<p> ' . $paragraph->nodeValue . ' </p>';
				$wordCount += str_word_count($paragraph->nodeValue);
			}

			$content['duration'] = floor($wordCount / 250) * 60;

			if($array[3] == 'blogs') {
				$content['category'] = 'blog';
			} else {
				$categorys           = $xpath->query("//p[@class='ec-article-info']");
				$category            = $categorys->item(1)->nodeValue;
				$index               = strstr($category, '|');
				$content['category'] = substr($index, 2);
			}

			return $content;
		}

		private function getUserAgent(){
			return 'Googlebot/2.1 (+http://www.google.com/bot.html)';
		}

	}
?>