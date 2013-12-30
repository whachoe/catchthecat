<?php
class Highscores {
	private $db;

	function __construct() {
		$mongo = new Mongo();
		$this->db = $mongo->selectDB('catchthecat');
	}

    function get_highscores($id)
    {
        return $this->db->highscores->find(array('game_id' => $id))->sort(array("score" => 1, "date" => 1))->limit(10);
    }

    function save_highscore($id, $name, $score)
    {
        $newscore = array(
                "name" => $name,
                "score" => intval($score),
                "date" => time(),
                "game_id" => $id
            );
        $this->db->highscores->insert($newscore);
    }

    function save_lost_game($id, $score)
    {
        $c = $this->db->lostgames;
        
        $record = array(
            "score" => $score,
            "date"  => time(),
            'game_id' => $id
        );
        $c->insert($record);
    }

	function get_total_wins($gameid) {
		return $this->db->highscores->count(array("game_id" => $gameid));
	}

	function get_total_loss($gameid) {
		return $this->db->lostgames->count(array("game_id" => $gameid));
	}
}