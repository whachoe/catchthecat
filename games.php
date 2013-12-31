<?php
class game
{
	const POLY_TYPE_WHITESPACE = 0;
	const POLY_TYPE_EMPTY = 1;
	const POLY_TYPE_CLICKED = 2;

	public $gameinfo = null;
    
    function __construct($id)
    {
        /*
        $record = array(
            "name" => '',
            "author" => '',
            "author_email" => '',
            "modified_date" => "date",
            "field" => '', // 2-dim array
            "cat_start_x" => 5,
            "cat_start_y" => 5,
            "start_blocks" => 4,
            "active" => "boolean"

            // Board theme
            "colors" => array(),

            // Game highscores
            "highscores" => array("name" => '', "score" => '', "date" => ''),
            
        );
         *
         */
        if ($id) {
            $this->gameinfo = $this->get($id);
        }
    }

    function get($id)
    {
        $m = new Mongo();
        $c = $m->catchthecat->games;
        $info = $c->findOne(array("_id" => new MongoId($id)));
        
        if (!isset($info['_id'])) {
            $info = $c->findOne(array("name" => "default"));
        }
        $info['_id'] = (string) $info['_id'];
        $this->gameinfo = $info;

		$this->randomGreenBlocks();

		// Save it in the session so we can later on check if the user has actually won
		$_SESSION['field'] = $this->fieldToArray();

        return $this->gameinfo;
    }

    public static function listgames()
    {
        $m = new Mongo();
        $c = $m->catchthecat->games;
        $cursor = $c->find(array());
        return $cursor;
    }

    static function save($gameinfo)
    {
        $m = new Mongo();
        $c = $m->catchthecat->games;
        $c->save($gameinfo);

        return $gameinfo;
    }

    function showminimap()
    {
?>
    <iframe id="minimapiframe_<?php echo $this->gameinfo['_id']?>" src="mini.svg" height="120" width="160" frameborder="0"></iframe>
    <script type="text/javascript">
        $().ready(function() {
            getGameinfo('<?php echo $this->gameinfo['_id']?>');
        });
    </script>
<?php
    }

	private function randomGreenBlocks()
	{
		$board = $this->gameinfo;

		$width = count($board['field'][0]) - 1;
        $height = count($board['field']) - 1;

        for ($i=0; $i<$board['start_blocks']; $i++) {
			do {
				$x = floor(rand(0, $width));
				$y = floor(rand(0, $height));
			} while ($board['field'][$y][$x] != self::POLY_TYPE_EMPTY || ($x == $board['cat_start_x'] && $y == $board['cat_start_y']));

			$this->gameinfo['field'][$y][$x] = self::POLY_TYPE_CLICKED;
        }
    }

	private function fieldToArray() {
		$array = array();
		foreach ($this->gameinfo['field'] as $line) {
			foreach ($line as $cell) {
				$array[] = $cell;
			}
		}

		return $array;
	}
}
