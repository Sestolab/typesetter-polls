<?php

namespace Addon\Polls;

defined('is_running') or die('Not an entry point...');


class Poll extends \Addon\Polls\Common{

	public static function SectionTypes($section_types){
		global $addonPathData;

		if (file_exists($addonPathData.'/polls.php'))
			$section_types['poll'] = [
				'label' => 'Poll'
			];

		return $section_types;
	}


	public static function SectionToContent($section_data){
		if ($section_data['type'] != 'poll') return $section_data;

		if (self::isVoted($section_data['poll_id']))
			$section_data['content'] = \Addon\Polls\Common::ShowResults($section_data['poll_id']);
		else
			$section_data['content'] = self::ShowForm($section_data['poll_id']);

		return $section_data;
	}


	public static function NewSections($links){
		global $addonRelativeCode, $addonPathData;

		foreach ($links as $key => $section_type_arr)
			if ($section_type_arr[0] == 'poll')
				$links[$key] = ['poll', $addonRelativeCode.'/icons/section.png'];

		return $links;
	}


	public static function DefaultContent($default_content, $type){
		if ($type !== 'poll') return $default_content;

		return [
			'content' => '',
			'poll_id' => '',
			'gp_label' => 'Poll'
		];
	}


	public static function SaveSection($return, $section, $type){
		global $page;

		if ($type !== 'poll') return $return;

		if (!empty($_POST['poll_id']))
			$page->file_sections[$section]['poll_id'] = $_POST['poll_id'];

		return true;
	}


	public static function InlineEdit_Scripts($scripts, $type){
		global $addonRelativeCode, $addonPathData;

		if ($type !== 'poll') return $scripts;

		\Addon\Polls\Common::GetTranslations();

		echo 'var PollsLang = '.json_encode(\Addon\Polls\Common::$lang).';';
		echo 'var PollsEditor = {polls:'.json_encode(\Addon\Polls\Common::GetPolls()).'};';

		$scripts[] = $addonRelativeCode.'/js/PollsEdit.js';

		return $scripts;
	}


	public static function PageRunScript($cmd){
		switch ($cmd){
			case 'PollVote':
				isset($_POST['answer'])	&& self::Vote();
				break;

			case 'RefreshPollSection':
				isset($_REQUEST['poll_id']) && self::RefreshPollSection();
				break;
		}
		return $cmd;
	}


	private static function Vote(){
		if (self::isVoted()) return;

		global $addonPathData;

		$votesFile = $addonPathData.'/'.$_POST['poll_id'].'/votes.php';
		$votes = \gpFiles::Get($votesFile, 'votes');
		$answer = $_POST['answer'];
		$votes[$answer] = isset($votes[$answer]) ? $votes[$answer] + 1 : ($votes[$answer] = 1);

		\gpFiles::SaveArray($votesFile, 'votes', $votes);
		\gp\tool\Session::Cookie($_POST['poll_id'], true);
	}


	private static function RefreshPollSection(){
		global $page;
		$page->ajaxReplace = [];

		$section_options = [
			'type' => 'poll',
			'content' => '',
			'poll_id' => $_REQUEST['poll_id'],
		];
		$arg_value = \gp\tool\Output\Sections::SectionToContent($section_options, '');
		return $page->ajaxReplace[] = ['refresh_poll_section', 'arg', $arg_value];
	}


	private static function isVoted($poll_id=null){
		if (\common::GetCommand() == 'PollVote' && isset($poll_id))
			return isset($_POST['poll_id']) && $_POST['poll_id'] == $poll_id || isset($_COOKIE[$poll_id]);
		return isset($_COOKIE[isset($_POST['poll_id']) ? $_POST['poll_id'] : $poll_id]);
	}


	private static function ShowForm($poll_id){
		global $title;
		$poll = self::GetPoll($poll_id);

		if (!$poll) return '';

		self::GetTranslations();
		$form = '<h4>'.$poll['poll_question'].'</h4>
			<form action="'.\common::GetUrl($title).'" method="post">';

		foreach ($poll['poll_answers'] as $value)
			$form .='
				<label>
					<input type="radio" name="answer" value="'.$value.'" required />
					'.$value.'
				</label><br>';

		$form .= '<input type="hidden" name="poll_id" value="'.$poll_id.'" />';
		$form .= '<input type="hidden" name="cmd" value="PollVote" />';
		$form .= '<input type="submit" value="'.self::$lang['vote'].'" />';
		$form .= '</form>';

		return $form;
	}

}

