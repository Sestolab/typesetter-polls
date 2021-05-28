<?php

namespace Addon\Polls;

defined('is_running') or die('Not an entry point...');


class Common{

	public static $lang;


	static function GetTranslations(){
		global $config, $addonPathCode;

		$path = $addonPathCode.'/languages/';
		self::$lang = \gpFiles::Get($path.$config['language'].'.php', 'lang') ?: \gpFiles::Get($path.'en.php', 'lang');
	}


	static function GetPolls(){
		global $addonPathData;
		return \gpFiles::Get($addonPathData.'/polls.php', 'polls');
	}


	static function GetPoll($id){
		global $addonPathData;
		return \gpFiles::Get($addonPathData.'/'.$id.'/poll.php', 'poll');
	}


	static function GetVotes($id){
		global $addonPathData;
		return \gpFiles::Get($addonPathData.'/'.$id.'/votes.php', 'votes');
	}


	static function ShowResults($poll_id){
		$votes = self::GetVotes($poll_id);
		$poll = self::GetPoll($poll_id);

		$content = '<h4>'.$poll['poll_question'].'</h4>';

		foreach ($poll['poll_answers'] as $answer)
			$content .= '<p>'.$answer.' ('.(isset($votes[$answer]) ? $votes[$answer] : 0).')</p>';

		return $content;
	}

}

