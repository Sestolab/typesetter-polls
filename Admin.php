<?php

namespace Addon\Polls;

defined('is_running') or die('Not an entry point...');


class Admin extends \Addon\Polls\Common{

	public function __construct(){
		global $addonRelativeCode, $page;
		$page->head_js[] = $addonRelativeCode.'/js/Polls.js';

		switch (\common::GetCommand()){
			case 'RemovePoll':
				isset($_POST['poll_id']) && $this->RemovePoll($_POST['poll_id']);
				break;
			case 'NewPoll':
				return $page->ajaxReplace = [['gpabox', '', $this->NewPoll()]];
			case 'SavePoll':
				$this->SavePoll();
			case 'ShowPoll':
				if (isset($_POST['poll_id']))
					return $page->ajaxReplace = [['gpabox', '', self::ShowResults($_POST['poll_id'])]];
				break;
		}

		$this->ShowPolls();

		echo \common::Link('Admin_Polls', self::$lang['new'],'cmd=NewPoll', 'class="gpbutton" name="gpabox"');

		echo '<div class="text-right">Made by <a href="https://sestolab.pp.ua" target="_blank">Sestolab</a></div>';
	}


	private function ShowPolls(){
		global $langmessage;

		echo '
			<h2>'.self::$lang['Polls_Admin'].'</h2>
			<table class="bordered full_width striped">
				<thead>
					<tr>
						<th>'.self::$lang['question'].'</th>
						<th></th>
					</tr>
				</thead>';
			foreach(self::GetPolls() as $i => $q)
				echo '<tr>
						<td>'.$q.'</td>
						<td>'
						.\common::Link(
							'Admin_Polls',
							'',
							'cmd=RemovePoll&poll_id='.$i,
							[
								'class' => 'gpbutton gpconfirm fa fa-trash',
								'data-cmd' => 'postlink',
								'title' => sprintf($langmessage['generic_delete_confirm'], $q)
							]
						)
						.\common::Link(
							'Admin_Polls',
							'',
							'cmd=ShowPoll&poll_id='.$i,
							[
								'class' => 'gpbutton fa fa-bar-chart',
								'data-cmd' => 'postlink'
							]
						).
					'</td>
				</tr>';
		echo '</table>';
	}


	private function RemovePoll($id){
		global $addonPathData, $langmessage;

		$polls = \gpFiles::Get($addonPathData.'/polls.php', 'polls');
		unset($polls[$id]);
		\gpFiles::RmAll($addonPathData.'/'.$id);
		\gpFiles::SaveArray($addonPathData.'/polls.php', 'polls', $polls);

	}


	private function NewPoll(){
		return '
			<h3>'.self::$lang['new_poll'].'</h3>
			<form id="new_poll" method="post" action="'.\common::GetUrl('Admin_Polls').'">
				<table class="bordered full_width">
					<tr>
						<th colspan="2">
							'.self::$lang['question'].'
						</th>
					</tr>
					<tr>
						<td>
							<input name="poll_question" class="gpinput full_width" placeholder="'.self::$lang['question'].'" required />
						</td>
					</tr>

					<tr>
						<th colspan="2">
							'.self::$lang['answers'].'
						</th>
					</tr>

					<tr>
						<td>
							<input name="poll_answers[]" class="gpinput full_width" placeholder="'.self::$lang['answer'].'" required />
						</td>
					</tr>

				</table>
				<input type="hidden" name="cmd" value="SavePoll" />
				<input type="submit" name="" value="'.self::$lang['create'].'" class="gpsubmit gpvalidate"  data-cmd="gppost"/>
			</form>
			';
	}


	private function SavePoll(){
		global $addonPathData, $langmessage;

		$poll['poll_question'] = htmlspecialchars($_POST['poll_question']);
		$poll['poll_answers'] = array_map('htmlspecialchars', $_POST['poll_answers']);

		$pollId = \common::RandomString(6);
		$pollsFile = $addonPathData.'/polls.php';
		$pollFile = $addonPathData.'/'.$pollId.'/poll.php';

		$polls = \gpFiles::Get($pollsFile, 'polls');
		$polls[$pollId] = $poll['poll_question'];

		if(\gpFiles::SaveArray($pollFile, 'poll', $poll) && \gpFiles::SaveArray($pollsFile, 'polls', $polls))
			return message($langmessage['SAVED']);
		message($langmessage['OOPS']);
	}



	static function AdminLinkLabel($link_label, $link_name){
		if ($link_name !== 'Admin_Polls') return $link_label;

		self::GetTranslations();

		return self::$lang['Polls_Admin'];
	}

}

