var POLY_TYPE_WHITESPACE = 0;
var POLY_TYPE_EMPTY = 1;
var POLY_TYPE_CLICKED = 2;

var board = {
    "field" : [[0,0,0,1,1,1,1,1,0,0,0],
        [0,0,1,1,1,1,1,1,0,0,0],
        [0,0,1,1,1,1,1,1,1,0,0],
        [0,1,1,1,1,1,1,1,1,0,0],
        [0,1,1,1,1,1,1,1,1,1,0],
        [1,1,1,1,1,1,1,1,1,1,0],
        [0,1,1,1,1,1,1,1,1,1,0],
        [0,1,1,1,1,1,1,1,1,0,0],
        [0,0,1,1,1,1,1,1,1,0,0],
        [0,0,1,1,1,1,1,1,0,0,0],
        [0,0,0,1,1,1,1,1,0,0,0]],
    "cat_start_x" : 5,
    "cat_start_y" : 5,
    "start_blocks": 4,
    "colors" : null,

    // Methods
    "drawField" : function () {
        this.width = this.field[0].length;
        this.height = this.field.length;

        for(i=0; i < this.height; i++) {
            for (j=0;j<this.width; j++) {
                coords = get_coords(j,i);
                cx = coords.x;
                cy = coords.y;
//                    console.log("X: "+j+" Y: "+i + " CX: "+cx+" CY: "+cy);
                if (this.field[i][j] != POLY_TYPE_WHITESPACE) {
                    poly = DrawPoly(cx, cy, 6, 20, 'y', "poly_clicked(this)", "hex_"+j+'_'+i);
                    if (this.field[i][j] == POLY_TYPE_CLICKED) {
                        board.polyChangeType(poly, POLY_TYPE_CLICKED);
                    }
                } else {
                    poly = DrawPoly(cx, cy, 6, 20, 'y', "", "hex_"+j+'_'+i);
                    board.polyChangeType(poly,POLY_TYPE_WHITESPACE);
                }
            }
        }
    },

    "polyChangeType" : function(poly,newtype) {
        poly.setAttribute('poly_type', newtype);
        poly.setAttribute('fill', board.colors[newtype]);

        switch (newtype) {
            case POLY_TYPE_WHITESPACE:
                poly.setAttribute('stroke', board.colors[newtype]);
                break;
            default:
                break;
        }

        return poly;
    },

    "initColors": function() {
        board.colors = new Array();
        board.colors[POLY_TYPE_WHITESPACE] = "white";
        board.colors[POLY_TYPE_EMPTY] = "cornflowerblue";
        board.colors[POLY_TYPE_CLICKED] = "green";
    },

    "loadField" : function(boarddata) {
        board._id = boarddata._id;
        board.field = boarddata.field;
        board.cat_start_x = boarddata.cat_start_x;
        board.cat_start_y = boarddata.cat_start_y;
        board.start_blocks = boarddata.start_blocks;
        board.colors = boarddata.colors;
        board.name = boarddata.name;
        board.author = boarddata.author;
        board.modified_date = boarddata.modified_date;
    }
}

/**
 * Various strategies to choose best spot to move the cat to
 **/
var strategies = new Array(

    // Choose a free cell at random
    function (really_possible)
    {
        return really_possible[Math.floor(Math.random()*really_possible.length)];
    },

    // strategy_shortest_to_edge: typical min-max 2d routine
    function (coords)
    {
        coords = shuffle(coords);

        var min_distance_coord = null;
        var min_distance = Math.max(board.width,board.height);

        for (i=0; i<coords.length; i++) {
            var x = coords[i][0];
            var y = coords[i][1];
            var x_right = board.width - x -1;
            var y_bottom = board.height - y -1;
            var dist = Math.min(x,y,x_right,y_bottom);
            var vector = [x-prev_cat_coords[0], y-prev_cat_coords[1]];

            // Favor the previous direction
            if (vector[0] == prev_vector[0] && vector[1] == prev_vector[1])
                dist--;

            // Penalty on previous spot: do not go back readily.
            if (coords == prev_cat_coords)
                dist = dist + 2;

            if (dist < min_distance) {

                min_distance = dist;
                min_distance_coord = coords[i];
            }
        }

        if (min_distance_coord) {
            return min_distance_coord;
        } else {
            return strategies[0](coords); // use random algo
        }
    },

    // shortest_open_path_to_edge: a better version of the previous routine
    function (coords)
    {
        // let's shuffle them first to make it a bit more natural'
        // coords = shuffle(coords);

        var min_distance_coord = null;
        var min_distance = Math.max(board.width,board.height);
        var cat_i = cat_svg.getAttribute('i');
        var cat_j = cat_svg.getAttribute('j');
        var path = new Array();

        for (i=0; i<coords.length; i++) {
            x = parseInt(coords[i][0]);
            y = parseInt(coords[i][1]);

            for (j=0; j<6; j++) {
                tmp_path = new Array();
                path[j] = findShortestPath(x,y,j);
                if (path[j] != null) {
                    if (path[j].length < min_distance) {
                        min_distance = path[j].length;
                        min_distance_coord = path[j][0];
                    }
                }
            }
        }
        if (min_distance_coord)
            return min_distance_coord;
        else
            return strategies[0](coords); // use the random algo
    }
);


// Global variables
var won = false;
var move_count = 0;
var prev_cat_coords = [board.cat_start_x, board.cat_start_y];
var prev_vector = [0,0];
var tmp_path = [];
var strategy_index = 1;


// document.onload-routine
function Init()
{
    SVGDocument = document.getElementById('catchthecatsvg').ownerDocument;
    SVGRoot = SVGDocument.documentElement;

//    board.randomGreenBlocks();
    board.drawField();

    drawCat(board.cat_start_x,board.cat_start_y, true);
};

// shuffle an array
function shuffle (v)
{
    for(var j, x, i = v.length; i; j = parseInt(Math.random() * i), x = v[--i], v[i] = v[j], v[j] = x);
    return v;
};

// Translates coords from cellnumbers to real svg-coords for the cat-object
function get_coords_cat(i,j)
{
    return {
        "x": 18 + 40*i +(j % 2 == 1 ? 20:0),
        "y": 20 + 35*j
    };
}

// Translates coords from cellnumbers to real svg-coords for the hex's
function get_coords(x,y)
{
    return {
        "x": 30 + 40*x +(y % 2 == 1 ? 20:0),
        "y": 35 + 35*y
    };
}

function cot(n)
{
    return 1 / Math.tan(n);
};

function sec(n)
{
    return 1 / Math.cos(n);
};

// Draws a polygon: used for the hexagons
function DrawPoly(cx, cy, sides, edge, orient, onclick, id)
{
    var inradius = (edge / 2) * cot( Math.PI / sides );
    var circumradius = inradius * sec( Math.PI / sides );

    var points = '';
    for (var s = 0; sides >= s; s++)
    {
        var angle = (2.0 * Math.PI * s / sides);
        if (orient == 'y')
            angle += 11;
        var x = ( circumradius * Math.cos(angle) ) + cx;
        var y = ( circumradius * Math.sin(angle) ) + cy;

        points += x + ',' + y + ' ';
    }

    var poly = SVGDocument.createElementNS(svgns, 'polygon');
    poly.setAttributeNS(null,'id', id);
    poly.setAttributeNS(null,'points', points);
    poly.setAttributeNS(null,'stroke',  'blue');
    poly.setAttributeNS(null,'onclick', onclick);
    poly.setAttributeNS(null,'fill',  'cornflowerblue');
    poly.setAttributeNS(null,'poly_type', POLY_TYPE_EMPTY);

    board.polyChangeType(poly, POLY_TYPE_EMPTY);

    SVGRoot.appendChild(poly);

    return poly;
};

// Put the cat somewhere on the board
function drawCat(i,j,scale)
{
    coords = get_coords_cat(i,j);

    cat_svg = document.getElementById('cat_svg');
    if (scale) {
        cat = document.getElementById('cat');
        cat.setAttribute('transform','scale(0.04)');
    }

    cat_svg.setAttribute('i', i);
    cat_svg.setAttribute('j', j);
    cat_svg.setAttribute('x', coords.x);
    cat_svg.setAttribute('y', coords.y);

    MoveToTop(cat_svg);
};

// Gets called whenever the user clicks a field
function poly_clicked(poly)
{
    // Only do this stuff when the field is still empty
    if (poly.getAttribute('poly_type') == POLY_TYPE_EMPTY) {
        cat_i = cat_svg.getAttribute('i');
        cat_j = cat_svg.getAttribute('j');

        id = poly.getAttribute('id').replace('hex_','');
        coords = id.split('_');
        x = coords[0];
        y = coords[1];

        if (x == cat_i && y == cat_j) {
            //            console.log("Wrong move (cat is here): "+x+','+y);
        } else {
            board.polyChangeType(poly, POLY_TYPE_CLICKED);
            board.field[y][x] = POLY_TYPE_CLICKED;
            move_count++;
            //            console.log(board.field);

            // Move the cat
            moveCat();
        }
    }
}

// Return all the coords of fields surrounding the given coordinate
function getSurroundingCoords(i,j)
{
    var possible = new Array();
    i = parseInt(i);
    j = parseInt(j);
    if (j % 2 == 0) {
        possible = [[i-1,j],[i-1,j-1],[i,j-1],[i+1,j],[i,j+1],[i-1,j+1]];
    } else {
        possible=[[i-1,j],[i,j-1],[i+1,j-1],[i+1,j],[i+1,j+1],[i,j+1]];
    }

    return possible;
}

// returns an array of [i,j] coords around the given coord that is still empty
function getEmptySpot(i,j)
{
    var possible = getSurroundingCoords(i,j);
    var really_possible = new Array();

    // First filtering out all the impossible moves
    for(c=0; c<6; c++) {
        x = possible[c][0];
        y = possible[c][1];

        // Filtering out the illegal moves first
        if (x <0 || x >= board.width || y < 0 || y >= board.height) {
            continue;
        }

        // this field is already occupied
        if (board.field[y][x] > POLY_TYPE_EMPTY) {
            continue;
        }

        // We can't move into whitespace'
        if (board.field[y][x] == POLY_TYPE_WHITESPACE) {
            continue;
        }

        // If we can reach the edge in this turn, DO IT!
        var surround = getSurroundingCoords(x,y);
        for (c2=0; c2 < surround.length; c2++) {
            x2 = surround[c2][0];
            y2 = surround[c2][1];
            if (board.field[y2] == undefined || board.field[y2][x2] == undefined || board.field[y2][x2] == POLY_TYPE_WHITESPACE) {
                won = true;
                return possible[c];
            }
        }

        really_possible.push(possible[c]);

        // DEBUG: make all surrounding hex's red'
//        id = "hex_"+x+'_'+y;
//        SVGDocument.getElementById(id).setAttribute('fill', 'red');
    }

    // Choose one of the strategies and pick a coord
    if (really_possible.length >0) {
        return strategies[strategy_index](really_possible);
    }

    return false;
}

// The not-so-smart function that moves the cat around to try to escape the field
function moveCat()
{
    cat_i = cat_svg.getAttribute('i');
    cat_j = cat_svg.getAttribute('j');
    new_coords = getEmptySpot(cat_i, cat_j);
    if (new_coords) {
//            console.log("Moving cat to: "+new_coords[0]+','+new_coords[1]);
        drawCat(new_coords[0], new_coords[1]);
        prev_vector = [new_coords[0] - cat_i, new_coords[1] - cat_j];
        prev_cat_coords = [cat_i,cat_j];
    } else {
        parent.checkHighscores(move_count);
        alert('Congrats! You won in '+move_count+' turns.');
    }
    if (won) {
        alert('You lose! Neh neh neh');
        parent.registerGame(move_count);
    }
}

// Makes an SVG-element jump to the top: z-index fix
function MoveToTop(svgNode)
{
    svgNode.parentNode.appendChild( svgNode );
}

// Puts an SVG-element behind the other elements: z-index fix
function MoveToBottom(svgNode)
{
    svgNode.parentNode.insertBefore( svgNode, svgNode.parentNode.firstChild );
}

// Maps out a path in a certain direction, recursively
function findShortestPath(x,y,dir)
{
    tmp_path.push([x,y]);

    // If we're on the border. STOP'
    if (board.field[y][x] == POLY_TYPE_WHITESPACE || x == 0 || x == board.width-1 || y == 0 || y == board.height-1) {
        return tmp_path;
    } else {
        var surround = getSurroundingCoords(x,y);

        if (board.field[surround[dir][1]] != undefined && board.field[surround[dir][1]][surround[dir][0]] != undefined
            && board.field[surround[dir][1]][surround[dir][0]] == POLY_TYPE_EMPTY) {
            return findShortestPath(surround[dir][0], surround[dir][1], dir);
        } else {
            tmp_path = [];
            return tmp_path;
        }
    }
}
