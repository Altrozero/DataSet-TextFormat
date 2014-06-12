<?php

/**
 * Designed to format numbered data sets nicely in a fixed width font
 * text editors. Not suited for handling strings!
 * 
 * @author Timothy Wilson <tim.wilson@aceviral.com>, <altrozero@gmail.com>
 */

/** $var int $additionalSpaces */
$additionalSpaces = 2;

/** @var string $output */
$output = '';

/** @var string $data */
$data = '\  2014-1-1 2014-1-2 2014-1-3
Load 60	87	71
Users 8785 98568 1102356
		
RAM 5.88 10.82 29.13
SERVERS 1.316667 1.298851 3.239437
		
REV 0.005 0.009 80.014
FAIL 0.0119 0.044 0';

/**
 * Convert to arrays based off new lines and spaces
 * 
 * @param string $text
 * 
 * @return Array
 */
function splitTextToArrays($text)
{
  $matrix = Array();
  
  $rows = explode(PHP_EOL, $text);
  
  for ($i = 0, $maxI = count($rows); $i < $maxI; $i++) { // Loop Rows
    $cells = Array(); // Cells in this row
    $previousStop = 0; // Hold the end of the last cut
    
    // Loop Characters to find cells
    for ($j = 0, $maxJ = strlen($rows[$i]); $j < $maxJ; $j++) {
      $cell = '';
      
      // Check if space with no backslash
      if (ctype_space($rows[$i][$j]) && ($j == 0 || $rows[$i][($j - 1)] != '\\')
      ) {
        // Cut string for cell
        $cell = substr($rows[$i], $previousStop, $j-$previousStop);
        $previousStop = $j; // Save the end of this cell
      } else if ($j + 1 == $maxJ) { // End of string, no space
        $cell = substr($rows[$i], $previousStop);
      }
      
      $cell = trim($cell); // Clear additional whitespace

      if ($cell != '') { // Make sure not empty
        $cells[] = $cell; // Save cell
      }
    }
    
    $matrix[] = $cells; // Save cells in this row to the matrix
  }
  
  return $matrix; // Return matrix (rows, columns)
}

/**
 * Get the max character length for each column
 * 
 * @param Array $matrix
 * 
 * @return Array
 */
function columnWidths($matrix)
{
  $columnWidths = Array();
  
  for ($i = 0, $maxI = count($matrix); $i < $maxI; $i++) { // Rows
    for ($j = 0, $maxJ = count($matrix[$i]); $j < $maxJ; $j++) { // Cols
      if (empty($columnWidths[$j]) // If no previous high?
        || strlen($matrix[$i][$j]) > $columnWidths[$j]
      ) {
        $columnWidths[$j] = strlen($matrix[$i][$j]);
      }
    }
  }
  
  return $columnWidths;
}

/**
 * Format the text
 * 
 * @param string $text
 * @global int $additionalSpaces
 * 
 * @return string
 */
function convert($text)
{
  global $additionalSpaces;
  
  $newText = '';
  
  if (empty($text)) {
    return $newText;
  }
  
  $matrix = splitTextToArrays($text);
  $columnWidths = columnWidths($matrix);
  
  for ($i = 0, $max = count($matrix); $i < $max; $i++) { // Loop Rows
    for ($j = 0; $j < count($matrix[$i]); $j++) { // Loop Cols
      while (strlen($matrix[$i][$j]) < $columnWidths[$j] + $additionalSpaces
      ) { // Add spaces
        $matrix[$i][$j] .= ' ';
      }
      
      $newText .= $matrix[$i][$j];
    }
    
    $newText .= PHP_EOL; // Add new line characters
  }
  
  return $newText;
}


// Run
if (!empty($_POST['additionalSpaces'])
  && is_numeric($_POST['additionalSpaces'])
) { // Check form for space requirements
  $additionalSpaces = $_POST['additionalSpaces'];
}

if (!empty($_POST['byText']) && !empty($_POST['format'])) { // Get incoming text
  $data = $_POST['format'];
}

$output = convert($data);

?><html>
  <head>
    <title>Data Set Columns</title>
    
    <style>
      html body {
        margin: 15px;
        
        font-family:Arial,Veranda;
        font-size: 13px;
        font-weight: normal;
        color: #000;
      }
      
      div.title {
        font-weight: bold;
        font-size: 15px;
      }
      
      table {
        border-spacing: 0px;
        
        font-family:Arial,Veranda;
        font-size: 13px;
        font-weight: normal;
        color: #000;
      }
    </style>
  </head>
  <body>
    <div class="title">Data Set Columns</div>
    <br />
    <div style="width: 600px;">
    This tool is used for nicely formatting plain text files based on whitespace
    for columns and rows.It assumes you are using a constant width font editor.
    Designed for number datasets from spreadsheets. Not suited for strings!
    <br />
    <br />
    The original purpose of this was to make copying small parts of spreadsheets
    in to e-mails formattable in a nice way.<br />
    </div>
    <br />
    <form action="" method="POST" style="display: inline-block;">
      <table>
        <tr>
          <td colspan="2">
            <textarea name="format" style="width: 600px;" rows="10"><?=$data?></textarea>
          </td>
        </tr>
        <tr>
          <td>
            Spacing
            <select name="additionalSpaces">
              <?php for ($i = 0; $i <= 50; $i++): ?>
              <option value="<?=$i?>" <?=($i == $additionalSpaces) ? 'selected' : ''?>><?=$i?></option>
              <?php endfor; ?>
            </select>
          </td>
          <td>
            <input type="submit" name="byText" value="Parse" style="float: right;" />
          </td>
        </tr>
      </table>
      
    </form>
    <br />
    <?php if (!empty($output)): ?>
    <div class="title">Output / Results</div><br />
    <textarea style="width: 600px;" rows="10"><?=$output?></textarea>
    <?php endif; ?>
  </body>
</html>