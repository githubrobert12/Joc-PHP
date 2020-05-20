<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Joc PHP</title>
    </head>
    <body>
        <div style="-moz-user-select: none; -webkit-user-select:none; -ms-user-select:none; user-select:none; -moz-user-drag:none; -webkit-user-drag:none; -ms-user-drag:none; user-drag: none;" unselectable="on">
            <p></p>
            <?php
            new Game();
            ?>
        </div>
    </body>
</html>

<?php

class Game {
	
    var $position;                       
    var $board        = '---------';    
    var $debug        = false;           
    var $valid_char   = ['x', 'o', '-']; 
    var $invalid_char;                 
    var $grid_size    = 3;               
    var $winning_line = [];              
    var $win_lines    = [];                 

  

    function __construct() {
     
        if (isset($_GET['size'])) {
           
            $this->grid_size = trim($_GET['size']);
            $this->board     = str_repeat('-', pow($this->grid_size, 2));
        }

      
        if (isset($_GET['board'])) {
   
            if (strlen(trim($_GET['board'])) == 0) {
      
                $this->board = str_repeat('-', pow($this->grid_size, 2));
            } else {
     
                $this->board = trim(strtolower($_GET['board']));
            }
        }

        $this->position = str_split($this->board); 

     

      
        $this->generate_win_lines();

     
        $this->game_check();
    }

 
    function generate_win_lines() {
        $this->win_lines = []; 
      
        for ($a = 0; $a < $this->grid_size; $a++) {
            $horizontal = []; 
            $vertical   = []; 
           
            for ($b = 0; $b < $this->grid_size; $b++) {
   
                $horizontal[] = $this->grid_size * $a + $b;
            
                $vertical[]   = $this->grid_size * $b + $a;
            }
        
            $this->win_lines['Horizontal']['Row ' . ($a + 1)]  = $horizontal;
        
            $this->win_lines['Vertical']['Column ' . ($a + 1)] = $vertical;
       
            $this->win_lines['Diagonal']['backslash'][]        = $this->grid_size * $a + $a;
          
            $this->win_lines['Diagonal']['forward slash'][]    = $this->grid_size * ($a + 1) - ($a + 1);
        }
        
        if ($this->debug) {
            
            echo '<br /><table border = "1" style="border-collapse: collapse">';
            
            echo '<caption>Toate combinatiile castigatoare</caption>';
          
            echo '<thead><tr>';
            foreach ($this->win_lines as $line_type => $lines) {
                echo '<th>' . $line_type . '</th>';
            }
            echo '</tr></thead>'; 
            echo '<tr>'; 
            foreach ($this->win_lines as $line_type) {
                echo '<td><div style="padding:8px;">';
                foreach ($line_type as $line => $pos) {
                    echo $line . ': [' . implode(',', $pos) . ']<br />';
                }
                echo '</div></td>';
            }
            echo '</tr>'; 
            echo '</table>'; 
        }
    }

    /**
     Mesaje pentru exceptii intalnite
     */
    function game_check() {
        $this->invalid_char = array_diff($this->position, $this->valid_char);
        if ($this->grid_size % 2 == 0 || $this->grid_size < 3 || $this->grid_size > 15) {
            $this->game_message('invalid-size');
        } else if (count($this->invalid_char, COUNT_RECURSIVE) > 0) {
            $this->game_message('invalid-character');
        } else if (strlen($this->board) <> pow($this->grid_size, 2)) {
            $this->game_message('invalid-board');
        } else if ($this->board == str_repeat('-', pow($this->grid_size, 2))) {
            $this->game_play(true);
            $this->game_message('new-game');
        } else if (substr_count($this->board, 'x') - substr_count($this->board, 'o') > 1) {
            $this->game_play(false);
            $this->game_message('too-many-x');
        } else if (substr_count($this->board, 'o') - substr_count($this->board, 'x') > 0) {
            $this->game_play(false);
            $this->game_message('too-many-o');
        } else if ($this->win_check('x')) {
            $this->game_play(false);
            $this->game_message('x-win');
        } else if ($this->win_check('o')) {
            
            $this->game_play(false);
            $this->game_message('o-win');
        } else if (stristr($this->board, '-') === FALSE) {
            
            $this->game_play(false);
            $this->game_message('tie-game');
        } else {
            $this->pick_move();
            if ($this->win_check('o')) {
                $this->game_play(false);
                $this->game_message('o-win');
            } else {
                $this->game_play(true);
                $this->game_message('ongoing-game');
            }
        }
    }

    /**
Afiseaza tabela de joc
     */
    function game_play($link) {
        echo '<br />'; 
        if ($this->grid_size > 3) {
         
        }
        echo '<font face = "courier" size = "5">';
        echo '<table cols = "' . ($this->debug ? $this->grid_size + 2 : $this->grid_size) . '" border = "1" style = "font-weight:bold; border-collapse: collapse">';
        if ($this->debug) {
            echo '<thead><tr><th></th>';
            for ($col = 1; $col <= $this->grid_size; $col++) {
                echo '<th style="padding: 5px;"> Column ' . $col . '</th>';
            }
            echo '<th></th></tr></thead>';
            echo '<tfoot><tr><th></th>';
            for ($col = 1; $col <= $this->grid_size; $col++) {
                echo '<th> Column ' . $col . '</th>';
            }
            echo '<th></th></tr></tfoot>';
        }
        echo '<tbody><tr>';
        $row = 1;   
        if ($this->debug) {
            echo '<th style="padding: 5px;">Row ' . $row . '</th>';
        }
        for ($pos = 0; $pos < pow($this->grid_size, 2); $pos++) {
            if ($link) {
                echo $this->show_cell($pos);
            } else {
				
					 echo '<td style="text-align:center;' . (in_array($pos, $this->winning_line[0]) ? ' background-color: #90EE90;' : ' opacity: 0.5;' ) . '"><div style="padding: 1em;">' . $this->position[$pos] . ($this->debug ? ('<br /><span style="font-size:66%;">' . $pos . ':(' . $row . ',' . (($pos % $this->grid_size) + 1) . ')</span>') : '') . '</div></td>';
					
			}
            if (($pos + 1) % $this->grid_size == 0) {
                if ($this->debug) {
                    echo '<th style="padding: 5px;">Row ' . $row++ . '</th>';
                }
                if (($pos + 1) != pow($this->grid_size, 2)) {
                    echo '</tr><tr>';
                    if ($this->debug) {
                        echo '<th style="padding: 5px;">Row ' . $row . '</th>';
                    }
                }
            }
        }
        echo '</tr></tbody>';
        echo '</table>';
        echo '</font>';
        echo '<br /><hr />';
    }

    /**
    Genereaza cod HTML pentru celule
     */
    function show_cell($which) {
        $token = $this->position[$which];
        if ($token <> '-') {
            $player_board = str_split($this->board);  
            return '<td style="text-align:center;' . ($token != $player_board[$which] ? ' background-color: #FFA500;' : '' ) . '"><div style="padding: 1em;">' . $token . ($this->debug ? ('<br /><span style="font-size:66%;">' . $which . ':(' . ((int) ($which / $this->grid_size) + 1) . ',' . (($which % $this->grid_size) + 1) . ')</span>') : '') . '</div></td>';
        }
        $this->newposition         = $this->position;               
        $this->newposition[$which] = 'x';                          
        $move                      = implode($this->newposition);  
        $link                      = '?size=' . $this->grid_size . '&board=' . $move . ($this->debug ? '&debug' : '');             
        return '<td style="text-align:center;"><a href = "' . $link . '" style = "text-decoration: none;"><div style="padding: 1em;">-' . ($this->debug ? ('<br /><span style="font-size:66%;">' . $which . ':(' . ((int) ($which / $this->grid_size) + 1) . ',' . (($which % $this->grid_size) + 1) . ')</span>') : '') . '</div></a></td>';
    }

    /**
     Logica
     */
    function pick_move() {
        echo ($this->debug ? '<br />> The AI is making its move...<br />' : '');
        $ai_win_move = $this->win_check('o');
        if ($ai_win_move != -1) {
            $this->position[$ai_win_move] = 'o';
        } else {
            $player_win_move = $this->win_check('x');
            if ($player_win_move != -1) {
                $this->position[$player_win_move] = 'o';
            } else {
                $board = implode($this->position);
                $move  = round((pow($this->grid_size, 2) / 2), PHP_ROUND_HALF_ODD);
                while (substr($board, $move, 1) != '-') {
                    $move = rand(0, (pow($this->grid_size, 2) - 1));
                }
                $new_board = substr_replace($board, 'o', $move, 1);

                $this->position = str_split($new_board);
            }
        }
    }

    /**
   Functie care verifica daca a castigat cineva
     */
    function win_check($token) {
        if ($this->debug && debug_backtrace()[1]['function'] == 'game_check') {
            echo '<br />> Check function called from Game for token ' . $token . '...<br />';
        }

        $this->winning_line = []; 
        foreach ($this->win_lines as $line_type => $lines) {
            foreach ($lines as $line_name => $line) {
                $this->winning_line[0] = $line; 
                $check_value           = 0;     
                $win_move              = 0;     
                foreach ($line as $pos) {
                    if ($this->debug && debug_backtrace()[1]['function'] == 'game_check') {
                        echo 'Checking for token ' . $token . ' in ' . $line_type . ' ' . $line_name . ' [' . implode(',', $line) . ']';
                    }
                    if ($this->position[$pos] != $token) {
                        if (debug_backtrace()[1]['function'] == 'game_check') {

                            if ($this->debug) {
                                echo ' - Position ' . $pos . '.  Result:  Not Found.  Skipping rest of ' . $line_name . '<br />';
                            }
                            break;
                        } else if (debug_backtrace()[1]['function'] == 'pick_move') {
                            $win_move = $pos;
                        }
                    } else {
                        if ($this->debug && debug_backtrace()[1]['function'] == 'game_check') {
                            echo ' - Position ' . $pos . '.  Result:  Found.<br />';
                        }
                        $check_value++;
                    }
                }

                if (debug_backtrace()[1]['function'] == 'pick_move') {
                    if ($check_value == ($this->grid_size - 1)) {
                        if ($this->position[$win_move] == '-') {
                            return $win_move;
                        }
                    }
                } else if (debug_backtrace()[1]['function'] == 'game_check') {
                    if ($check_value == $this->grid_size) {
                        if ($this->debug) {
                            echo 'We have a winner!<br />';
                        }
                        return true;
                    }
                }
            }
        }
        $this->winning_line = []; 
        if (debug_backtrace()[1]['function'] == 'game_check') {
            return false;
        } else if (debug_backtrace()[1]['function'] == 'pick_move') {
            return -1;
        } else {
            return null;
        }
    }

    /**
   Mesaje informative
     */
    function game_message($message) {
        $newGame = true; 
       
           
                if ($this->grid_size > 3) {
                    echo '<a draggable="false" href="' . $_SERVER['PHP_SELF'] . '?size=' . ($this->grid_size - 2) . ($this->debug ? '&debug' : '') . '" style="display: inline-block; -webkit-appearance: button; -moz-appearance: button; appearance: button; text-decoration: none; color: initial; padding: 0.5em;">Scade dimensiunea tablei '. ($this->grid_size - 2) . 'x' . ($this->grid_size - 2) . '' . ($this->debug ? ' ' : '') . '</a>';
                    echo '<br />';
                    echo '<a draggable="false" href="' . $_SERVER['PHP_SELF'] . ($this->debug ? '&debug' : '') . '" style="display: inline-block; -webkit-appearance: button; -moz-appearance: button; appearance: button; text-decoration: none; color: initial; padding: 0.5em;">Reseteaza la dimensiunea tablei la 3' . ($this->debug ? ' ' : '') . '</a>';
                    echo '<br />';
                }
                if ($this->grid_size < 15) {
                    echo '<a draggable="false" href="' . $_SERVER['PHP_SELF'] . '?size=' . ($this->grid_size + 2) . ($this->debug ? '&debug' : '') . '" style="display: inline-block; -webkit-appearance: button; -moz-appearance: button; appearance: button; text-decoration: none; color: initial; padding: 0.5em;">Creste dimensiunea tablei la ' . ($this->grid_size + 2) . 'x' . ($this->grid_size + 2) . '' . ($this->debug ? ' ' : '') . '</a>';
                } else {
                    echo '<br /><i>15 este dimensiunea maxima</i>';
                }

         
          
     
      
        if ($newGame) {
            echo '<br /><br /><a draggable="false" href="' . $_SERVER['PHP_SELF'] . '?size=' . $this->grid_size . '" style="-webkit-appearance: button; -moz-appearance: button; appearance: button; text-decoration: none; color: initial; padding: 0.5em;">Apasa aici sa incepi din nou jocul' . ($this->debug ? ' ' : '') . '!</a>';

            if ($this->debug) {
                echo '<br /><br /><a draggable="false" href="' . $_SERVER['PHP_SELF'] . '?size=' . $this->grid_size . '&debug" style="-webkit-appearance: button; -moz-appearance: button; appearance: button; text-decoration: none; color: initial; padding: 0.5em;"></a>';
            }
        }
        
    }

   


  

}
?>