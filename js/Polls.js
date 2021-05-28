$(function(){
	const Polls = {
		answerInput: 'input[name="poll_answers[]"]',
		isLastAnswerInput: function(){
			return $('form').find(Polls.answerInput).length == 1;
		}
	}


	$(document).on('click', 'form#new_poll input.gpsubmit', function(){
		$('form#new_poll').find(Polls.answerInput).each(function(){
			if (!Polls.isLastAnswerInput())
				!$(this).val().trim() && $(this).closest('tr').remove();
		});
	});


	$(document).on('input change', Polls.answerInput, function(){
		var tr = $(this).closest('tr'),
			nextRow = tr.next('tr');

		if ($(this).val()){
			if (!nextRow.has(Polls.answerInput).length){
				var newRow = tr.clone();
				newRow.find('input').val('');
				tr.after(newRow);
			}
		} else if (tr !== nextRow){
			nextRow.find(Polls.answerInput).focus();
			tr.remove();
		}
	});

});

