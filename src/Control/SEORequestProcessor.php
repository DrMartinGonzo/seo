<?php

/**
 * Created by Nivanka Fonseka (nivanka@silverstripers.com).
 * User: nivankafonseka
 * Date: 4/2/18
 * Time: 6:59 AM
 * To change this template use File | Settings | File Templates.
 */
namespace SilverStripers\seo\Control;


use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPMiddleware;
use SilverStripe\SiteConfig\SiteConfig;

class SEORequestProcessor implements HTTPMiddleware {


	public function processInputs($body)
	{
		$config = SiteConfig::current_site_config();

		// head scripts
		if($config->HeadScripts && strpos($body, '</head>') !== false) {
			$head = strpos($body, '</head>');
			$before = substr($body, 0, $head);
			$after = substr($body, $head + strlen('</head>'));
			$body = $before . $config->HeadScripts . '</head>' . $after;
		}

		// end of body
		if($config->BodyStartScripts && strpos($body, '<body') !== false) {
			preg_match("/<body(.)*>/", $body, $matches);
			if($matches) {
				$bodyTag = $matches[0];
				$start = strpos($body, $bodyTag);
				$before = substr($body, 0, $start);
				$after = substr($body, $start + strlen($bodyTag));
				$body = $before . $bodyTag . $config->BodyStartScripts . $after;
			}
		}

		// end of body
		if($config->BodyEndScripts && strpos($body, '</body>') !== false) {
			$bodyEnd = strpos($body, '</body>');
			$before = substr($body, 0, $bodyEnd);
			$after = substr($body, $bodyEnd + strlen('</body>'));
			$body = $before . $config->BodyEndScripts . '</body>' . $after;
		}
		return $body;
	}


	public function process(HTTPRequest $request, callable $delegate)
	{
		$response = $delegate($request);
		if($response && ($body = $response->getbody())) {
			$body = $this->processInputs($body);
			$response->setBody($body);
		}
		return $response;
	}
}