<?php
//if(!(preg_match("/^[  a-zA-Z]+/", $_POST['query']))){ 
//  echo "<script>window.alert('Error en query')</script>";
//}
$word = isset($_POST['query'])?$_POST['query']:"";
$host = "localhost";
$user = "root";
$password = "cloudera";
$database = "proyecto";

if (strlen($word) <= 1 )
{
    echo "<script>window.alert('Term too short, try another one')</script>";
}
else
{
    $db = mysql_connect($host, $user, $password);
    mysql_select_db($database);
    $search = explode(" ",$word);
    $count = 0;

    foreach ($search as $s)
    {
	$construct = "";
        $construct .="Palabra LIKE '%$s%'";
        $construct = " select Url, Titulo from job1 where $construct;";
        $run = mysql_query( $construct );
        $linksfound = mysql_num_rows($run);
        if ($linksfound == 0){
            echo "Sorry, there are no matching result for <b> $s </b>. </br> </br> 1. Try more general words. for example: If you want to search 'how to create a website' then use general keyword like 'create' 'website' </br> 2. Try different words with similar  meaning </br> 3. Please check your spelling";
            echo "<br/><span><a href='search.html'>Go Back</a></span><p>";
        }
        else {
            echo "<h1>$linksfound results found!</h1>";
	    echo "<span><a href='search.html'>Go Back</a></span><p>";
            while( $runrows = mysql_fetch_assoc( $run ) ) {
                $url = $runrows ['Url'];
                $title = $runrows ['Titulo'];
                echo "<div class='row'><a href='$url'><b>$title</b></a><br/><a href='$url'> $url </a><p></div>";
            }
        }
    }
}
?>
