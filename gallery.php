<?php
    include_once 'games.php';
    include_once 'time.php';
    
    $cursor = game::listgames();
    $baseurl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
?>
<html>
    <head>
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jqModal.js"></script>

        <link rel="stylesheet" href="css/jqModal.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/catchthecat.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/gallery.css" type="text/css" media="screen" />
        <script type="text/javascript">
            function getGameinfo(id)
            {
                $.getJSON('ajax.php', {'action': 'loadgame', 'game_id': id}, function (data) {
                   gameinfo = data;
                   document.getElementById('minimapiframe_'+id).contentWindow.board.loadField(gameinfo);
                   document.getElementById('minimapiframe_'+id).contentWindow.Init();
                });
            }

        </script>
    </head>

<body>
<h1>Game Gallery</h1>
<p>Here you can browse through all the games that are made by players.</p>

<?php
while ($data = $cursor->getNext()) {
    $time = new TimeHelper();
    $game = new game($data['_id']);
    
    // var_dump($data);
?>
<div class="gamefiche">
    <div class="small_board">
        <?php $game->showminimap(); ?>
    </div>
    <div class="gamefiche_info">
        <div class="gamefiche_name"><?php echo $data['name'] ?></div>
        <div class="gamefiche_author">By <span><?php echo $data['author'] ?></span></div>
        <a class="playbutton" href="<?php echo $baseurl ?>/index.php?game_id=<?php echo $data['_id'] ?>">Play!</a>
        <div class="gamefiche_date">Added on <?php echo $time->niceShort($data['modified_date'], true); ?></div>
    </div>
    <div style="float: none; clear: both;"></div>
</div>
<?php
}
?>
</body>
</html>

