<?php
class game
{
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
}
