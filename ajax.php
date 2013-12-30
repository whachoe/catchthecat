<?php
    include "highscores.php";
    include "games.php";

	error_reporting(E_ERROR);

    function returnJson($data)
    {
        ob_start();
        header("Content-Type: application/json");
        echo json_encode($data);
        echo ob_get_clean();
        die;
    }

	$highscoresClass = new Highscores();

    switch ($_REQUEST['action']) {
        case 'checkhighscores':
            $score = (int) $_REQUEST['score'];
			$highscores = $highscoresClass->get_highscores($_REQUEST['game_id']);

            if (!$highscores || $highscores->count() < 11) {
                $result = array('highscore' => 1);
                returnJson($result);
            }

            $counter = 0;
            while ($highscores->hasNext() && $counter < 10) {
                $bla = $highscores->getNext();
                if ($score < $bla['score']) {
                    $result = array('highscore' => 1);
                    returnJson($result);
                }
                $counter++;
            }

            $result = array('highscore' => 0);
            returnJson($result);
            break;

        case 'logscore':
            if ($_REQUEST['win'] == 'true') {
				$highscoresClass->save_highscore($_REQUEST['game_id'], 'unknown', $_REQUEST['score']);
                returnJson('1');
            } else {
				$highscoresClass->save_lost_game($_REQUEST['game_id'], $_REQUEST['score']);
                returnJson('1');
            }
            break;

        case 'loadgame':
            $game = new game($_REQUEST['game_id']);
            returnJson($game->gameinfo);
            break;

        case 'saveboard':
//            file_put_contents('/tmp/bla', $_SERVER['HTTP_REFERER']."\n", FILE_APPEND);
            
            $data = json_decode($_POST['board']);
            
            // Extra datafields
            $data->modified_date = time();
            $data->active = true;

            // Tests
                // Does it exist already?
                
            if ($gameinfo = game::save($data)) {
                returnJson(array('success' => true, "id" => (string) $gameinfo->_id));
            }
            else
                returnJson(array('success' => false));
                
            break;
    }
?>