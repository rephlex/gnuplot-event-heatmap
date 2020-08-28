<?php

// query the database. We fetch the timestamp in 15 minute timeslices (00:00, 00:15, 00:30 and so on)
$query = 'select time_to_sec(time(timestamp)) - time_to_sec(time(timestamp))%(15*60) as timeslice, weekday(timestamp) as dow from log order by timeslice,dow;';


// connect to mysql, run query, fetch data and disconnect
$link = mysqli_connect('127.0.0.1','root');
mysqli_select_db($link, 'log');
$res = mysqli_query($link,$query);
mysqli_close ($link);

// prepare matrix:
// x = time in seconds [0..86400] in 15 minute increments
// y = day of week [0..6]
for ($row = 0; $row<7 ;  $row++) {
        for ($col = 0 ; $col < 86400 ; $col += (60*15) ) {
		$counts[$row][$col] = 0;
	}
}

// since our matrix stores the Z  value,
// we now iterate through the events sequentially and
// update the corresponding heatmap Z-value (aka "count")

while ($line = mysqli_fetch_array($res)) {
	$row = $line['dow'];
	$col = $line['timeslice'];
	$counts[$row][$col] += 1;
}

// now we convert need to output the matrix into a string.
// one line per weekday (7 rows), one col per 15 minutes (leaves us with 96 cols).
// The cols are separated by spaces.
$matrix = "";
for ($row = 0; $row<7 ;  $row++) {
	for ($col = 0 ; $col < 86400 ; $col += (60*15) ) {
		$matrix .= $counts[$row][$col] . " ";
	}
	$matrix .= "\n";
}

// now, let's assemble the gnuplot script

$gnuplotdata="set terminal png size 5000\n" .
     "set output \"/var/www/html/graph.png\"\n" .
     "set size ratio 7.0/60.0\n" .
     "set timestamp\n" .

     "set xtics 0,1\n" .
     "set ytics 0,1\n" .
//     "set xtics offset -0.5,0.0\n" .
     "set xtics scale 1,1\n" .
     "set mxtics 1\n" .
     "set mytics 1\n" .
     "set xtics rotate by 70 right\n" .
     "set xtics (";

	for ($i = 0; $i < 96; $i++) {
		$gnuplotdata .= '"' . gmdate("H:i:s",$i*60*15) . '" ' . $i . ', ';
	} $gnuplotdata .= ")\n";

	$gnuplotdata .= 'set ytics ("Montag" 0, "Dienstag" 1, "Mittwoch" 2, "Donnerstag" 3, "Freitag" 4, "Samstag" 5, "Sonntag" 6)';

     $gnuplotdata .= "\n" .
     "set grid front mxtics mytics linetype -1 linecolor rgb \"black\"\n" .
     "plot \"-\" matrix with image notitle \n" .
     $matrix . "\n" .
     "e\n";

// write out the gnuplot script with the in-line-matrix to disk
file_put_contents('/var/www/html/graph.dat', $gnuplotdata);

// call gnuplot and create the image
shell_exec("cat /var/www/html/graph.dat | /usr/bin/gnuplot");

//finally, show the image
?>
<img src="./graph.png" />
