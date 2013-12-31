<?php
    $baseurl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Editor for Catch The Cat game</title>
                <script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
                <script type="text/javascript" src="js/jqModal.js"></script>
                <script type="text/javascript" src="js/json.js"></script>
                
                <link rel="stylesheet" href="css/jqModal.css" type="text/css" media="screen" />
				<link rel="stylesheet" href="css/catchthecat.css" type="text/css" media="screen" />
                <link rel="stylesheet" href="css/editor.css" type="text/css" media="screen" />

                <script type="text/javascript">
                    var board = null; // we are gonna save the board into this var
                    
                    function getfieldtype()
                    {
                        return parseInt($("input:radio[name=poly_type]:checked").val());
                    }

                    function getfieldcolor(fieldname)
                    {
                        color = $('#'+fieldname).val();
                        if (color) {
                            return color;
                        } else {
                            return false;
                        }
                    }

                    // Called in onchange from cat-position fields
                    function drawCat()
                    {
                        var x = $('#start_cat_x').val();
                        var y = $('#start_cat_y').val();
                        if (!isNaN(x) && !isNaN(y)) {
                            document.getElementById('editorframe').contentWindow.board.cat_start_x = x;
                            document.getElementById('editorframe').contentWindow.board.cat_start_y = y;
                            document.getElementById('editorframe').contentWindow.drawCat(x,y);
                        }
                    }

                    function save()
                    {
                        var topost = document.getElementById('editorframe').contentWindow.board;
                        topost.name     = $('#board_name').val();
                        topost.author   = $('#board_author').val();
                        topost.email    = $('#board_email').val();

                        // Validation
                            // Does cat sit on empty block?
                            if (topost.field[topost.cat_start_y][topost.cat_start_x] != 1) {
                                alert('Cat needs to start on an empty field!');
                                return false;
                            }

                            // Is the cat on an edge?
                            var surround = document.getElementById('editorframe').contentWindow.getSurroundingCoords(topost.cat_start_x,topost.cat_start_y);
                            for (i=0; i<surround.length; i++) {
                                if (topost.field[surround[i][1]][surround[i][0]] == 0) {
                                    alert('The cat cannot sit on the edge!');
                                    return false;
                                }
                            }
                            // Is name filled in?
                            if ($('#board_name').val().length < 4) {
                                alert('Board name has to be at least 4 characters!');
                                return false;
                            }

                            // Illegal board name?
                            if ($('#board_name').val() == 'default') {
                                alert('Board name illegal! Please choose another name.');
                                return false;
                            }
                            
                        $.post('ajax.php', {action: "saveboard", "board": $.toJSON(topost)}, function (data) {
                            data = $.parseJSON(data);
                            
                            if (data.success) {
                                id = data.id;
                                $('#game_saved_success_link').attr('href', '<?php echo $baseurl ?>/index.php?game_id='+id);
                                
                                // say all went well in a nice popup thingy
                                $("#game_saved_success").jqmShow();
                            } else {
                                // Manage the errors
                                
                            }
                        });
                        return true;
                    }

                    $().ready(function() {
                       // popups
                       $('#game_saved_success').jqm();
                       
                       // Event handling
                       $('#savebutton').click(save);
                       $('#start_cat_x').change(drawCat);
                       $('#start_cat_y').change(drawCat);
                       $('input.color_field').change(function(e) {
                           var poly_type = $(e.target).attr('poly_type');
                           var radiolabel_id = 'radio'+poly_type+'_label';
                           var poly_color = $(e.target).val();
                           
                           eval('document.getElementById("editorframe").contentWindow.board.colors[' + poly_type + '] = "' + poly_color +'"');
                           document.getElementById("editorframe").contentWindow.board.redrawField();
                           $('#'+radiolabel_id).css('background-color',poly_color);
                       });
                       $('#start_blocks').change(function(e) {
                           var amount = $(e.target).val();
                           document.getElementById('editorframe').contentWindow.board.start_blocks = amount;
                       });
                    });
                </script>
    </head>
    <body>
		<?php include_once 'menu.php'; ?>

        <div id="game_saved_success" class="jqmWindow" style="width: 300px; height: 200px;">
            <span class="allgood"> Game saved succesfully!</span> <br/>
            You can play it by clicking <a id="game_saved_success_link" href="">this link</a>
            <br/><br/>
            <a href="#" class="jqmClose" onclick="$('#highscoresform').hide();">close</a>
        </div>

        <div id="game_saved_error" class="jqmWindow" style="width: 300px; height: 200px;">
            <Span class="errormsg"> Error saving game</span>
            
        </div>
        <div id="mainscreen">
            <fieldset id="instructions">
                <legend>Instructions</legend>
                <ul>
                    <li>The X-Y coordinate system starts at the upper left corner. So coordinate (4,5) means: start from upper left corner and move 4 right and 5 down.</li>
                </ul>
            </fieldset>
            <br<br/>
            <form action="" method="post" onsubmit="return false;">
                <input type="hidden" id="formboard" name="board" value="" />
                <fieldset id="gameparamsdiv">
                    <legend>Game Parameters</legend>
                    <label>Board Name<label><input id="board_name" type="text" name="name" size="36"/><br/>
                    <label>Author</label><input id="board_author" type="text" name="author" size="36" /><br/>
                    <label>E-Mail</label><input id="board_email" type="text" name="email" size="36" /><br/>
                    <label>Start Position Cat (x,y)</label><input type="text" id="start_cat_x" name="start_cat_x" size="3" />&nbsp;<input type="text" id="start_cat_y" name="start_cat_y" size="3" /><br/>
                    <label>Color Whitespace</label><input type="text" class="color_field" id="poly_type_whitespace_color" name="poly_type_whitespace_color" poly_type="0" value="white"/><br/>
                    <label>Color Empty Field</label><input type="text" class="color_field"  id="poly_type_empty_color" name="poly_type_empty_color" size="12" poly_type="1" value="cornflowerblue" /><br/>
                    <label>Color Blocked Field</label><input type="text" class="color_field"  id="poly_type_clicked_color" name="poly_type_clicked_color" size="12" poly_type="2" value="green" /><br/>
                    <label>Amount of random blocks given at start of game</label><input type="text" id="start_blocks" name="start_blocks" size="3" />
                </fieldset>

                <fieldset id="drawprimitives">
                    <legend>Drawing</legend>
                    <input type="radio" id="radio0" class="poly_type" value="0" name="poly_type" /><label id="radio0_label" for="radio0">Whitespace</label>
                    <input type="radio" id="radio1" class="poly_type" value="1" name="poly_type" CHECKED /><label id="radio1_label" for="radio1">Empty field</label>
                    <input type="radio" id="radio2" class="poly_type" value="2" name="poly_type" /><label id="radio2_label" for="radio2">Blocked field</label>

                <iframe id="editorframe" src="editor.svg" height="600" width="700" frameborder="0"></iframe>
                </fieldset>
                <br/>
                <input type="button" id="savebutton" name="save" value="Save" />
            </form>
			<br/>
        <div style="clear: both; float: none"></div>

		</div>

		<?php include_once 'footer.php'; ?>

    </body>
</html>
