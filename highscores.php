<?php
class Highscores {
	private $db;

	function __construct() {
		$mongo = new Mongo();
		$this->db = $mongo->selectDB('catchthecat');
	}

    function get_highscores($id)
    {
        return $this->db->highscores->find(array('game_id' => $id))->sort(array("score" => 1, "date" => -1))->limit(10);
    }

    function save_highscore($id, $name, $score, $field)
    {
		if ($this->beSmarterThanTheScriptKiddies($field, $_SESSION['field'], $score)) {
			$newscore = array(
				"name" => $name,
				"score" => intval($score),
				"date" => time(),
				"game_id" => $id
			);
			$this->db->highscores->insert($newscore);

			return true;
		}

		return false;
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

	private function beSmarterThanTheScriptKiddies($posted_field, $str_field, $moves) {
		$posted_field_array = explode(',', $posted_field);

		// Length of field different? ERROR!
		if (count($posted_field_array) != count($str_field)) return false;

		// Count the fields that are different in both arrays:
		$differences = 0;
		for ($i=0; $i < count($posted_field_array); $i++) {
			if ($posted_field_array[$i] != $str_field[$i])
				$differences++;
		}

		if ($differences == $moves) return true;

		return false;
	}
}