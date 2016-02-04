<?php

require('config.php');

if ($_POST['token'] == $slack_token) { // Valid token, continue
	
	switch ($_POST['trigger_word']) {

		case "match:":

			// match: @troy and @liz win v @scott and @andrew
			// match: @scott win v @troy
			// Strip out up to 2 user names on each side of the "v"

			$teams = preg_split('/\svs?\.?\s/', $_POST['text']);

			// Get user names on each team
			preg_match_all('/@[a-z0-9]+/', $teams[0], $winning_team_players);
			preg_match_all('/@[a-z0-9]+/', $teams[1], $losing_team_players);

			$winner = preg_match('/w[io]n[s]?/', $teams[0]);

			if ($winner > 0 && count($winning_team_players[0]) > 0 && count($losing_team_players[0]) > 0) {

				$winning_team = trim(preg_replace('/w[io]n[s]?/', '', $teams[0]));
				$winning_team = trim(preg_replace('/'.$_POST['trigger_word'].'/', '', $winning_team));
				$losing_team = trim($teams[1]);

				$mysqli = new mysqli($database_host, $database_username, $database_password, $database_name);

				foreach($losing_team_players[0] as $player){
					// Update stats
					$mysqli->query('INSERT INTO user_stats (slack_user_name, games_played, losses) VALUES (\''.$player.'\', games_played+1, losses+1) ON DUPLICATE KEY UPDATE games_played=games_played + 1, losses=losses + 1');
					
					if ($mysqli->affected_rows <1) {
						echo json_encode(['text' => 'Hmm... there seems to be a database error. Sorry, the stats couldn\'t be saved.']);
						die();
					}

				}

				foreach($winning_team_players[0] as $key => $player){
					// Update stats
					$mysqli->query('INSERT INTO user_stats (slack_user_name, games_played, wins) VALUES (\''.$player.'\', games_played+1, wins+1) ON DUPLICATE KEY UPDATE games_played=games_played + 1, wins=wins + 1');
					
					if ($mysqli->affected_rows <1) {
						echo json_encode(['text' => 'Hmm... there seems to be a database error. Sorry, the stats couldn\'t be saved.']);
						die();
					}

				}

				echo json_encode(['text' => 'Congrats '.$winning_team.'! (Better luck next time '.$losing_team.')']);
				die();

			} else {

				echo json_encode(['text' => 'Please tell me who won! Like this: \''.$_POST['trigger_word'].' @player and @player2 win vs. @player3 and @player4\'']);
				die();

			}

			break;

		case "stats":

			$response = [
				"text" => "This doesn't do anything yet. Wanna help build it?"
			];
			echo json_encode($response);

			break;

		case "leaderboard":

			$response = [
				"text" => "This doesn't do anything yet. Wanna help build it?"
			];
			echo json_encode($response);

			break;

		case "test":

			$response = [

				"text" => "Works!"

			];
			echo json_encode($response);

			break;

	}

}else{
	die();	
}
