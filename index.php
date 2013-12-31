<?php
   include_once 'highscores.php';
    include_once 'games.php';
    include_once 'time.php';

	error_reporting(E_ERROR);
	session_start();

    if ($_POST['action'] == "sendhighscore") {
        if (is_numeric($_POST['score']) && $_POST['score'] > 0) {
            if ($_POST['highscore_name']) {
				$highscoresClass = new Highscores();

                $highscoresClass->save_highscore($_POST['game_id'], $_POST['highscore_name'], $_POST['score'], $_POST['field']);
                header("Location: index.php?game_id=".$_POST['game_id']);
                die;
            }
        }
    }

    // If we get a game-id, let's try to load that game from the db
    $gamename = "default";
    if ($_REQUEST['game_id']) {
        $gamename = $_REQUEST['game_id'];
    }
    $game = new Game($gamename);
    $gameid = $game->gameinfo['_id'];
    $time = new TimeHelper();
    
    // Highscores
	$highscoresClass = new Highscores();
	$highscores 	= $highscoresClass->get_highscores($gameid);
    $total_wins 	= $highscoresClass->get_total_wins($gameid);
    $total_lost 	= $highscoresClass->get_total_loss($gameid);
    $total_scores 	= $total_wins + $total_lost;
?>
<html>
    <head>
        <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
        <script type="text/javascript" src="js/jqModal.js"></script>

        <link rel="stylesheet" href="css/jqModal.css" type="text/css" media="screen" />
		<link rel="stylesheet" href="css/TableCSSCode.css" type="text/css" media="screen" />
        <link rel="stylesheet" href="css/catchthecat.css" type="text/css" media="screen" />

    </head>
    
    <body>
        <script type="text/javascript">
            var highscore = 0;
            var gameinfo = null;

            function getGameinfo()
            {
                $.getJSON('ajax.php', {'action': 'loadgame', 'game_id': '<?php echo $gameid?>'}, function (data) {
                   gameinfo = data;
                   document.getElementById('iframecat').contentWindow.board.loadField(gameinfo);
                   document.getElementById('iframecat').contentWindow.Init();
                });
            }

            function checkHighscores(score)
            {
                $.getJSON('ajax.php', {'action': 'checkhighscores', 'score': score, 'game_id': '<?php echo $gameid ?>'}, function (data) {
                    if (data.highscore != undefined && data.highscore == 1) {
                        $("#highscore_hidden").val(score);
                        $("#highscore_game_id").val('<?php echo $gameid ?>');
						$("#highscore_field").val(document.getElementById('iframecat').contentWindow.board.field);
                        $("#highscoresform").jqmShow();
                    } else {
                        // just log the score as an anonymous user
						var payload = {
							'action': 'logscore',
							'score': score,
							'win': true,
							'game_id': '<?php echo $gameid ?>',
							'field': document.getElementById('iframecat').contentWindow.board.field
						}
                        $.getJSON('ajax.php', payload, function (data2) {
                            // Reload the page
                            window.location.reload();
                        });
                    }
                });
            }

            function registerGame(score)
            {
                $.getJSON('ajax.php', {'action': 'logscore', 'score': score, 'win': false, 'game_id': '<?php echo $gameid ?>'}, function (data) {
                    window.location.reload();
                });
            }


            $().ready(function() {
                // highscore form popup
                $('#highscoresform').jqm({});
				document.getElementById('iframecat').addEventListener("load", getGameinfo);
            });
        </script>
    
    <div id="highscoresform" class="jqmWindow" style="width: 300px; height: 200px;">
        <form action="" method="POST">
            <input type="hidden" name="action" value="sendhighscore" />
            <input id="highscore_hidden" type="hidden" name="score" value="0" />
            <input id="highscore_game_id" name="game_id" type="hidden" value="" />
			<input id="highscore_field" name="field" type="hidden" value="" />
            <h3>You got a highscore!</h3>
            <label for="highscore_name">Your Name:</label>
            <input type="text" id="highscore_name" name="highscore_name" value="" size="32"/>
            <br/><br/>
            <input type="submit" value="Go!" /> &nbsp;
            <a href="#" class="jqmClose" onclick="$('#highscoresform').hide();">Nah, I don't care about highscores</a>
        </form>
    </div>

	<h3 class="logo">Catch The Cat</h3>
	<ul id="menu_wrap" class="l_Blue">
		<li class="button"><a href="gallery.php">Game Gallery</a></li>
		<li class="button"><a href="editor.php">Make your Own</a></li>
	</ul>
	<div style="clear: both"></div>

	<div id="iframediv" style="float: left; margin-left: 20px; height: 600px; width: 800px">

		<span id="description">
			The object of the game is to surround the cat with green fields so it can't escape to the edge of the board.
		</span>

		<iframe id="iframecat" src="catchthecat.svg" width="800" height="600" frameborder="0"></iframe>
    </div>

    <div id="highscoreslist">
        <div id="gameinfo">
            <span class="gamename"><?php echo $game->gameinfo['name']?></span><br/>
            <span class="author_name">Made by <i><?php echo $game->gameinfo['author']?></i></span>
			on <span class="modified_date"><i><?php echo $time->niceShort($game->gameinfo['modified_date']); ?></i> </span>
        </div>

		<div class="CSSTableGenerator" >
			<table id="highscoretable" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="3">High Scores</td>
				</tr>
			<?php
				$counter = 1;
				foreach ($highscores as $score) {
					$oddeven = $counter % 2 ? 'odd' : 'even';
					echo "<tr class=\"$oddeven\">\n";
					echo '	<td class="highscorelist_counter">'.$counter.'</td>';
					echo '	<td class="highscorelist_name">'.$score['name'].'</td>';
					echo '	<td class="highscorelist_score">'.$score['score'].'</td>';
					echo "</tr>\n";
					$counter++;
				}
				for ($i=$counter; $i<11; $i++) {
					$oddeven = $i % 2 ? 'odd' : 'even';
					echo "<tr class=\"$oddeven\">\n";
					echo '<td class="highscorelist_counter">'.$i.'</td>';
					echo '<td class="highscorelist_name">&nbsp;</td>';
					echo '<td class="highscorelist_score">&nbsp;</td>';
					echo "</tr>\n";
				}
			?>
			</table>
		</div>

        <p style="font-size: 10px">
            This game has been played <strong> <?php echo $total_scores ?> </strong> times. <br/>
            <strong><?php echo $total_wins ?></strong> times, people have won. <strong><br/>
            <?php echo $total_lost?></strong> times, the cat got away.
        </p>
        
        <div id="controls" style="height: 40px; margin-top: 20px;">
            <a href="javascript:window.location.reload()">Reset</a>
        </div>

    </div>
    <div style="clear: both; float: none"></div>

    
    <div id="footer">
        Made by <a class="whiteurl" href="mailto: whachoe+catchthecat@gmail.com">Jo Giraerts</a> (c) 2010.<br/>
        <small>Based on <a class="whiteurl" href="http://www.members.shaw.ca/gf3/circle-cat/zcircle-the-cat.swf">this</a></small>.
    </div>
    </body>
</html>    
